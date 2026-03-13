<?php

namespace App\Controller;

use App\Entity\Rapport;
use App\Entity\Operation;
use App\Entity\User;
use App\Form\RapportType;
use App\Repository\RapportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/rapport')]
class RapportController extends AbstractController
{
    private function assertParticipant(Operation $operation): void
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return;
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        foreach ($operation->getMedecins() as $medecin) {
            if ($medecin->getUser()?->getId() === $user->getId()) {
                return;
            }
        }

        foreach ($operation->getInfirmieres() as $infirmiere) {
            if ($infirmiere->getUser()?->getId() === $user->getId()) {
                return;
            }
        }

        throw $this->createAccessDeniedException('Seuls les participants à l\'opération peuvent écrire dans ce rapport.');
    }

    // ========== CRÉER UN RAPPORT ==========
    #[Route('/operation/{id}/nouveau', name: 'rapport_creer', methods: ['GET', 'POST'])]
    public function creer(
        Operation $operation,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->assertParticipant($operation);

        $rapport = new Rapport();
        $rapport->setOperation($operation);
        
        $form = $this->createForm(RapportType::class, $rapport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rapport->setDateCreation(new \DateTime());
            $rapport->setDateModification(new \DateTime());
            $rapport->setAuteur($this->getUser());

            $em->persist($rapport);
            $em->flush();

            $this->addFlash('success', 'Rapport créé avec succès');
            return $this->redirectToRoute('rapport_afficher', ['id' => $rapport->getId()]);
        }

        return $this->render('rapport/creer.html.twig', [
            'form' => $form->createView(),
            'operation' => $operation,
        ]);
    }

    // ========== AFFICHER UN RAPPORT ==========
    #[Route('/{id}', name: 'rapport_afficher', methods: ['GET'])]
    public function afficher(Rapport $rapport): Response
    {
        $this->assertParticipant($rapport->getOperation());

        return $this->render('rapport/afficher.html.twig', [
            'rapport' => $rapport,
        ]);
    }

    // ========== ÉDITER UN RAPPORT ==========
    #[Route('/{id}/editer', name: 'rapport_editer', methods: ['GET', 'POST'])]
    public function editer(
        Rapport $rapport,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->assertParticipant($rapport->getOperation());

        $form = $this->createForm(RapportType::class, $rapport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rapport->setDateModification(new \DateTime());

            $em->flush();

            $this->addFlash('success', 'Rapport modifié avec succès');
            return $this->redirectToRoute('rapport_afficher', ['id' => $rapport->getId()]);
        }

        return $this->render('rapport/editer.html.twig', [
            'form' => $form->createView(),
            'rapport' => $rapport,
        ]);
    }

    // ========== SUPPRIMER UN RAPPORT ==========
    #[Route('/{id}/supprimer', name: 'rapport_supprimer', methods: ['POST'])]
    public function supprimer(
        Rapport $rapport,
        EntityManagerInterface $em
    ): Response {
        $this->assertParticipant($rapport->getOperation());

        $operationId = $rapport->getOperation()->getId();
        
        $em->remove($rapport);
        $em->flush();

        $this->addFlash('success', 'Rapport supprimé');
        return $this->redirectToRoute('rapport_liste', ['operationId' => $operationId]);
    }

    // ========== LISTER RAPPORTS D'UNE OPÉRATION ==========
    #[Route('/operation/{operationId}/liste', name: 'rapport_liste')]
    public function lister(
        int $operationId,
        EntityManagerInterface $em,
        RapportRepository $rapportRepo
    ): Response {
        $operation = $em->getRepository(Operation::class)->find($operationId);
        
        if (!$operation) {
            throw $this->createNotFoundException('Opération non trouvée');
        }

        $this->assertParticipant($operation);

        $rapports = $rapportRepo->findByOperation($operation);

        return $this->render('rapport/liste.html.twig', [
            'operation' => $operation,
            'rapports' => $rapports,
        ]);
    }

    // ========== RECHERCHER DANS LES RAPPORTS ==========
    #[Route('/operation/{operationId}/recherche', name: 'rapport_recherche')]
    public function rechercher(
        int $operationId,
        Request $request,
        EntityManagerInterface $em,
        RapportRepository $rapportRepo
    ): Response {
        $operation = $em->getRepository(Operation::class)->find($operationId);
        
        if (!$operation) {
            throw $this->createNotFoundException('Opération non trouvée');
        }

        $this->assertParticipant($operation);

        $query = $request->query->get('q', '');
        $rapports = [];

        if (!empty($query)) {
            $rapports = $rapportRepo->searchByText($query, $operation);
        }

        return $this->render('rapport/recherche.html.twig', [
            'operation' => $operation,
            'rapports' => $rapports,
            'query' => $query,
        ]);
    }
}
