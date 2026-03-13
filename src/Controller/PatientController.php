<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Entity\Greffe;
use App\Entity\User;
use App\Repository\PatientRepository;
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
    /**
     * @param User[] $users
     * @return User[]
     */
    private function filterCareStaffUsers(array $users): array
    {
        return array_values(array_filter($users, static function (User $user): bool {
            $roles = $user->getRoles();

            return in_array('ROLE_ADMIN', $roles, true)
                || in_array('ROLE_MEDECIN', $roles, true)
                || in_array('ROLE_INFIRMIERE', $roles, true)
                || in_array('ROLE_CHIRURGIEN', $roles, true);
        }));
    }

    #[Route('/patient/{id<\d+>}', name: 'patient_show', methods: ['GET'])]
    public function show(int $id, ManagerRegistry $doctrine, PatientRepository $patientRepository): Response
    {
        $em = $doctrine->getManager();
        $patient = $em->getRepository(Patient::class)->find($id);

        if (!$patient) {
            throw new NotFoundHttpException('Patient not found');
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            $user = $this->getUser();
            if (!$user instanceof User || !$patientRepository->userCanAccessPatient($user, $patient->getId())) {
                throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à ce patient.');
            }
        }

        return $this->render('patient/show.html.twig', [
            'patient' => $patient,
        ]);
    }

    #[Route('/patient/create', name: 'patient_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_MEDECIN') && !$this->isGranted('ROLE_INFIRMIERE') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Accès réservé au personnel soignant.');
        }

        $allUsers = $em->getRepository(User::class)->findBy([], ['email' => 'ASC']);
        $careStaffUsers = $this->filterCareStaffUsers($allUsers);

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $firstName = $request->request->get('firstName');
            $ville = $request->request->get('ville');
            $isAlive = $request->request->get('isAlive');
            $receiveGreffe = $request->request->get('receiveGreffe');
            $greffeType = $request->request->get('greffeType');
            $caregiverIds = array_map('intval', (array) $request->request->all('caregiverIds'));

            if (!$name || !$firstName || !$ville || $isAlive === null) {
                $this->addFlash('danger', 'Tous les champs obligatoires doivent être remplis.');
                return $this->redirectToRoute('patient_create');
            }

            if ($caregiverIds === []) {
                $this->addFlash('danger', 'Vous devez sélectionner au moins un membre du personnel pour ce patient.');
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

            $currentUser = $this->getUser();

            foreach ($caregiverIds as $memberId) {
                $member = $em->getRepository(User::class)->find($memberId);
                if ($member instanceof User) {
                    $roles = $member->getRoles();
                    if (
                        in_array('ROLE_ADMIN', $roles, true)
                        || in_array('ROLE_MEDECIN', $roles, true)
                        || in_array('ROLE_INFIRMIERE', $roles, true)
                        || in_array('ROLE_CHIRURGIEN', $roles, true)
                    ) {
                        $patient->addCaregiver($member);
                    }
                }
            }

            if ($currentUser instanceof User) {
                $patient->addCaregiver($currentUser);
            }

            $em->flush();

            $this->addFlash('success', 'Patient créé avec succès !');
            return $this->redirectToRoute('patient_show', ['id' => $patient->getId()]);
        }

        return $this->render('patient/create.html.twig', [
            'careStaffUsers' => $careStaffUsers,
        ]);
    }

    #[Route('/patient/{id<\d+>}/export', name: 'patient_export', methods: ['GET'])]
    public function export(int $id, ManagerRegistry $doctrine, PatientRepository $patientRepository): Response
    {
        $em = $doctrine->getManager();
        $patient = $em->getRepository(Patient::class)->find($id);

        if (!$patient) {
            throw new NotFoundHttpException('Patient not found');
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            $user = $this->getUser();
            if (!$user instanceof User || !$patientRepository->userCanAccessPatient($user, $patient->getId())) {
                throw $this->createAccessDeniedException('Vous ne pouvez pas exporter ce patient.');
            }
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
            $section->addText('Chirurgien : ${chirurgien}');
            $section->addText('Infirmière : ${infirmiere}');
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
