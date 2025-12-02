<?php

namespace App\Entity;

use App\Repository\GreffeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GreffeRepository::class)]
class Greffe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $Fonctionnel = null;

    #[ORM\Column]
    private ?\DateTime $Date_Fin_De_Fonction = null;

    #[ORM\Column(length: 255)]
    private ?string $Type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isFonctionnel(): ?bool
    {
        return $this->Fonctionnel;
    }

    public function setFonctionnel(bool $Fonctionnel): static
    {
        $this->Fonctionnel = $Fonctionnel;

        return $this;
    }

    public function getDateFinDeFonction(): ?\DateTime
    {
        return $this->Date_Fin_De_Fonction;
    }

    public function setDateFinDeFonction(\DateTime $Date_Fin_De_Fonction): static
    {
        $this->Date_Fin_De_Fonction = $Date_Fin_De_Fonction;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->Type;
    }

    public function setType(string $Type): static
    {
        $this->Type = $Type;

        return $this;
    }
}
