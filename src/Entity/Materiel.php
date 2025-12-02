<?php

namespace App\Entity;

use App\Repository\MaterielRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MaterielRepository::class)]
class Materiel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $Sonde_JJ = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isSondeJJ(): ?bool
    {
        return $this->Sonde_JJ;
    }

    public function setSondeJJ(bool $Sonde_JJ): static
    {
        $this->Sonde_JJ = $Sonde_JJ;

        return $this;
    }
}
