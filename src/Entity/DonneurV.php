<?php

namespace App\Entity;

use App\Repository\DonneurVRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DonneurVRepository::class)]
class DonneurV
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $Num_CRISTAL = null;

    #[ORM\Column(length: 255)]
    private ?string $Groupe_Sanguin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumCRISTAL(): ?int
    {
        return $this->Num_CRISTAL;
    }

    public function setNumCRISTAL(int $Num_CRISTAL): static
    {
        $this->Num_CRISTAL = $Num_CRISTAL;

        return $this;
    }

    public function getGroupeSanguin(): ?string
    {
        return $this->Groupe_Sanguin;
    }

    public function setGroupeSanguin(string $Groupe_Sanguin): static
    {
        $this->Groupe_Sanguin = $Groupe_Sanguin;

        return $this;
    }
}
