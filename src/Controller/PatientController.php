<?php

namespace App\Controller;

use App\Entity\Patient;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PatientController extends AbstractController
{
    #[Route('/patient/{id}', name: 'patient_show', methods: ['GET'])]
    public function show(int $id, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $patient = $em->getRepository(Patient::class)->find($id);

        if (!$patient) {
            throw new NotFoundHttpException('Patient not found');
        }

        return $this->render('patient/show.html.twig', [
            'patient' => $patient,
        ]);
    }
}
