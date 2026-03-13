<?php

namespace App\Entity;

use App\Repository\RapportRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RapportRepository::class)]
#[ORM\Table(name: 'rapport')]
class Rapport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Operation::class, inversedBy: 'rapports')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Operation $operation = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $auteur = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire')]
    #[Assert\Length(min: 5, max: 255)]
    private ?string $titre = null;

    /**
     * Le contenu HTML riche stocké en base de données
     * Exemple: <p>Ceci est un <b>rapport</b></p>
     */
    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Le contenu est obligatoire')]
    private ?string $contenuHtml = null;

    /**
     * Copie du texte sans balises HTML (pour la recherche)
     * Généré automatiquement à partir de contenuHtml
     */
    #[ORM\Column(type: 'text')]
    private ?string $contenuTexte = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $dateModification = null;

    /**
     * Versionning automatique (Doctrine)
     * Permet de rollback à une version antérieure
     */
    #[ORM\Version]
    #[ORM\Column(type: 'integer')]
    private int $version = 1;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $statut = 'brouillon';

    // ========== GETTERS/SETTERS ==========

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOperation(): ?Operation
    {
        return $this->operation;
    }

    public function setOperation(?Operation $operation): self
    {
        $this->operation = $operation;
        return $this;
    }

    public function getAuteur(): ?User
    {
        return $this->auteur;
    }

    public function setAuteur(?User $auteur): self
    {
        $this->auteur = $auteur;
        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getContenuHtml(): ?string
    {
        return $this->contenuHtml;
    }

    public function setContenuHtml(string $contenuHtml): self
    {
        $this->contenuHtml = $contenuHtml;
        // Générer automatiquement le texte sans HTML pour la recherche
        $this->contenuTexte = strip_tags($contenuHtml);
        return $this;
    }

    public function getContenuTexte(): ?string
    {
        return $this->contenuTexte;
    }

    public function setContenuTexte(string $contenuTexte): self
    {
        $this->contenuTexte = $contenuTexte;
        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTime $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getDateModification(): ?\DateTime
    {
        return $this->dateModification;
    }

    public function setDateModification(\DateTime $dateModification): self
    {
        $this->dateModification = $dateModification;
        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }
}
