<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminPannelController extends AbstractController
{
    #[Route(path: '/admin-pannel', name: 'app_admin_pannel')]
    public function index(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $users = $userRepository->findAll();
        
        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route(path: '/admin/user/create', name: 'admin_user_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $data = json_decode($request->getContent(), true);
        
        if (!$data['email'] || !$data['password']) {
            return new JsonResponse(['success' => false, 'message' => 'Email et mot de passe requis'], 400);
        }
        
        // Vérifier si l'email existe déjà
        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return new JsonResponse(['success' => false, 'message' => 'Cet email existe déjà'], 400);
        }
        
        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $user->setRoles($data['roles'] ?? ['ROLE_USER']);
        
        $em->persist($user);
        $em->flush();
        
        return new JsonResponse(['success' => true, 'message' => 'Utilisateur créé avec succès']);
    }

    #[Route(path: '/admin/user/{id<\d+>}/update', name: 'admin_user_update', methods: ['POST'])]
    public function update(int $id, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (!$data['email']) {
            return new JsonResponse(['success' => false, 'message' => 'Email requis'], 400);
        }
        
        // Vérifier si le nouvel email existe déjà (sauf pour cet utilisateur)
        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser && $existingUser->getId() !== $user->getId()) {
            return new JsonResponse(['success' => false, 'message' => 'Cet email existe déjà'], 400);
        }
        
        $user->setEmail($data['email']);
        
        // Mettre à jour le mot de passe seulement s'il est fourni
        if (!empty($data['password'])) {
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        }
        
        $user->setRoles($data['roles'] ?? ['ROLE_USER']);
        
        $em->flush();
        
        return new JsonResponse(['success' => true, 'message' => 'Utilisateur mis à jour avec succès']);
    }

    #[Route(path: '/admin/user/{id<\d+>}/delete', name: 'admin_user_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
        }
        
        // Empêcher la suppression de son propre compte
        if ($user->getId() === $this->getUser()->getId()) {
            return new JsonResponse(['success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte'], 403);
        }
        
        $em->remove($user);
        $em->flush();
        
        return new JsonResponse(['success' => true, 'message' => 'Utilisateur supprimé avec succès']);
    }
}