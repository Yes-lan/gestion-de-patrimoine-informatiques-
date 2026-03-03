<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Entity\Greffe;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PatientController extends AbstractController
{
    #[Route('/patient/{id<\d+>}', name: 'patient_show', methods: ['GET'])]
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

    #[Route('/patient/create', name: 'patient_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MEDECIN');

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $firstName = $request->request->get('firstName');
            $ville = $request->request->get('ville');
            $isAlive = $request->request->get('isAlive');
            $receiveGreffe = $request->request->get('receiveGreffe');
            $greffeType = $request->request->get('greffeType');

            if (!$name || !$firstName || !$ville || $isAlive === null) {
                $this->addFlash('danger', 'Tous les champs obligatoires doivent être remplis.');
                return $this->redirectToRoute('patient_create');
            }

            $patient = new Patient();
            $patient->setName($name);
            $patient->setFirstName($firstName);
            $patient->setVille($ville);
            $patient->setIsAlive($isAlive === '1');

            $em->persist($patient);

            // If patient should receive a greffe, create it
            if ($receiveGreffe && $greffeType) {
                $greffe = new Greffe();
                $greffe->setType($greffeType);
                $greffe->setPatient($patient);
                $greffe->setFonctionnel(true);
                $greffe->setDateFinDeFonction(new \DateTime('2099-12-31')); // Default future date
                
                $em->persist($greffe);
            }

            $em->flush();

            $this->addFlash('success', 'Patient créé avec succès !');
            return $this->redirectToRoute('patient_show', ['id' => $patient->getId()]);
        }

        return $this->render('patient/create.html.twig');
    }

    #[Route('/patient/{id<\d+>}/export', name: 'patient_export', methods: ['GET'])]
    public function export(int $id, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $patient = $em->getRepository(Patient::class)->find($id);

        if (!$patient) {
            throw new NotFoundHttpException('Patient not found');
        }

        $templatePath = $this->getParameter('kernel.project_dir') . '/templates/fiche_patient.docx';
        // if template missing or empty, create a simple one with placeholders
        if (!file_exists($templatePath) || filesize($templatePath) === 0) {
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $section = $phpWord->addSection();
            $section->addText('Fiche patient');
            $section->addText('Nom : ${name}');
            $section->addText('Prénom : ${firstName}');
            $section->addText('Ville : ${ville}');
            $section->addText('Numéro dossier : ${numDossier}');
            $phpWord->save($templatePath, 'Word2007');
        }

        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);
        $templateProcessor->setValue('name', $patient->getName());
        $templateProcessor->setValue('firstName', $patient->getFirstName());
        $templateProcessor->setValue('ville', $patient->getVille());
        $templateProcessor->setValue('numDossier', $patient->getId());

        $tempFile = tempnam(sys_get_temp_dir(), 'fiche').'.docx';
        $templateProcessor->saveAs($tempFile);

        return $this->file($tempFile, 'fiche_patient_'.$patient->getId().'.docx', ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }
}
