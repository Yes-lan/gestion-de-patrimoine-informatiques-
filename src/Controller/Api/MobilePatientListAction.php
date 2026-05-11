<?php

namespace App\Controller\Api;

use App\Entity\Patient;
use App\Repository\PatientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;

#[Route('/api/mobile/patients', name: 'api_mobile_patients', methods: ['GET'])]
class MobilePatientListAction extends AbstractController
{
    public function __invoke(
        Request $request,
        PatientRepository $patientRepository,
        #[CurrentUser] User $user = null,
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Non authentifié.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $q = mb_strtolower(trim((string) $request->query->get('q', '')));
        $qb = $patientRepository->createQueryBuilder('p')->orderBy('p.Name', 'ASC')->addOrderBy('p.FirstName', 'ASC');

        // Filter by alive status
        if ($request->query->has('alive')) {
            $qb->andWhere('p.alive = :alive')->setParameter('alive', (int) $request->query->get('alive'));
        }

        // Filter by needs greffe
        if ($request->query->has('needsGreffe')) {
            $qb->andWhere('p.needsGreffe = :needs')->setParameter('needs', (int) $request->query->get('needsGreffe'));
        }

        // Role-based filtering (non-admins only see assigned patients)
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $allowedPatientIds = $patientRepository->findPatientIdsByCaregiver($user);
            if ($allowedPatientIds === []) {
                return $this->json(['patients' => []]);
            }
            $qb->andWhere('p.id IN (:allowedPatientIds)')->setParameter('allowedPatientIds', $allowedPatientIds);
        }

        // Search
        if ($q !== '') {
            $qb->andWhere('LOWER(p.Name) LIKE :q OR LOWER(p.FirstName) LIKE :q OR LOWER(p.Ville) LIKE :q')
               ->setParameter('q', '%' . $q . '%');
        }

        $patients = $qb->getQuery()->getResult();

        return $this->json([
            'patients' => array_map(fn (Patient $p) => [
                'id' => $p->getId(),
                'name' => $p->getName(),
                'firstName' => $p->getFirstName(),
                'fullName' => trim($p->getName() . ' ' . $p->getFirstName()),
                'city' => $p->getVille(),
                'alive' => $p->isAlive(),
                'greffesCount' => $p->getGreffes()->count(),
                'operationsCount' => $p->getOperations()->count(),
            ], $patients),
        ]);
    }
}
