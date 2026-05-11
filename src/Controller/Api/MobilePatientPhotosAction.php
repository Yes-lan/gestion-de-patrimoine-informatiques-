<?php

namespace App\Controller\Api;

use App\Entity\Patient;
use App\Repository\PatientRepository;
use App\Repository\PatientPhotoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;

#[Route('/api/mobile/patients/{patientId<\d+>}/photos', name: 'api_mobile_patient_photos', methods: ['GET', 'POST'])]
class MobilePatientPhotosAction extends AbstractController
{
    public function __invoke(
        int $patientId,
        Request $request,
        PatientRepository $patientRepository,
        PatientPhotoRepository $photoRepository,
        #[CurrentUser] User $user = null,
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Non authentifié.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $patient = $patientRepository->find($patientId);
        if (!$patient instanceof Patient) {
            return $this->json(['message' => 'Patient introuvable.'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Check access
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true) && 
            !$patientRepository->userCanAccessPatient($user, $patientId)) {
            return $this->json(['message' => 'Accès refusé.'], JsonResponse::HTTP_FORBIDDEN);
        }

        if ($request->getMethod() === 'POST') {
            return $this->uploadPhoto($request, $patient, $user, $photoRepository, $patientRepository);
        }

        // GET photos
        $photos = $photoRepository->findBy(['patient' => $patient], ['createdAt' => 'DESC']);

        return $this->json([
            'photos' => array_map(fn ($photo) => [
                'id' => $photo->getId(),
                'url' => $photo->getFilename() ? '/uploads/patient-photos/' . $photo->getFilename() : '',
                'caption' => $photo->getCaption(),
            ], $photos),
        ]);
    }

    private function uploadPhoto(
        Request $request,
        Patient $patient,
        User $user,
        PatientPhotoRepository $photoRepository,
        PatientRepository $patientRepository,
    ): JsonResponse {
        // This is a simplified version - implement file upload if needed
        return $this->json(['message' => 'Photo upload not yet implemented.'], JsonResponse::HTTP_NOT_IMPLEMENTED);
    }
}
