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

#[IsGranted('ROLE_USER')]
final class GreffeController extends AbstractController
{
    #[Route('/greffe', name: 'app_greffe')]
    public function index(): Response
    {
        return $this->render('greffe/index.html.twig');
    }

    #[Route('/greffe/search', name: 'app_greffe_search', methods: ['GET'])]
    public function search(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        if (! $this->getUser()) {
            return $this->json(['error' => 'Vous devez vous connecter.'], Response::HTTP_UNAUTHORIZED);
        }

        $q = trim((string) $request->query->get('q', ''));
        $status = $request->query->get('status', '');
        $organ = trim((string) $request->query->get('organ', ''));
        $dateFrom = trim((string) $request->query->get('date_from', ''));
        $dateTo = trim((string) $request->query->get('date_to', ''));

        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder()
            ->select('p', 'g')
            ->from(Patient::class, 'p')
            ->leftJoin(Greffe::class, 'g', 'WITH', 'g.patient = p');

        if ($q !== '') {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('LOWER(p.nom)', ':q'),
                $qb->expr()->like('LOWER(p.prenom)', ':q')
            ))->setParameter('q', '%' . mb_strtolower($q) . '%');
        }

        if ($status === 'need') {
            $qb->andWhere('g.id IS NULL');
        } elseif ($status === 'had') {
            $qb->andWhere('g.id IS NOT NULL');
        }

        if ($organ !== '') {
            $qb->andWhere('LOWER(g.organ) LIKE :organ')->setParameter('organ', '%' . mb_strtolower($organ) . '%');
        }

        if ($dateFrom) {
            try { $df = new \DateTime($dateFrom); $qb->andWhere('g.date >= :df')->setParameter('df', $df->format('Y-m-d')); } catch (\Throwable) {}
        }
        if ($dateTo) {
            try { $dt = new \DateTime($dateTo); $qb->andWhere('g.date <= :dt')->setParameter('dt', $dt->format('Y-m-d')); } catch (\Throwable) {}
        }

        $qb->setMaxResults(500);
        $rows = $qb->getQuery()->getResult();

        $data = [];
        foreach ($rows as $row) {
            if (is_array($row)) { $patient = $row[0] ?? null; $greffe = $row[1] ?? null; }
            elseif ($row instanceof Patient) { $patient = $row; $greffe = null; }
            else { continue; }

            $data[] = [
                'id' => $patient->getId(),
                'nom' => method_exists($patient,'getNom') ? $patient->getNom() : null,
                'prenom' => method_exists($patient,'getPrenom') ? $patient->getPrenom() : null,
                'date_naissance' => (method_exists($patient,'getDateNaissance') && $patient->getDateNaissance()) ? $patient->getDateNaissance()->format('Y-m-d') : null,
                'greffe' => $greffe ? [
                    'id' => method_exists($greffe,'getId') ? $greffe->getId() : null,
                    'date' => (method_exists($greffe,'getDate') && $greffe->getDate()) ? $greffe->getDate()->format('Y-m-d') : null,
                    'organ' => method_exists($greffe,'getOrgan') ? $greffe->getOrgan() : null,
                ] : null,
            ];
        }

        return $this->json($data);
    }
}
