<?php

namespace App\Controller\Api;

use App\Entity\Operation;
use App\Entity\Patient;
use App\Entity\PatientNote;
use App\Entity\PatientPhoto;
use App\Entity\Rapport;
use App\Entity\RendezVous;
use App\Entity\User;
use App\Repository\PatientNoteRepository;
use App\Repository\PatientPhotoRepository;
use App\Repository\PatientRepository;
use App\Repository\RendezVousRepository;
use App\Repository\UserRepository;
use App\Service\MobileJwtService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/mobile', name: 'api_mobile_')]
final class MobileApiController extends AbstractController
{
    public function __construct(
        private readonly MobileJwtService $mobileJwtService,
        #[Autowire(service: 'monolog.logger.failed_login')]
        private readonly LoggerInterface $failedLoginLogger,
        #[Autowire('%kernel.logs_dir%/failed_login.log')]
        private readonly string $failedLoginLogFile,
        #[Autowire('%kernel.project_dir%/public/uploads/patient-photos')]
        private readonly string $photoUploadDir,
    ) {
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
    ): JsonResponse {
        $data = $this->decodeJsonRequest($request);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        $email = mb_strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');
        $clientIp = $request->getClientIp();

        if ($email === '' || $password === '') {
            $payload = [
                'source' => 'mobile_api_login',
                'attempted_login' => $email,
                'ip' => $clientIp,
                'user_agent' => $request->headers->get('User-Agent'),
                'path' => $request->getPathInfo(),
                'failed_at_utc' => (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format(\DateTimeInterface::ATOM),
                'error' => 'missing_credentials',
            ];

            $this->failedLoginLogger->info('Failed mobile login attempt', $payload);
            $this->appendFailedLoginToFile($payload);
            return $this->json(['message' => 'Email et mot de passe requis.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->findOneBy(['email' => $email]);
        if (!$user instanceof User || !$passwordHasher->isPasswordValid($user, $password)) {
            $payload = [
                'source' => 'mobile_api_login',
                'attempted_login' => $email,
                'ip' => $clientIp,
                'user_agent' => $request->headers->get('User-Agent'),
                'path' => $request->getPathInfo(),
                'failed_at_utc' => (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format(\DateTimeInterface::ATOM),
                'error' => 'invalid_credentials',
            ];

            $this->failedLoginLogger->info('Failed mobile login attempt', $payload);
            $this->appendFailedLoginToFile($payload);
            return $this->json(['message' => 'Identifiants invalides.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $token = $this->mobileJwtService->createToken($user);

        return $this->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 86400,
            'user' => $this->serializeUser($user),
        ]);
    }

    private function appendFailedLoginToFile(array $payload): void
    {
        $logDir = \dirname($this->failedLoginLogFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }

        @file_put_contents(
            $this->failedLoginLogFile,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND | LOCK_EX,
        );
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(Request $request, UserRepository $userRepository): JsonResponse
    {
        $user = $this->requireApiUser($request, $userRepository);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->json(['user' => $this->serializeUser($user)]);
    }

    #[Route('/patients', name: 'patients', methods: ['GET'])]
    public function patients(
        Request $request,
        UserRepository $userRepository,
        PatientRepository $patientRepository,
    ): JsonResponse {
        $user = $this->requireApiUser($request, $userRepository);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $q = mb_strtolower(trim((string) $request->query->get('q', '')));
        $qb = $patientRepository->createQueryBuilder('p')->orderBy('p.Name', 'ASC')->addOrderBy('p.FirstName', 'ASC');

        if (!$this->isAdmin($user)) {
            $allowedPatientIds = $patientRepository->findPatientIdsByCaregiver($user);
            if ($allowedPatientIds === []) {
                return $this->json(['patients' => []]);
            }

            $qb->andWhere('p.id IN (:allowedPatientIds)')->setParameter('allowedPatientIds', $allowedPatientIds);
        }

        if ($q !== '') {
            $qb
                ->andWhere('LOWER(p.Name) LIKE :q OR LOWER(p.FirstName) LIKE :q OR LOWER(p.Ville) LIKE :q')
                ->setParameter('q', '%' . $q . '%');
        }

        $patients = $qb->getQuery()->getResult();

        return $this->json([
            'patients' => array_map(fn (Patient $patient): array => $this->serializePatientSummary($patient), $patients),
        ]);
    }

    #[Route('/patients/{id<\d+>}', name: 'patient_show', methods: ['GET'])]
    public function patientShow(
        int $id,
        Request $request,
        UserRepository $userRepository,
        PatientRepository $patientRepository,
        RendezVousRepository $rendezVousRepository,
        PatientPhotoRepository $patientPhotoRepository,
    ): JsonResponse {
        $user = $this->requireApiUser($request, $userRepository);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $patient = $patientRepository->find($id);
        if (!$patient instanceof Patient) {
            return $this->json(['message' => 'Patient introuvable.'], JsonResponse::HTTP_NOT_FOUND);
        }

        if (!$this->canAccessPatient($user, $patient, $patientRepository)) {
            return $this->json(['message' => 'Accès refusé à ce patient.'], JsonResponse::HTTP_FORBIDDEN);
        }

        $rendezVous = $rendezVousRepository->findByPatient($patient);
        $photos = $patientPhotoRepository->findByPatient($patient);
        $operations = $patient->getOperations()->toArray();

        usort($operations, static fn (Operation $left, Operation $right): int => ($right->getDateOperation()?->getTimestamp() ?? 0) <=> ($left->getDateOperation()?->getTimestamp() ?? 0));

        $rapports = [];
        foreach ($operations as $operation) {
            foreach ($operation->getRapports() as $rapport) {
                if ($rapport instanceof Rapport) {
                    $rapports[] = $rapport;
                }
            }
        }

        usort($rapports, static fn (Rapport $left, Rapport $right): int => ($right->getDateCreation()?->getTimestamp() ?? 0) <=> ($left->getDateCreation()?->getTimestamp() ?? 0));

        return $this->json([
            'patient' => $this->serializePatientDetail($patient, $operations, $rendezVous, $rapports, $photos, $request),
        ]);
    }

    #[Route('/rdv', name: 'rdv', methods: ['GET'])]
    public function rendezVous(
        Request $request,
        UserRepository $userRepository,
        PatientRepository $patientRepository,
        RendezVousRepository $rendezVousRepository,
    ): JsonResponse {
        $user = $this->requireApiUser($request, $userRepository);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $patientId = (int) $request->query->get('patientId', 0);
        $qb = $rendezVousRepository->createQueryBuilder('r')
            ->leftJoin('r.patient', 'p')
            ->addSelect('p')
            ->orderBy('r.scheduledAt', 'ASC');

        if ($patientId > 0) {
            $qb->andWhere('p.id = :patientId')->setParameter('patientId', $patientId);
        }

        if (!$this->isAdmin($user)) {
            $allowedPatientIds = $patientRepository->findPatientIdsByCaregiver($user);
            if ($allowedPatientIds === []) {
                return $this->json(['rdv' => []]);
            }

            $qb->andWhere('p.id IN (:allowedPatientIds)')->setParameter('allowedPatientIds', $allowedPatientIds);
        }

        $items = $qb->getQuery()->getResult();

        return $this->json([
            'rdv' => array_map(fn (RendezVous $item): array => $this->serializeRendezVous($item), $items),
        ]);
    }

    #[Route('/patients/{id<\d+>}/photos', name: 'patient_photos', methods: ['GET'])]
    public function patientPhotos(
        int $id,
        Request $request,
        UserRepository $userRepository,
        PatientRepository $patientRepository,
        PatientPhotoRepository $patientPhotoRepository,
    ): JsonResponse {
        $user = $this->requireApiUser($request, $userRepository);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $patient = $patientRepository->find($id);
        if (!$patient instanceof Patient) {
            return $this->json(['message' => 'Patient introuvable.'], JsonResponse::HTTP_NOT_FOUND);
        }

        if (!$this->canAccessPatient($user, $patient, $patientRepository)) {
            return $this->json(['message' => 'Accès refusé à ce patient.'], JsonResponse::HTTP_FORBIDDEN);
        }

        $photos = $patientPhotoRepository->findByPatient($patient);

        return $this->json([
            'photos' => array_map(fn (PatientPhoto $photo): array => $this->serializePhoto($photo, $request), $photos),
        ]);
    }

    #[Route('/patients/{id<\d+>}/photos', name: 'patient_photos_upload', methods: ['POST'])]
    public function uploadPatientPhoto(
        int $id,
        Request $request,
        UserRepository $userRepository,
        PatientRepository $patientRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $user = $this->requireApiUser($request, $userRepository);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $patient = $patientRepository->find($id);
        if (!$patient instanceof Patient) {
            return $this->json(['message' => 'Patient introuvable.'], JsonResponse::HTTP_NOT_FOUND);
        }

        if (!$this->canAccessPatient($user, $patient, $patientRepository)) {
            return $this->json(['message' => 'Accès refusé à ce patient.'], JsonResponse::HTTP_FORBIDDEN);
        }

        $file = $request->files->get('photo');
        if (!$file instanceof UploadedFile) {
            return $this->json(['message' => 'Fichier photo requis.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $mimeType = (string) ($file->getMimeType() ?? '');
        if (!str_starts_with($mimeType, 'image/')) {
            return $this->json(['message' => 'Le fichier doit être une image.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $extension = $file->guessExtension() ?: ($file->getClientOriginalExtension() ?: 'bin');
        $filename = sprintf('%d_%s.%s', $patient->getId(), bin2hex(random_bytes(8)), $extension);
        $file->move($this->photoUploadDir, $filename);

        $photo = new PatientPhoto();
        $photo
            ->setPatient($patient)
            ->setUploadedBy($user)
            ->setFilename($filename)
            ->setOriginalName((string) $file->getClientOriginalName())
            ->setMimeType($mimeType)
            ->setCaption(trim((string) $request->request->get('caption', '')) ?: null);

        $entityManager->persist($photo);
        $entityManager->flush();

        return $this->json([
            'photo' => $this->serializePhoto($photo, $request),
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/patients/{id<\d+>}/notes', name: 'patient_notes', methods: ['GET'])]
    public function patientNotes(
        int $id,
        Request $request,
        UserRepository $userRepository,
        PatientRepository $patientRepository,
        PatientNoteRepository $patientNoteRepository,
    ): JsonResponse {
        $user = $this->requireApiUser($request, $userRepository);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $patient = $patientRepository->find($id);
        if (!$patient instanceof Patient) {
            return $this->json(['message' => 'Patient introuvable.'], JsonResponse::HTTP_NOT_FOUND);
        }

        if (!$this->canAccessPatient($user, $patient, $patientRepository)) {
            return $this->json(['message' => 'Accès refusé à ce patient.'], JsonResponse::HTTP_FORBIDDEN);
        }

        $notes = $patientNoteRepository->createQueryBuilder('n')
            ->where('n.patient = :patient')
            ->setParameter('patient', $patient)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->json([
            'notes' => array_map(fn (PatientNote $note): array => $this->serializePatientNote($note), $notes),
        ]);
    }

    #[Route('/patients/{id<\d+>}/notes', name: 'patient_notes_add', methods: ['POST'])]
    public function addPatientNote(
        int $id,
        Request $request,
        UserRepository $userRepository,
        PatientRepository $patientRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $user = $this->requireApiUser($request, $userRepository);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $patient = $patientRepository->find($id);
        if (!$patient instanceof Patient) {
            return $this->json(['message' => 'Patient introuvable.'], JsonResponse::HTTP_NOT_FOUND);
        }

        if (!$this->canAccessPatient($user, $patient, $patientRepository)) {
            return $this->json(['message' => 'Accès refusé à ce patient.'], JsonResponse::HTTP_FORBIDDEN);
        }

        $data = $this->decodeJsonRequest($request);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        $content = trim((string) ($data['content'] ?? ''));
        if ($content === '') {
            return $this->json(['message' => 'La note ne peut pas être vide.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $note = new PatientNote();
        $note->setPatient($patient);
        $note->setCreatedBy($user);
        $note->setContent($content);

        $entityManager->persist($note);
        $entityManager->flush();

        return $this->json([
            'note' => $this->serializePatientNote($note),
        ], JsonResponse::HTTP_CREATED);
    }

    private function decodeJsonRequest(Request $request): array|JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->json(['message' => 'JSON invalide.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        return is_array($data) ? $data : [];
    }

    private function requireApiUser(Request $request, UserRepository $userRepository): User|JsonResponse
    {
        $authorization = (string) $request->headers->get('Authorization', '');
        if (!str_starts_with($authorization, 'Bearer ')) {
            return $this->json(['message' => 'Token Bearer requis.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $token = trim(substr($authorization, 7));
        $payload = $this->mobileJwtService->decodeToken($token);
        if (!is_array($payload) || !isset($payload['sub'])) {
            return $this->json(['message' => 'Token invalide ou expiré.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $user = $userRepository->find((int) $payload['sub']);
        if (!$user instanceof User) {
            return $this->json(['message' => 'Utilisateur introuvable.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return $user;
    }

    private function isAdmin(User $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }

    private function canAccessPatient(User $user, Patient $patient, PatientRepository $patientRepository): bool
    {
        return $this->isAdmin($user) || $patientRepository->userCanAccessPatient($user, (int) $patient->getId());
    }

    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ];
    }

    private function serializePatientNote(PatientNote $note): array
    {
        return [
            'id' => $note->getId(),
            'content' => $note->getContent(),
            'createdBy' => [
                'id' => $note->getCreatedBy()?->getId(),
                'email' => $note->getCreatedBy()?->getEmail(),
                'name' => $note->getCreatedBy()?->getPrenom() . ' ' . $note->getCreatedBy()?->getNom(),
            ],
            'createdAt' => $note->getCreatedAt()?->format('c'),
            'updatedAt' => $note->getUpdatedAt()?->format('c'),
        ];
    }

    private function serializePatientSummary(Patient $patient): array
    {
        return [
            'id' => $patient->getId(),
            'name' => $patient->getName(),
            'firstName' => $patient->getFirstName(),
            'fullName' => trim((string) $patient->getName() . ' ' . (string) $patient->getFirstName()),
            'city' => $patient->getVille(),
            'alive' => $patient->isAlive(),
            'greffesCount' => $patient->getGreffes()->count(),
            'operationsCount' => $patient->getOperations()->count(),
        ];
    }

    /**
     * @param Operation[] $operations
     * @param RendezVous[] $rendezVous
     * @param Rapport[] $rapports
     * @param PatientPhoto[] $photos
     */
    private function serializePatientDetail(
        Patient $patient,
        array $operations,
        array $rendezVous,
        array $rapports,
        array $photos,
        Request $request,
    ): array {
        return [
            'id' => $patient->getId(),
            'name' => $patient->getName(),
            'firstName' => $patient->getFirstName(),
            'fullName' => trim((string) $patient->getName() . ' ' . (string) $patient->getFirstName()),
            'city' => $patient->getVille(),
            'alive' => $patient->isAlive(),
            'email' => $patient->getUser()?->getEmail(),
            'greffes' => array_map(static fn ($greffe): array => [
                'id' => $greffe->getId(),
                'type' => $greffe->getType(),
                'fonctionnel' => $greffe->isFonctionnel(),
                'dateFinDeFonction' => $greffe->getDateFinDeFonction()?->format(DATE_ATOM),
            ], $patient->getGreffes()->toArray()),
            'operations' => array_map(fn (Operation $operation): array => [
                'id' => $operation->getId(),
                'title' => $operation->getTitre(),
                'description' => $operation->getDescription(),
                'dateOperation' => $operation->getDateOperation()?->format(DATE_ATOM),
                'chirurgiensCount' => $operation->getChirurgiens()->count(),
                'infirmieresCount' => $operation->getInfirmieres()->count(),
            ], $operations),
            'rapports' => array_map(fn (Rapport $rapport): array => [
                'id' => $rapport->getId(),
                'title' => $rapport->getTitre(),
                'text' => $rapport->getContenuTexte(),
                'html' => $rapport->getContenuHtml(),
                'status' => $rapport->getStatut(),
                'createdAt' => $rapport->getDateCreation()?->format(DATE_ATOM),
                'updatedAt' => $rapport->getDateModification()?->format(DATE_ATOM),
                'operationId' => $rapport->getOperation()?->getId(),
            ], array_slice($rapports, 0, 20)),
            'rdv' => array_map(fn (RendezVous $item): array => $this->serializeRendezVous($item), $rendezVous),
            'photos' => array_map(fn (PatientPhoto $photo): array => $this->serializePhoto($photo, $request), $photos),
        ];
    }

    private function serializeRendezVous(RendezVous $rendezVous): array
    {
        return [
            'id' => $rendezVous->getId(),
            'title' => $rendezVous->getTitle(),
            'scheduledAt' => $rendezVous->getScheduledAt()?->format(DATE_ATOM),
            'location' => $rendezVous->getLocation(),
            'status' => $rendezVous->getStatus(),
            'notes' => $rendezVous->getNotes(),
            'createdAt' => $rendezVous->getCreatedAt()?->format(DATE_ATOM),
            'patient' => [
                'id' => $rendezVous->getPatient()?->getId(),
                'fullName' => trim((string) $rendezVous->getPatient()?->getName() . ' ' . (string) $rendezVous->getPatient()?->getFirstName()),
            ],
        ];
    }

    private function serializePhoto(PatientPhoto $photo, Request $request): array
    {
        return [
            'id' => $photo->getId(),
            'caption' => $photo->getCaption(),
            'filename' => $photo->getFilename(),
            'originalName' => $photo->getOriginalName(),
            'mimeType' => $photo->getMimeType(),
            'createdAt' => $photo->getCreatedAt()?->format(DATE_ATOM),
            'url' => $request->getSchemeAndHttpHost() . '/uploads/patient-photos/' . rawurlencode((string) $photo->getFilename()),
        ];
    }
}
