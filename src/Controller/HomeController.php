<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route(path: '/', name: 'app_home')]
    public function index(): Response
    {
        // Si l'utilisateur est connecté, afficher le dashboard
        if ($this->getUser()) {
            return $this->render('home/index.html.twig');
        }

        // Sinon, rediriger vers la page de login
        return $this->redirectToRoute('app_login');
    }
}
