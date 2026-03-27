<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Entity\User;
use App\Repository\PatientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AdminPannelController extends AbstractController
{
    #[Route(path: '/admin-pannel', name: 'app_admin_pannel', methods: ['GET'])]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/hub.html.twig');
    }

    #[Route(path: '/admin/patients', name: 'admin_patients', methods: ['GET'])]
    public function patients(PatientRepository $patientRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/patients.html.twig', [
            'patients' => $patientRepository->findAll(),
        ]);
    }

    #[Route(path: '/admin/patient/create', name: 'admin_patient_create', methods: ['POST'])]
    public function createPatient(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true);

        if (empty($data['name']) || empty($data['firstName']) || empty($data['ville']) || empty($data['email']) || empty($data['password']) || !array_key_exists('alive', $data)) {
            return new JsonResponse(['success' => false, 'message' => 'Tous les champs sont requis'], 400);
        }

        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return new JsonResponse(['success' => false, 'message' => 'Cet email existe déjà'], 400);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setRoles(['ROLE_PATIENT']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));

        $patient = new Patient();
        $patient->setName($data['name']);
        $patient->setFirstName($data['firstName']);
        $patient->setVille($data['ville']);
        $patient->setIsAlive((bool) $data['alive']);
        $patient->setUser($user);

        $em->persist($patient);
        $em->flush();

        return new JsonResponse(['success' => true, 'message' => 'Patient créé avec succès']);
    }

    #[Route(path: '/admin/patient/{id<\d+>}/update', name: 'admin_patient_update', methods: ['POST'])]
    public function updatePatient(int $id, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $patient = $em->getRepository(Patient::class)->find($id);
        if (!$patient) {
            return new JsonResponse(['success' => false, 'message' => 'Patient non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['name']) || empty($data['firstName']) || empty($data['ville']) || empty($data['email']) || !array_key_exists('alive', $data)) {
            return new JsonResponse(['success' => false, 'message' => 'Nom, prénom, ville et email sont requis'], 400);
        }

        $user = $patient->getUser();
        if (!$user) {
            $user = new User();
            $patient->setUser($user);
            $user->setRoles(['ROLE_PATIENT']);
        }

        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser && $existingUser->getId() !== $user->getId()) {
            return new JsonResponse(['success' => false, 'message' => 'Cet email existe déjà'], 400);
        }

        $patient->setName($data['name']);
        $patient->setFirstName($data['firstName']);
        $patient->setVille($data['ville']);
        $patient->setIsAlive((bool) $data['alive']);

        $user->setEmail($data['email']);
        $user->setRoles(['ROLE_PATIENT']);
        if (!empty($data['password'])) {
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        }

        $em->flush();

        return new JsonResponse(['success' => true, 'message' => 'Patient mis à jour avec succès']);
    }

    #[Route(path: '/admin/patient/{id<\d+>}/delete', name: 'admin_patient_delete', methods: ['DELETE'])]
    public function deletePatient(int $id, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $patient = $em->getRepository(Patient::class)->find($id);
        if (!$patient) {
            return new JsonResponse(['success' => false, 'message' => 'Patient non trouvé'], 404);
        }

        $linkedUser = $patient->getUser();
        if ($linkedUser && $this->getUser() && $linkedUser->getId() === $this->getUser()->getId()) {
            return new JsonResponse(['success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte'], 403);
        }

        $em->remove($patient);
        $em->flush();

        return new JsonResponse(['success' => true, 'message' => 'Patient supprimé avec succès']);
    }

    #[Route(path: '/admin/medecins', name: 'admin_medecins', methods: ['GET'])]
    public function medecins(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/staff.html.twig', [
            'title' => 'Gestion des Médecins',
            'staffLabel' => 'médecin',
            'staffType' => 'medecin',
            'users' => $this->filterUsersByRole($userRepository->findAll(), 'ROLE_MEDECIN'),
        ]);
    }

    #[Route(path: '/admin/infirmieres', name: 'admin_infirmieres', methods: ['GET'])]
    public function infirmieres(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/staff.html.twig', [
            'title' => 'Gestion des Infirmières',
            'staffLabel' => 'infirmière',
            'staffType' => 'infirmiere',
            'users' => $this->filterUsersByRole($userRepository->findAll(), 'ROLE_INFIRMIERE'),
        ]);
    }

    #[Route(path: '/admin/chirurgiens', name: 'admin_chirurgiens', methods: ['GET'])]
    public function chirurgiens(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/staff.html.twig', [
            'title' => 'Gestion des Chirurgiens',
            'staffLabel' => 'chirurgien',
            'staffType' => 'chirurgien',
            'users' => $this->filterUsersByRole($userRepository->findAll(), 'ROLE_CHIRURGIEN'),
        ]);
    }

    #[Route(path: '/admin/staff/{type}/create', name: 'admin_staff_create', methods: ['POST'])]
    public function createStaff(string $type, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true);

        $role = $this->resolveStaffRole($type);
        if (!$role) {
            return new JsonResponse(['success' => false, 'message' => 'Type de profil invalide'], 400);
        }

        if (empty($data['email']) || empty($data['password']) || empty($data['nom']) || empty($data['prenom'])) {
            return new JsonResponse(['success' => false, 'message' => 'Tous les champs sont requis'], 400);
        }

        $existingStaffByEmail = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingStaffByEmail) {
            return new JsonResponse(['success' => false, 'message' => 'Cet email existe déjà'], 400);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setNom($data['nom']);
        $user->setPrenom($data['prenom']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $user->setRoles([$role]);

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['success' => true, 'message' => ucfirst($type) . ' créé(e) avec succès']);
    }

    #[Route(path: '/admin/staff/{type}/{id<\d+>}/update', name: 'admin_staff_update', methods: ['POST'])]
    public function updateStaff(string $type, int $id, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true);

        $role = $this->resolveStaffRole($type);
        if (!$role) {
            return new JsonResponse(['success' => false, 'message' => 'Type de profil invalide'], 400);
        }

        $user = $em->getRepository(User::class)->find($id);
        if (!$user || !in_array($role, $user->getRoles(), true)) {
            return new JsonResponse(['success' => false, 'message' => ucfirst($type) . ' non trouvé(e)'], 404);
        }

        if (empty($data['email']) || empty($data['nom']) || empty($data['prenom'])) {
            return new JsonResponse(['success' => false, 'message' => 'Email, nom et prénom sont requis'], 400);
        }

        $linkedUserWithSameEmail = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($linkedUserWithSameEmail && $linkedUserWithSameEmail->getId() !== $user->getId()) {
            return new JsonResponse(['success' => false, 'message' => 'Cet email existe déjà'], 400);
        }

        $user->setEmail($data['email']);
        $user->setNom($data['nom']);
        $user->setPrenom($data['prenom']);
        if (!empty($data['password'])) {
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        }
        $user->setRoles([$role]);

        $em->flush();

        return new JsonResponse(['success' => true, 'message' => ucfirst($type) . ' mis(e) à jour avec succès']);
    }

    #[Route(path: '/admin/staff/{type}/{id<\d+>}/delete', name: 'admin_staff_delete', methods: ['DELETE'])]
    public function deleteStaff(string $type, int $id, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $role = $this->resolveStaffRole($type);
        if (!$role) {
            return new JsonResponse(['success' => false, 'message' => 'Type de profil invalide'], 400);
        }

        $user = $em->getRepository(User::class)->find($id);
        if (!$user || !in_array($role, $user->getRoles(), true)) {
            return new JsonResponse(['success' => false, 'message' => ucfirst($type) . ' non trouvé(e)'], 404);
        }

        if ($user && $this->getUser() && $user->getId() === $this->getUser()->getId()) {
            return new JsonResponse(['success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte'], 403);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(['success' => true, 'message' => ucfirst($type) . ' supprimé(e) avec succès']);
    }

    private function resolveStaffRole(string $type): ?string
    {
        return match ($type) {
            'medecin' => 'ROLE_MEDECIN',
            'chirurgien' => 'ROLE_CHIRURGIEN',
            'infirmiere' => 'ROLE_INFIRMIERE',
            default => null,
        };
    }

    /**
     * @param User[] $users
     * @return User[]
     */
    private function filterUsersByRole(array $users, string $role): array
    {
        return array_values(array_filter($users, static fn (User $user) => in_array($role, $user->getRoles(), true)));
    }
}
