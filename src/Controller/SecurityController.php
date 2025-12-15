<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, UserRepository $userRepository): Response
    {
        // si déjà connecté -> redirige vers greffe
        if ($this->getUser()) {
            return $this->redirectToRoute('app_greffe');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // si erreur d'authentification, préciser si l'utilisateur existe ou non
        if ($error) {
            $user = null;
            try {
                if ($lastUsername) {
                    // adapte la clé si tu utilises "username" au lieu de "email"
                    $user = $userRepository->findOneBy(['email' => $lastUsername]);
                }
            } catch (\Throwable $e) {
                // éviter 500 si la requête vers la BDD a échoué — loguer et afficher message générique
                $this->addFlash('danger', "Erreur interne. Veuillez réessayer plus tard.");
                // optionnel : log l'exception si un logger est disponible
                if ($this->container->has('logger')) {
                    $this->container->get('logger')->error('Erreur recherche user lors login: '.$e->getMessage());
                }
                return $this->render('security/login.html.twig', [
                    'last_username' => $lastUsername,
                    'error' => $error,
                ]);
            }

            if (! $user) {
                $this->addFlash('danger', "Identifiant incorrect — utilisateur introuvable.");
            } else {
                $this->addFlash('danger', "Mot de passe incorrect.");
            }
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
