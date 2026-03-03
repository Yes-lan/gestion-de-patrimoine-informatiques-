<?php

namespace App\Controller;

use App\Entity\Greffe;
use App\Entity\Patient;
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
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        // rassembler les paramètres de requête
        $q = trim((string) $request->query->get('q', ''));
        $status = $request->query->get('status', '');
        $organ = trim((string) $request->query->get('organ', ''));
        $dateFrom = trim((string) $request->query->get('date_from', ''));
        $dateTo = trim((string) $request->query->get('date_to', ''));
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = 20;

        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder()
            ->select('p', 'g')
            ->from(Patient::class, 'p')
            ->leftJoin('p.greffes', 'g');

        // appliquer les filtres le cas échéant
        if ($q !== '') {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('LOWER(p.Name)', ':q'),
                $qb->expr()->like('LOWER(p.FirstName)', ':q')
            ))->setParameter('q', '%' . mb_strtolower($q) . '%');
        }

        // Construire des filtres basés sur les greffes via une sous-requête afin de préserver le LEFT JOIN pour la récupération des greffes
        $hasGreffeFilters = false;
        $subQb = $em->createQueryBuilder()->select('p2.id')->from(Patient::class, 'p2')->leftJoin('p2.greffes', 'g2');

        if ($organ !== '') {
            $hasGreffeFilters = true;
            $subQb->andWhere('LOWER(g2.Type) LIKE :organ_sub')->setParameter('organ_sub', '%' . mb_strtolower($organ) . '%');
        }

        if ($dateFrom) {
            try {
                $df = new \DateTime($dateFrom);
                $hasGreffeFilters = true;
                $subQb->andWhere('g2.Date_Fin_De_Fonction >= :df_sub')->setParameter('df_sub', $df->format('Y-m-d'));
            } catch (\Throwable) {}
        }
        if ($dateTo) {
            try {
                $dt = new \DateTime($dateTo);
                $hasGreffeFilters = true;
                $subQb->andWhere('g2.Date_Fin_De_Fonction <= :dt_sub')->setParameter('dt_sub', $dt->format('Y-m-d'));
            } catch (\Throwable) {}
        }

        // Gestion du statut : utiliser une sous-requête pour trouver les patients qui ont (ou n'ont pas) de greffes correspondantes
        if ($status === 'need') {
            // patients sans aucune greffe (ou sans filtres de greffe correspondants si fournis)
            if ($hasGreffeFilters) {
                // patients qui n'ont PAS de greffe correspondant aux filtres de greffe
                $subDql = $subQb->getDQL();
                foreach ($subQb->getParameters() as $p) { $qb->setParameter($p->getName(), $p->getValue()); }
                $qb->andWhere($qb->expr()->notIn('p.id', $subDql));
            } else {
                // patients qui n'ont aucune greffe
                $subAny = $em->createQueryBuilder()->select('p3.id')->from(Patient::class, 'p3')->leftJoin('p3.greffes', 'g3')->andWhere('g3.id IS NOT NULL');
                $qb->andWhere($qb->expr()->notIn('p.id', $subAny->getDQL()));
            }
        } elseif ($status === 'had') {
            // patients qui ont au moins une greffe (ou au moins une correspondant aux filtres de greffe)
            if ($hasGreffeFilters) {
                $subDql = $subQb->getDQL();
                foreach ($subQb->getParameters() as $p) { $qb->setParameter($p->getName(), $p->getValue()); }
                $qb->andWhere($qb->expr()->in('p.id', $subDql));
            } else {
                $subAny = $em->createQueryBuilder()->select('p3.id')->from(Patient::class, 'p3')->leftJoin('p3.greffes', 'g3')->andWhere('g3.id IS NOT NULL');
                $qb->andWhere($qb->expr()->in('p.id', $subAny->getDQL()));
            }
        } else {
            // aucun statut explicite mais des filtres de greffe fournis -> n'inclure que les patients qui ont des greffes correspondantes
            if ($hasGreffeFilters) {
                $subDql = $subQb->getDQL();
                foreach ($subQb->getParameters() as $p) { $qb->setParameter($p->getName(), $p->getValue()); }
                $qb->andWhere($qb->expr()->in('p.id', $subDql));
            }
        }

        // déterminer si l'appelant attend du JSON (requête avec en-tête Accept, ou AJAX)
        $isJson = $request->isXmlHttpRequest() || str_contains($request->headers->get('Accept', ''), 'application/json');

        // si du JSON est demandé ou des filtres fournis, retourner du JSON (le client gérera lui-même la pagination)
            if ($isJson || $q !== '' || $status !== '' || $organ !== '' || $dateFrom !== '' || $dateTo !== '') {
                // First: determine matching patient IDs for the given filters (avoid losing greffes due to WHERE on joined greffes)
                $idsQb = clone $qb;
                $idsQb->select('DISTINCT p.id');
                $idsQb->setMaxResults(500);
                $idRows = $idsQb->getQuery()->getScalarResult();
                $ids = array_map(fn($r) => (int) ($r['id'] ?? $r['p_id'] ?? array_values($r)[0]), $idRows);

                if (empty($ids)) {
                    return $this->json([]);
                }

                // Second: fetch those patients with their greffes using a LEFT JOIN so greffes are preserved
                $fetchQb = $em->createQueryBuilder()
                    ->select('p', 'g')
                    ->from(Patient::class, 'p')
                    ->leftJoin('p.greffes', 'g')
                    ->andWhere('p.id IN (:ids)')
                    ->setParameter('ids', $ids)
                    ->setMaxResults(500);

                $rows = $fetchQb->getQuery()->getResult();

                // Aggregate greffes per patient
                $map = [];
                foreach ($rows as $row) {
                    if (is_array($row)) {
                        $patient = $row[0] ?? null;
                        $greffe = $row[1] ?? null;
                        $greffesToAdd = $greffe ? [$greffe] : [];
                    } elseif ($row instanceof Patient) {
                        $patient = $row;
                        $greffesToAdd = $patient->getGreffes()->toArray();
                    } else {
                        continue;
                    }
                    if (!$patient) { continue; }
                    $pid = $patient->getId();
                    if (!isset($map[$pid])) {
                        $map[$pid] = [
                            'id' => $pid,
                            'nom' => $patient->getName(),
                            'prenom' => $patient->getFirstName(),
                            'date_naissance' => null,
                            'greffes' => [],
                        ];
                    }
                    foreach ($greffesToAdd as $gObj) {
                        if (!$gObj) { continue; }
                        $map[$pid]['greffes'][] = [
                            'id' => $gObj->getId(),
                            'date' => $gObj->getDateFinDeFonction() ? $gObj->getDateFinDeFonction()->format('Y-m-d') : null,
                            'organ' => $gObj->getType(),
                        ];
                    }
                }

                // return flat array where 'greffe' is the first greffe (for compatibility) and 'greffes' contains all
                $data = array_map(function($p){
                    $first = count($p['greffes']) ? $p['greffes'][0] : null;
                    $p['greffe'] = $first;
                    return $p;
                }, array_values($map));

                return $this->json($data);
            }

        // liste par défaut avec pagination
        // Pour la pagination HTML, nous voulons les patients avec leurs greffes préservées.
        // Utiliser DISTINCT et passer la requête au Paginator de Doctrine avec fetchJoinCollection=true
        $qb->distinct();
        $query = $qb->setFirstResult(($page - 1) * $perPage)
                    ->setMaxResults($perPage)
                    ->getQuery();

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query, true);
        $total = count($paginator);
        $patients = [];
        foreach ($paginator as $row) {
            if (is_array($row)) { $patients[] = $row[0]; }
            elseif ($row instanceof Patient) { $patients[] = $row; }
        }

        return $this->render('greffe/index.html.twig', [
            'patients' => $patients,
            'current_page' => $page,
            'total_pages' => (int) ceil($total / $perPage),
        ]);
    }
}


