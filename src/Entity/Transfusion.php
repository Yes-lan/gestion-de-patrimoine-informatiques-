<?php

namespace App\Entity;

use App\Repository\TransfusionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransfusionRepository::class)]
class Transfusion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $CGR = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCGR(): ?int
    {
        return $this->CGR;
    }

    public function setCGR(int $CGR): static
    {
        $this->CGR = $CGR;

        return $this;
    }
}
