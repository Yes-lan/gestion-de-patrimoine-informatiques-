<?php

namespace App\Entity;

use App\Repository\OperationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OperationRepository::class)]
#[ORM\Table(name: 'operation')]
class Operation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'operations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(name: 'date_operation', type: 'datetime')]
    private ?\DateTime $dateOperation = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'nb_medecins', type: 'integer', options: ['default' => 0])]
    private int $nbMedecins = 0;

    #[ORM\Column(name: 'nb_infirmieres', type: 'integer', options: ['default' => 0])]
    private int $nbInfirmieres = 0;

    /**
     * @var Collection<int, Medecin>
     */
    #[ORM\ManyToMany(targetEntity: Medecin::class)]
    #[ORM\JoinTable(name: 'operation_medecin')]
    private Collection $medecins;

    /**
     * @var Collection<int, Infirmiere>
     */
    #[ORM\ManyToMany(targetEntity: Infirmiere::class)]
    #[ORM\JoinTable(name: 'operation_infirmiere')]
    private Collection $infirmieres;

    /**
     * @var Collection<int, Rapport>
     */
    #[ORM\OneToMany(targetEntity: Rapport::class, mappedBy: 'operation', orphanRemoval: true)]
    private Collection $rapports;

    public function __construct()
    {
        $this->medecins = new ArrayCollection();
        $this->infirmieres = new ArrayCollection();
        $this->rapports = new ArrayCollection();
        $this->dateOperation = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): static
    {
        $this->patient = $patient;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDateOperation(): ?\DateTime
    {
        return $this->dateOperation;
    }

    public function setDateOperation(\DateTime $dateOperation): static
    {
        $this->dateOperation = $dateOperation;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getNbMedecins(): int
    {
        return $this->nbMedecins;
    }

    public function setNbMedecins(int $nbMedecins): static
    {
        $this->nbMedecins = $nbMedecins;

        return $this;
    }

    public function getNbInfirmieres(): int
    {
        return $this->nbInfirmieres;
    }

    public function setNbInfirmieres(int $nbInfirmieres): static
    {
        $this->nbInfirmieres = $nbInfirmieres;

        return $this;
    }

    /**
     * @return Collection<int, Medecin>
     */
    public function getMedecins(): Collection
    {
        return $this->medecins;
    }

    public function addMedecin(Medecin $medecin): static
    {
        if (!$this->medecins->contains($medecin)) {
            $this->medecins->add($medecin);
        }

        return $this;
    }

    public function removeMedecin(Medecin $medecin): static
    {
        $this->medecins->removeElement($medecin);

        return $this;
    }

    /**
     * @return Collection<int, Infirmiere>
     */
    public function getInfirmieres(): Collection
    {
        return $this->infirmieres;
    }

    public function addInfirmiere(Infirmiere $infirmiere): static
    {
        if (!$this->infirmieres->contains($infirmiere)) {
            $this->infirmieres->add($infirmiere);
        }

        return $this;
    }

    public function removeInfirmiere(Infirmiere $infirmiere): static
    {
        $this->infirmieres->removeElement($infirmiere);

        return $this;
    }

    /**
     * @return Collection<int, Rapport>
     */
    public function getRapports(): Collection
    {
        return $this->rapports;
    }

    public function addRapport(Rapport $rapport): static
    {
        if (!$this->rapports->contains($rapport)) {
            $this->rapports->add($rapport);
            $rapport->setOperation($this);
        }

        return $this;
    }

    public function removeRapport(Rapport $rapport): static
    {
        if ($this->rapports->removeElement($rapport)) {
            if ($rapport->getOperation() === $this) {
                $rapport->setOperation(null);
            }
        }

        return $this;
    }
}
