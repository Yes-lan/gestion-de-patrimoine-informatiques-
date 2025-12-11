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
        if ($this->getUser()) {
            return $this->render('home/index.html.twig');
        }

        return $this->redirectToRoute('app_login');
}

    
}
