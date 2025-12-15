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
        // status: "need" => patients sans greffe, "had" => patients ayant une greffe, omis => tous
        $status = $request->query->get('status');

        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder()
            ->select('p', 'g')
            ->from(Patient::class, 'p')
            ->leftJoin(Greffe::class, 'g', 'WITH', 'g.patient = p');

        if ($status === 'need') {
            $qb->andWhere('g.id IS NULL');
        } elseif ($status === 'had') {
            $qb->andWhere('g.id IS NOT NULL');
        }

        $qb->setMaxResults(200);
        $rows = $qb->getQuery()->getResult();

        $data = [];
        foreach ($rows as $row) {
            // selon la forme du résultat, $row peut être un tableau [p,g] ou l'entité Patient seule
            if (is_array($row)) {
                $patient = $row[0] ?? null;
                $greffe = $row[1] ?? null;
            } elseif ($row instanceof Patient) {
                $patient = $row;
                $greffe = null;
            } else {
                continue;
            }

            if (! $patient) {
                continue;
            }

            $data[] = [
                'id' => method_exists($patient, 'getId') ? $patient->getId() : null,
                'nom' => method_exists($patient, 'getNom') ? $patient->getNom() : null,
                'prenom' => method_exists($patient, 'getPrenom') ? $patient->getPrenom() : null,
                'date_naissance' => method_exists($patient, 'getDateNaissance') && $patient->getDateNaissance() ? $patient->getDateNaissance()->format('Y-m-d') : null,
                'greffe' => $greffe ? [
                    'id' => method_exists($greffe, 'getId') ? $greffe->getId() : null,
                    'date' => method_exists($greffe, 'getDate') && $greffe->getDate() ? $greffe->getDate()->format('Y-m-d') : null,
                    'organ' => method_exists($greffe, 'getOrgan') ? $greffe->getOrgan() : null,
                    // ajoutez d'autres champs si besoin
                ] : null,
            ];
        }

        return $this->json($data);
    }
}
