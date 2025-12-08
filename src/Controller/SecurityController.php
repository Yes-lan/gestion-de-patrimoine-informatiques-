<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends AbstractController
{
    #[Route(path: '/', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        // Si c'est une requête AJAX
        if ($request->isXmlHttpRequest()) {
            $user = $this->getUser();

            if ($user) {
                return new JsonResponse([
                    'success' => true,
                    'email' => $user->getUserIdentifier()
                ]);
            } else {
                return new JsonResponse([
                    'success' => false
                ]);
            }
        }

        // Requête normale (page affichée au départ)
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'user' => $this->getUser()
        ]);
    }
}
