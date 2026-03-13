<?php

namespace App\Controller;

use App\Entity\Infirmiere;
use App\Entity\Medecin;
use App\Entity\Operation;
use App\Entity\Patient;
use App\Form\OperationType;
use App\Repository\OperationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/operation')]
class OperationController extends AbstractController
{
    #[Route('', name: 'app_operation', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $q = trim((string) $request->query->get('q', ''));
        $dateFrom = trim((string) $request->query->get('date_from', ''));
        $dateTo = trim((string) $request->query->get('date_to', ''));
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = 20;

        $qb = $em->getRepository(Operation::class)->createQueryBuilder('o')
            ->leftJoin('o.patient', 'p')
            ->addSelect('p')
            ->orderBy('o.dateOperation', 'DESC');

        if ($q !== '') {
            $qb->andWhere('LOWER(o.titre) LIKE :q OR LOWER(p.Name) LIKE :q OR LOWER(p.FirstName) LIKE :q')
                ->setParameter('q', '%' . mb_strtolower($q) . '%');
        }

        if ($dateFrom !== '') {
            try {
                $from = new \DateTime($dateFrom);
                $qb->andWhere('o.dateOperation >= :dateFrom')->setParameter('dateFrom', $from->format('Y-m-d 00:00:00'));
            } catch (\Exception $e) {
            }
        }

        if ($dateTo !== '') {
            try {
                $to = new \DateTime($dateTo);
                $qb->andWhere('o.dateOperation <= :dateTo')->setParameter('dateTo', $to->format('Y-m-d 23:59:59'));
            } catch (\Exception $e) {
            }
        }

        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(DISTINCT o.id)')->getQuery()->getSingleScalarResult();

        $qb->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        $operations = $qb->getQuery()->getResult();

        $isJson = $request->isXmlHttpRequest() || str_contains($request->headers->get('Accept', ''), 'application/json');
        if ($isJson || $q !== '' || $dateFrom !== '' || $dateTo !== '') {
            $data = array_map(static function (Operation $operation): array {
                $patient = $operation->getPatient();

                return [
                    'id' => $operation->getId(),
                    'titre' => $operation->getTitre(),
                    'date' => $operation->getDateOperation()?->format('Y-m-d H:i'),
                    'patient' => [
                        'id' => $patient?->getId(),
                        'name' => $patient?->getName(),
                        'firstName' => $patient?->getFirstName(),
                    ],
                    'medecins' => $operation->getMedecins()->count(),
                    'infirmieres' => $operation->getInfirmieres()->count(),
                ];
            }, $operations);

            return $this->json($data);
        }

