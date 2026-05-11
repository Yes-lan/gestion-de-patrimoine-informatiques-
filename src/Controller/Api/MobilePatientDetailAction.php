<?php

namespace App\Controller\Api;

use App\Entity\Patient;
use App\Repository\PatientRepository;
use App\Repository\RendezVousRepository;
use App\Repository\PatientPhotoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;

#[Route('/api/mobile/patients/{id<\d+>}', name: 'api_mobile_patient_detail', methods: ['GET'])]
class MobilePatientDetailAction extends AbstractController
{
    public function __invoke(
        int $id,
        PatientRepository $patientRepository,
        RendezVousRepository $rendezVousRepository,
        PatientPhotoRepository $patientPhotoRepository,
        #[CurrentUser] User $user = null,
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Non authentifié.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $patient = $patientRepository->find($id);
        if (!$patient instanceof Patient) {
            return $this->json(['message' => 'Patient introuvable.'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Check access
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true) && 
            !$patientRepository->userCanAccessPatient($user, $id)) {
            return $this->json(['message' => 'Accès refusé.'], JsonResponse::HTTP_FORBIDDEN);
        }

        $rdvList = $rendezVousRepository->findByPatient($patient);
        $photos = $patientPhotoRepository->findByPatient($patient);
        $operations = $patient->getOperations()->toArray();

        // Sort operations by date DESC
        usort($operations, fn ($a, $b) => 
            ($b->getDateOperation()?->getTimestamp() ?? 0) <=> ($a->getDateOperation()?->getTimestamp() ?? 0)
        );

        // Collect rapports
        $rapports = [];
        foreach ($operations as $operation) {
            foreach ($operation->getRapports() as $rapport) {
                $rapports[] = $rapport;
            }
        }

        usort($rapports, fn ($a, $b) =>
            ($b->getDateCreation()?->getTimestamp() ?? 0) <=> ($a->getDateCreation()?->getTimestamp() ?? 0)
        );

        return $this->json([
            'patient' => [
                'id' => $patient->getId(),
                'fullName' => trim($patient->getName() . ' ' . $patient->getFirstName()),
                'name' => $patient->getName(),
                'firstName' => $patient->getFirstName(),
                'city' => $patient->getVille(),
                'email' => $patient->getUser()?->getEmail(),
                'rdv' => array_map(fn ($rdv) => [
                    'title' => $rdv->getTitle(),
                    'scheduledAt' => $rdv->getScheduledAt()?->format('c'),
                    'scheduledAtDisplay' => $rdv->getScheduledAt()?->format('d/m/Y H:i') ?? 'Date inconnue',
                    'location' => $rdv->getLocation(),
                ], $rdvList),
                'photos' => array_map(fn ($photo) => [
                    'url' => $photo->getFilePath() ? '/uploads/patient-photos/' . basename($photo->getFilePath()) : '',
                ], $photos),
                'rapports' => array_map(fn ($rapport) => [
                    'title' => $rapport->getTitle(),
                    'text' => $rapport->getTexte(),
                ], $rapports),
            ],
        ]);
    }
}
