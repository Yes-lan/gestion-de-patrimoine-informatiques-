<?php

namespace App\Controller\Api;

use App\Entity\Patient;
use App\Entity\PatientNote;
use App\Repository\PatientRepository;
use App\Repository\PatientNoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/mobile/patients/{patientId<\d+>}/notes', name: 'api_mobile_patient_notes', methods: ['GET', 'POST'])]
class MobilePatientNotesAction extends AbstractController
{
    public function __invoke(
        int $patientId,
        Request $request,
        PatientRepository $patientRepository,
        PatientNoteRepository $noteRepository,
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
            return $this->addNote($request, $patient, $user, $noteRepository, $patientRepository);
        }

        // GET notes
        $notes = $noteRepository->findBy(['patient' => $patient], ['createdAt' => 'DESC']);

        return $this->json([
            'notes' => array_map(fn ($note) => [
                'id' => $note->getId(),
                'content' => $note->getContent(),
                'createdBy' => [
                    'id' => $note->getCreatedBy()?->getId(),
                    'name' => $note->getCreatedBy()?->getNom() . ' ' . $note->getCreatedBy()?->getPrenom(),
                ],
                'createdAt' => $note->getCreatedAt()?->format('d/m/Y H:i:s') ?? '',
            ], $notes),
        ]);
    }

    private function addNote(
        Request $request,
        Patient $patient,
        User $user,
        PatientNoteRepository $noteRepository,
        PatientRepository $patientRepository,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];
        $content = trim((string) ($data['content'] ?? ''));

        if ($content === '') {
            return $this->json(['message' => 'Le contenu de la note est requis.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $note = new PatientNote();
        $note->setPatient($patient);
        $note->setCreatedBy($user);
        $note->setContent($content);

        $em = $patientRepository->getEntityManager();
        $em->persist($note);
        $em->flush();

        return $this->json([
            'message' => 'Note ajoutée avec succès.',
            'note' => [
                'id' => $note->getId(),
                'content' => $note->getContent(),
                'createdBy' => [
                    'id' => $note->getCreatedBy()?->getId(),
                    'name' => $note->getCreatedBy()?->getNom() . ' ' . $note->getCreatedBy()?->getPrenom(),
                ],
                'createdAt' => $note->getCreatedAt()?->format('d/m/Y H:i:s') ?? '',
            ],
        ]);
    }
}
