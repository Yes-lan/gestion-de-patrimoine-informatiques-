<?php

namespace App\Controller;

use App\Entity\Greffe;
use App\Entity\Patient;
use App\Entity\User;
use App\Repository\PatientRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class GreffeController extends AbstractController
{
    #[Route('/greffe', name: 'app_greffe', methods: ['GET'])]
    public function index(Request $request, PatientRepository $patientRepository): Response
    {
        // Rassembler les paramètres de requête
        $criteria = [
            'q' => trim((string) $request->query->get('q', '')),
            'status' => $request->query->get('status', ''),
            'organ' => trim((string) $request->query->get('organ', '')),
            'date_from' => trim((string) $request->query->get('date_from', '')),
            'date_to' => trim((string) $request->query->get('date_to', '')),
            'page' => max(1, (int) $request->query->get('page', 1)),
            'per_page' => 20,
        ];

        if (!$this->isGranted('ROLE_ADMIN')) {
            $user = $this->getUser();
            if ($user instanceof User) {
                $criteria['allowed_patient_ids'] = $patientRepository->findPatientIdsByCaregiver($user);
            } else {
                $criteria['allowed_patient_ids'] = [];
            }
        }

        // Utiliser la méthode du repository qui gère tous les filtres avec EXISTS (optimisé)
        $result = $patientRepository->findByFilters($criteria);
        
        // Déterminer si l'appelant attend du JSON (requête AJAX)
        $isJson = $request->isXmlHttpRequest() || 
                  str_contains($request->headers->get('Accept', ''), 'application/json');

        // Si JSON demandé OU des filtres fournis, retourner JSON
        if ($isJson || 
            $criteria['q'] !== '' || 
            $criteria['status'] !== '' || 
            $criteria['organ'] !== '' || 
            $criteria['date_from'] !== '' || 
            $criteria['date_to'] !== '') {
            
            // Transformer les patients en format JSON avec toutes leurs greffes
            $data = array_map(function(Patient $patient) {
                $greffesData = [];
                foreach ($patient->getGreffes() as $greffe) {
                    $greffesData[] = [
                        'id' => $greffe->getId(),
                        'date' => $greffe->getDateFinDeFonction() ? 
                                 $greffe->getDateFinDeFonction()->format('Y-m-d') : null,
                        'organ' => $greffe->getType(),
                        'type' => $greffe->getType(), // Alias pour compatibilité
                    ];
                }
                
                return [
                    'id' => $patient->getId(),
                    'nom' => $patient->getName(),
                    'name' => $patient->getName(), // Alias pour compatibilité
                    'prenom' => $patient->getFirstName(),
                    'firstName' => $patient->getFirstName(), // Alias pour compatibilité
                    'date_naissance' => null, // À implémenter si le champ existe
                    'greffes' => $greffesData,
                    'greffe' => !empty($greffesData) ? $greffesData[0] : null, // Premier pour compatibilité
                ];
            }, $result['data']);
            
            return $this->json($data);
        }

        // Rendu HTML par défaut avec pagination
        return $this->render('greffe/index.html.twig', [
            'patients' => $result['data'],
            'current_page' => $result['page'],
            'total_pages' => $result['total_pages'],
        ]);
    }
}
