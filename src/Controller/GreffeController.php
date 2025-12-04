<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class GreffeController extends AbstractController
{
    #[Route('/greffe', name: 'app_greffe')]
    public function index(): JsonResponse
    {
        return $this->json([

            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/GreffeController.php',
        ]);
    }
}
