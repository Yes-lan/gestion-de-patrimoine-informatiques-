<?php

namespace App\Entity;

use App\Repository\PatientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PatientRepository::class)]
class Patient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $Name = null;

    #[ORM\Column(length: 255)]
    private ?string $FirstName = null;

    #[ORM\Column(length: 255)]
    private ?string $Ville = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $alive = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'patient_caregiver')]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $caregivers;

    /**
     * @var Collection<int, Greffe>
     */
    #[ORM\OneToMany(targetEntity: Greffe::class, mappedBy: 'patient')]
    private Collection $greffes;

    /**
     * @var Collection<int, Operation>
     */
    #[ORM\OneToMany(targetEntity: Operation::class, mappedBy: 'patient', cascade: ['remove'])]
    private Collection $operations;

    public function __construct()
    {
        $this->greffes = new ArrayCollection();
        $this->operations = new ArrayCollection();
        $this->caregivers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): static
    {
        $this->Name = $Name;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->FirstName;
    }

    public function setFirstName(string $FirstName): static
    {
        $this->FirstName = $FirstName;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->Ville;
    }

    public function setVille(string $Ville): static
    {
        $this->Ville = $Ville;

        return $this;
    }

    public function isAlive(): ?bool
    {
        return $this->alive;
    }

    public function setIsAlive(?bool $alive): static
    {
        $this->alive = $alive;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getCaregivers(): Collection
    {
        return $this->caregivers;
    }

    public function addCaregiver(User $user): static
    {
        if (!$this->caregivers->contains($user)) {
            $this->caregivers->add($user);
        }

        return $this;
    }

    public function removeCaregiver(User $user): static
    {
        $this->caregivers->removeElement($user);

        return $this;
    }

    /**
     * @return Collection<int, Greffe>
     */
    public function getGreffes(): Collection
    {
        return $this->greffes;
    }

    public function addGreffe(Greffe $greffe): static
    {
        if (!$this->greffes->contains($greffe)) {
            $this->greffes->add($greffe);
            $greffe->setPatient($this);
        }

        return $this;
    }

    public function removeGreffe(Greffe $greffe): static
    {
        if ($this->greffes->removeElement($greffe)) {
            if ($greffe->getPatient() === $this) {
                $greffe->setPatient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Operation>
     */
    public function getOperations(): Collection
    {
        return $this->operations;
    }

    public function addOperation(Operation $operation): static
    {
        if (!$this->operations->contains($operation)) {
            $this->operations->add($operation);
            $operation->setPatient($this);
        }

        return $this;
    }

    public function removeOperation(Operation $operation): static
    {
        if ($this->operations->removeElement($operation)) {
            if ($operation->getPatient() === $this) {
                $operation->setPatient(null);
            }
        }

        return $this;
    }
}
