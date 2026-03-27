<?php

namespace App\Controller;

use App\Entity\Operation;
use App\Entity\Patient;
use App\Entity\User;
use App\Form\OperationType;
use App\Repository\OperationRepository;
use App\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/operation')]
class OperationController extends AbstractController
{
    private function denyUnlessPatientAccessible(Patient $patient, PatientRepository $patientRepository): void
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return;
        }

        $user = $this->getUser();
        if (!$user instanceof User || !$patientRepository->userCanAccessPatient($user, $patient->getId())) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à ce patient.');
        }
    }

    #[Route('', name: 'app_operation', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em, PatientRepository $patientRepository): Response
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

        if (!$this->isGranted('ROLE_ADMIN')) {
            $user = $this->getUser();
            if (!$user instanceof User) {
                throw $this->createAccessDeniedException('Utilisateur non authentifié.');
            }

            $allowedPatientIds = $patientRepository->findPatientIdsByCaregiver($user);
            if ($allowedPatientIds === []) {
                $operations = [];
                $isJson = $request->isXmlHttpRequest() || str_contains($request->headers->get('Accept', ''), 'application/json');

                if ($isJson || $q !== '' || $dateFrom !== '' || $dateTo !== '') {
                    return $this->json([]);
                }

                return $this->render('operation/index.html.twig', [
                    'operations' => $operations,
                    'current_page' => $page,
                    'total_pages' => 0,
                ]);
            }

            $qb->andWhere('p.id IN (:allowedPatientIds)')
                ->setParameter('allowedPatientIds', $allowedPatientIds);
        }

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
                    'chirurgiens' => $operation->getChirurgiens()->count(),
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

        $users = $em->getRepository(User::class)->createQueryBuilder('u')
            ->where('LOWER(u.nom) LIKE :q OR LOWER(u.prenom) LIKE :q OR LOWER(u.email) LIKE :q')
            ->andWhere('u.roles LIKE :roleChirurgien OR u.roles LIKE :roleInfirmiere')
            ->setParameter('q', '%' . mb_strtolower($q) . '%')
            ->setParameter('roleChirurgien', '%"ROLE_CHIRURGIEN"%')
            ->setParameter('roleInfirmiere', '%"ROLE_INFIRMIERE"%')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        $results = [];

        foreach ($users as $staffUser) {
            $nom = $staffUser->getNom() ?? '';
            $prenom = $staffUser->getPrenom() ?? '';
            $email = $staffUser->getEmail() ?? '';
            $roles = $staffUser->getRoles();

            if (in_array('ROLE_CHIRURGIEN', $roles, true)) {
                $results[] = [
                    'id' => 'chirurgien_' . $staffUser->getId(),
                    'type' => 'chirurgien',
                    'typeId' => $staffUser->getId(),
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'label' => sprintf('Dr %s %s (Chirurgien)', $prenom, $nom),
                ];
            }

            if (in_array('ROLE_INFIRMIERE', $roles, true)) {
                $results[] = [
                    'id' => 'infirmiere_' . $staffUser->getId(),
                    'type' => 'infirmiere',
                    'typeId' => $staffUser->getId(),
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'label' => sprintf('%s %s (Infirmière)', $prenom, $nom),
                ];
            }
        }

        return $this->json($results);
    }

    #[Route('/patient/search', name: 'operation_patient_search', methods: ['GET'])]
    public function searchPatient(Request $request, EntityManagerInterface $em, PatientRepository $patientRepository): JsonResponse
    {
        $q = trim((string) $request->query->get('q', ''));
        if (strlen($q) < 2) {
            return $this->json([]);
        }

        $qb = $em->getRepository(Patient::class)->createQueryBuilder('p')
            ->where('LOWER(p.Name) LIKE :q OR LOWER(p.FirstName) LIKE :q')
            ->setParameter('q', '%' . mb_strtolower($q) . '%')
            ->setMaxResults(10);

        if (!$this->isGranted('ROLE_ADMIN')) {
            $user = $this->getUser();
            if (!$user instanceof User) {
                return $this->json([]);
            }

            $allowedPatientIds = $patientRepository->findPatientIdsByCaregiver($user);
            if ($allowedPatientIds === []) {
                return $this->json([]);
            }

            $qb->andWhere('p.id IN (:allowedPatientIds)')
                ->setParameter('allowedPatientIds', $allowedPatientIds);
        }

        $patients = $qb->getQuery()->getResult();

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
        EntityManagerInterface $em,
        PatientRepository $patientRepository
    ): Response {
        if (!$this->isGranted('ROLE_CHIRURGIEN') && !$this->isGranted('ROLE_INFIRMIERE') && !$this->isGranted('ROLE_ADMIN')) {
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

            $this->denyUnlessPatientAccessible($patient, $patientRepository);

            $operation->setPatient($patient);

            $chirurgienIds = $request->request->all()['chirurgien_ids'] ?? [];
            $infirmiereIds = $request->request->all()['infirmiere_ids'] ?? [];

            foreach ($chirurgienIds as $chirurgienId) {
                $chirurgien = $em->getRepository(User::class)->find((int) $chirurgienId);
                if ($chirurgien && in_array('ROLE_CHIRURGIEN', $chirurgien->getRoles(), true)) {
                    $operation->addChirurgien($chirurgien);
                }
            }

            foreach ($infirmiereIds as $infirmiereId) {
                $infirmiere = $em->getRepository(User::class)->find((int) $infirmiereId);
                if ($infirmiere && in_array('ROLE_INFIRMIERE', $infirmiere->getRoles(), true)) {
                    $operation->addInfirmiere($infirmiere);
                }
            }

            // Auto-set staff counts based on selection
            $operation->setNbMedecins($operation->getChirurgiens()->count());
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
        OperationRepository $operationRepository,
        PatientRepository $patientRepository
    ): Response {
        $patient = $em->getRepository(Patient::class)->find($patientId);
        if (!$patient) {
            throw $this->createNotFoundException('Patient non trouvé');
        }

        $this->denyUnlessPatientAccessible($patient, $patientRepository);

        $operations = $operationRepository->findByPatient($patient);

        return $this->render('operation/liste.html.twig', [
            'patient' => $patient,
            'operations' => $operations,
        ]);
    }

    #[Route('/{id}', name: 'operation_afficher', methods: ['GET'])]
    public function afficher(Operation $operation, PatientRepository $patientRepository): Response
    {
        $patient = $operation->getPatient();
        if ($patient instanceof Patient) {
            $this->denyUnlessPatientAccessible($patient, $patientRepository);
        }

        return $this->render('operation/afficher.html.twig', [
            'operation' => $operation,
        ]);
    }
}