        return $this->render('operation/index.html.twig', [
            'operations' => $operations,
            'current_page' => $page,
            'total_pages' => (int) ceil($total / $perPage),
        ]);
    }

    #[Route('/personnel/search', name: 'operation_personnel_search', methods: ['GET'])]
    public function searchPersonnel(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $q = trim((string) $request->query->get('q', ''));
        if (strlen($q) < 2) {
            return $this->json([]);
        }

        $medecins = $em->getRepository(Medecin::class)->createQueryBuilder('m')
            ->where('LOWER(m.nom) LIKE :q OR LOWER(m.prenom) LIKE :q OR LOWER(m.email) LIKE :q')
            ->setParameter('q', '%' . mb_strtolower($q) . '%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $infirmieres = $em->getRepository(Infirmiere::class)->createQueryBuilder('i')
            ->where('LOWER(i.nom) LIKE :q OR LOWER(i.prenom) LIKE :q OR LOWER(i.email) LIKE :q')
            ->setParameter('q', '%' . mb_strtolower($q) . '%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $results = [];
        foreach ($medecins as $m) {
            $results[] = [
                'id' => 'medecin_' . $m->getId(),
                'type' => 'medecin',
                'typeId' => $m->getId(),
                'nom' => $m->getNom(),
                'prenom' => $m->getPrenom(),
                'email' => $m->getEmail(),
                'label' => sprintf('Dr %s %s (Médecin)', $m->getPrenom(), $m->getNom()),
            ];
        }

        foreach ($infirmieres as $i) {
            $results[] = [
                'id' => 'infirmiere_' . $i->getId(),
                'type' => 'infirmiere',
                'typeId' => $i->getId(),
                'nom' => $i->getNom(),
                'prenom' => $i->getPrenom(),
                'email' => $i->getEmail(),
                'label' => sprintf('%s %s (Infirmière)', $i->getPrenom(), $i->getNom()),
            ];
        }

        return $this->json($results);
    }

    #[Route('/patient/search', name: 'operation_patient_search', methods: ['GET'])]
    public function searchPatient(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $q = trim((string) $request->query->get('q', ''));
        if (strlen($q) < 2) {
            return $this->json([]);
        }

        $patients = $em->getRepository(Patient::class)->createQueryBuilder('p')
            ->where('LOWER(p.Name) LIKE :q OR LOWER(p.FirstName) LIKE :q')
            ->setParameter('q', '%' . mb_strtolower($q) . '%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $results = [];
        foreach ($patients as $patient) {
            $results[] = [
                'id' => $patient->getId(),
                'nom' => $patient->getName(),
                'prenom' => $patient->getFirstName(),
                'ville' => $patient->getVille(),
                'label' => sprintf('%s %s (N° %d - %s)', $patient->getName(), $patient->getFirstName(), $patient->getId(), $patient->getVille()),
            ];
        }

        return $this->json($results);
    }

    #[Route('/nouvelle', name: 'operation_creer', methods: ['GET', 'POST'])]
    public function creer(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if (!$this->isGranted('ROLE_MEDECIN') && !$this->isGranted('ROLE_INFIRMIERE') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Accès réservé au personnel soignant.');
        }

        $operation = new Operation();

        $form = $this->createForm(OperationType::class, $operation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $patientId = $request->request->get('operation')['patient'] ?? null;
            if (!$patientId) {
                $this->addFlash('danger', 'Veuillez sélectionner un patient.');
                return $this->render('operation/creer.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $patient = $em->getRepository(Patient::class)->find((int) $patientId);
            if (!$patient) {
                $this->addFlash('danger', 'Patient non trouvé.');
                return $this->render('operation/creer.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $operation->setPatient($patient);

            $medecinIds = $request->request->all()['medecin_ids'] ?? [];
            $infirmiereIds = $request->request->all()['infirmiere_ids'] ?? [];

            foreach ($medecinIds as $medecinId) {
                $medecin = $em->getRepository(Medecin::class)->find((int) $medecinId);
                if ($medecin) {
                    $operation->addMedecin($medecin);
                }
            }

            foreach ($infirmiereIds as $infirmiereId) {
                $infirmiere = $em->getRepository(Infirmiere::class)->find((int) $infirmiereId);
                if ($infirmiere) {
                    $operation->addInfirmiere($infirmiere);
                }
            }

            // Auto-set staff counts based on selection
            $operation->setNbMedecins($operation->getMedecins()->count());
            $operation->setNbInfirmieres($operation->getInfirmieres()->count());

            $em->persist($operation);
            $em->flush();

            $this->addFlash('success', 'Opération créée avec succès.');

            return $this->redirectToRoute('operation_afficher', ['id' => $operation->getId()]);
        }

        return $this->render('operation/creer.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/patient/{patientId}/liste', name: 'operation_liste', methods: ['GET'])]
    public function liste(
        int $patientId,
        EntityManagerInterface $em,
        OperationRepository $operationRepository
    ): Response {
        $patient = $em->getRepository(Patient::class)->find($patientId);
        if (!$patient) {
            throw $this->createNotFoundException('Patient non trouvé');
        }

        $operations = $operationRepository->findByPatient($patient);

        return $this->render('operation/liste.html.twig', [
            'patient' => $patient,
            'operations' => $operations,
        ]);
    }

    #[Route('/{id}', name: 'operation_afficher', methods: ['GET'])]
    public function afficher(Operation $operation): Response
    {
        return $this->render('operation/afficher.html.twig', [
            'operation' => $operation,
        ]);
    }
}
