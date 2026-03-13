<?php

namespace App\Repository;

use App\Entity\Patient;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Patient>
 */
class PatientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Patient::class);
    }

    /**
     * @return int[]
     */
    public function findPatientIdsByCaregiver(User $user): array
    {
        $rows = $this->createQueryBuilder('p')
            ->select('DISTINCT p.id AS id')
            ->innerJoin('p.caregivers', 'c')
            ->andWhere('c = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getArrayResult();

        return array_map(static fn (array $row) => (int) $row['id'], $rows);
    }

    public function userCanAccessPatient(User $user, int $patientId): bool
    {
        $count = (int) $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p.id)')
            ->innerJoin('p.caregivers', 'c')
            ->andWhere('c = :user')
            ->andWhere('p.id = :patientId')
            ->setParameter('user', $user)
            ->setParameter('patientId', $patientId)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Recherche de patients avec filtres optimisés utilisant EXISTS au lieu de sous-requêtes IN/NOT IN
     * 
     * @param array $criteria Critères de filtrage:
     *                        - 'q' (string): Recherche dans nom/prénom
     *                        - 'status' (string): 'need' | 'had' | ''
     *                        - 'organ' (string): Type d'organe
     *                        - 'date_from' (string): Date début yyyy-mm-dd
     *                        - 'date_to' (string): Date fin yyyy-mm-dd
     *                        - 'page' (int): Numéro de page (défaut: 1)
     *                        - 'per_page' (int): Résultats par page (défaut: 20)
     * @return array ['data' => Patient[], 'total' => int] Résultats paginés
     */
    public function findByFilters(array $criteria): array
    {
        // Extraction des critères avec valeurs par défaut
        $q = trim($criteria['q'] ?? '');
        $status = $criteria['status'] ?? '';
        $organ = trim($criteria['organ'] ?? '');
        $dateFrom = trim($criteria['date_from'] ?? '');
        $dateTo = trim($criteria['date_to'] ?? '');
        $allowedPatientIds = $criteria['allowed_patient_ids'] ?? null;
        $page = max(1, (int) ($criteria['page'] ?? 1));
        $perPage = (int) ($criteria['per_page'] ?? 20);
        
        // Construction de la requête DQL de base avec LEFT JOIN pour récupérer toutes les greffes
        $dql = "SELECT DISTINCT p, g 
                FROM App\Entity\Patient p 
                LEFT JOIN p.greffes g";
        
        $conditions = [];
        $params = [];
        
        // === FILTRE 1 : Recherche textuelle sur nom/prénom ===
        // Utilise LOWER() pour recherche insensible à la casse
        if ($q !== '') {
            $conditions[] = "(LOWER(p.Name) LIKE :q OR LOWER(p.FirstName) LIKE :q)";
            $params['q'] = '%' . mb_strtolower($q) . '%';
        }

        if (is_array($allowedPatientIds)) {
            if ($allowedPatientIds === []) {
                return [
                    'data' => [],
                    'total' => 0,
                    'page' => $page,
                    'per_page' => $perPage,
                    'total_pages' => 0,
                ];
            }

            $conditions[] = 'p.id IN (:allowed_patient_ids)';
            $params['allowed_patient_ids'] = array_map('intval', $allowedPatientIds);
        }
        
        // === FILTRE 2 : Statut greffe avec EXISTS (TRÈS PERFORMANT) ===
        // EXISTS arrête dès qu'une ligne correspond, contrairement à IN qui charge toutes les IDs
        
        if ($status === 'need') {
            // Patients sans AUCUNE greffe
            // NOT EXISTS est 3-5x plus rapide que NOT IN car MySQL/PostgreSQL optimise mieux
            $conditions[] = "NOT EXISTS (
                SELECT 1 
                FROM App\Entity\Greffe g_check 
                WHERE g_check.patient = p
            )";
            
        } elseif ($status === 'had') {
            // Patients avec AU MOINS une greffe
            // EXISTS s'arrête dès la première greffe trouvée (pas besoin de compter toutes)
            $existsConditions = ["g_check.patient = p"];
            $existsParams = [];
            
            // Si un organe est spécifié, on l'inclut dans le EXISTS
            if ($organ !== '') {
                $existsConditions[] = "LOWER(g_check.Type) LIKE :organ_exists";
                $existsParams['organ_exists'] = '%' . mb_strtolower($organ) . '%';
            }
            
            // Si des dates sont spécifiées, on les inclut dans le EXISTS
            if ($dateFrom !== '') {
                try {
                    $df = new \DateTime($dateFrom);
                    $existsConditions[] = "g_check.Date_Fin_De_Fonction >= :date_from_exists";
                    $existsParams['date_from_exists'] = $df->format('Y-m-d');
                } catch (\Exception $e) {
                    // Date invalide ignorée
                }
            }
            
            if ($dateTo !== '') {
                try {
                    $dt = new \DateTime($dateTo);
                    $existsConditions[] = "g_check.Date_Fin_De_Fonction <= :date_to_exists";
                    $existsParams['date_to_exists'] = $dt->format('Y-m-d');
                } catch (\Exception $e) {
                    // Date invalide ignorée
                }
            }
            
            $conditions[] = sprintf(
                "EXISTS (
                    SELECT 1 
                    FROM App\Entity\Greffe g_check 
                    WHERE %s
                )",
                implode(' AND ', $existsConditions)
            );
            
            $params = array_merge($params, $existsParams);
            
        } else {
            // Aucun statut : si des filtres greffe sont fournis, on cherche les patients correspondants
            if ($organ !== '' || $dateFrom !== '' || $dateTo !== '') {
                $existsConditions = ["g_check.patient = p"];
                $existsParams = [];
                
                if ($organ !== '') {
                    $existsConditions[] = "LOWER(g_check.Type) LIKE :organ_no_status";
                    $existsParams['organ_no_status'] = '%' . mb_strtolower($organ) . '%';
                }
                
                if ($dateFrom !== '') {
                    try {
                        $df = new \DateTime($dateFrom);
                        $existsConditions[] = "g_check.Date_Fin_De_Fonction >= :date_from_no_status";
                        $existsParams['date_from_no_status'] = $df->format('Y-m-d');
                    } catch (\Exception $e) {}
                }
                
                if ($dateTo !== '') {
                    try {
                        $dt = new \DateTime($dateTo);
                        $existsConditions[] = "g_check.Date_Fin_De_Fonction <= :date_to_no_status";
                        $existsParams['date_to_no_status'] = $dt->format('Y-m-d');
                    } catch (\Exception $e) {}
                }
                
                $conditions[] = sprintf(
                    "EXISTS (
                        SELECT 1 
                        FROM App\Entity\Greffe g_check 
                        WHERE %s
                    )",
                    implode(' AND ', $existsConditions)
                );
                
                $params = array_merge($params, $existsParams);
            }
        }
        
        // Ajouter les conditions WHERE si nécessaire
        if (!empty($conditions)) {
            $dql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        // Ordonner par nom
        $dql .= " ORDER BY p.Name ASC, p.FirstName ASC";
        
        // === REQUÊTE DE COMPTAGE ===
        // Compter le nombre total de résultats pour la pagination
        $countDql = "SELECT COUNT(DISTINCT p.id) 
                     FROM App\Entity\Patient p";
        
        if (!empty($conditions)) {
            $countDql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $countQuery = $this->getEntityManager()->createQuery($countDql);
        foreach ($params as $key => $value) {
            if ($key === 'allowed_patient_ids') {
                $countQuery->setParameter($key, $value);
            } else {
                $countQuery->setParameter($key, $value);
            }
        }
        $total = (int) $countQuery->getSingleScalarResult();
        
        // === REQUÊTE PRINCIPALE AVEC PAGINATION ===
        $query = $this->getEntityManager()->createQuery($dql);
        
        // Définir les paramètres
        foreach ($params as $key => $value) {
            if ($key === 'allowed_patient_ids') {
                $query->setParameter($key, $value);
            } else {
                $query->setParameter($key, $value);
            }
        }
        
        // Appliquer la pagination
        $query->setFirstResult(($page - 1) * $perPage)
              ->setMaxResults($perPage);
        
        // Exécuter et retourner les résultats
        $results = $query->getResult();
        
        // Grouper les patients (car DISTINCT sur LEFT JOIN peut créer des doublons)
        $patients = [];
        $seenIds = [];
        foreach ($results as $result) {
            if (is_array($result)) {
                $patient = $result[0] ?? null;
            } else {
                $patient = $result;
            }
            
            if ($patient && !in_array($patient->getId(), $seenIds, true)) {
                $patients[] = $patient;
                $seenIds[] = $patient->getId();
            }
        }
        
        return [
            'data' => $patients,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }
}
