<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/', name: 'app_login')]
public function login(AuthenticationUtils $authenticationUtils): Response
{
    // Récupère l'erreur de connexion
    $error = $authenticationUtils->getLastAuthenticationError();

    // Dernier email entré
    $lastUsername = $authenticationUtils->getLastUsername();

    // Vérifie si l'utilisateur est connecté
    $user = $this->getUser();

    return $this->render('security/login.html.twig', [
        'last_username' => $lastUsername,
        // Si erreur → "User invalide", sinon null
        'error' => $error ? 'User invalide' : null,
        'user' => $user,
    ]);
}


    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
