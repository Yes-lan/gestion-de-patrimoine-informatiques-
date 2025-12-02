<?php

namespace App\Entity;

use App\Repository\GroupageHLARepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroupageHLARepository::class)]
class GroupageHLA
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $HLA_A = null;

    #[ORM\Column]
    private ?int $HLA_B = null;

    #[ORM\Column]
    private ?int $HLA_Cw = null;

    #[ORM\Column]
    private ?int $HLA_DR = null;

    #[ORM\Column]
    private ?int $HLA_DQ = null;

    #[ORM\Column]
    private ?int $HLA_DP = null;

    #[ORM\Column]
    private ?int $Incompatibilites_HLA_A = null;

    #[ORM\Column]
    private ?int $Incompatibilites_HLA_B = null;

    #[ORM\Column]
    private ?int $Incompatibilites_HLA_Cw = null;

    #[ORM\Column]
    private ?int $Incompatibilites_HLA_DR = null;

    #[ORM\Column]
    private ?int $Incompatibilites_HLA_DQ = null;

    #[ORM\Column]
    private ?int $Incompatibilites_HLA_DP = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHLAA(): ?int
    {
        return $this->HLA_A;
    }

    public function setHLAA(int $HLA_A): static
    {
        $this->HLA_A = $HLA_A;

        return $this;
    }

    public function getHLAB(): ?int
    {
        return $this->HLA_B;
    }

    public function setHLAB(int $HLA_B): static
    {
        $this->HLA_B = $HLA_B;

        return $this;
    }

    public function getHLACw(): ?int
    {
        return $this->HLA_Cw;
    }

    public function setHLACw(int $HLA_Cw): static
    {
        $this->HLA_Cw = $HLA_Cw;

        return $this;
    }

    public function getHLADR(): ?int
    {
        return $this->HLA_DR;
    }

    public function setHLADR(int $HLA_DR): static
    {
        $this->HLA_DR = $HLA_DR;

        return $this;
    }

    public function getHLADQ(): ?int
    {
        return $this->HLA_DQ;
    }

    public function setHLADQ(int $HLA_DQ): static
    {
        $this->HLA_DQ = $HLA_DQ;

        return $this;
    }

    public function getHLADP(): ?int
    {
        return $this->HLA_DP;
    }

    public function setHLADP(int $HLA_DP): static
    {
        $this->HLA_DP = $HLA_DP;

        return $this;
    }

    public function getIncompatibilitesHLAA(): ?int
    {
        return $this->Incompatibilites_HLA_A;
    }

    public function setIncompatibilitesHLAA(int $Incompatibilites_HLA_A): static
    {
        $this->Incompatibilites_HLA_A = $Incompatibilites_HLA_A;

        return $this;
    }

    public function getIncompatibilitesHLAB(): ?int
    {
        return $this->Incompatibilites_HLA_B;
    }

    public function setIncompatibilitesHLAB(int $Incompatibilites_HLA_B): static
    {
        $this->Incompatibilites_HLA_B = $Incompatibilites_HLA_B;

        return $this;
    }

    public function getIncompatibilitesHLACw(): ?int
    {
        return $this->Incompatibilites_HLA_Cw;
    }

    public function setIncompatibilitesHLACw(int $Incompatibilites_HLA_Cw): static
    {
        $this->Incompatibilites_HLA_Cw = $Incompatibilites_HLA_Cw;

        return $this;
    }

    public function getIncompatibilitesHLADR(): ?int
    {
        return $this->Incompatibilites_HLA_DR;
    }

    public function setIncompatibilitesHLADR(int $Incompatibilites_HLA_DR): static
    {
        $this->Incompatibilites_HLA_DR = $Incompatibilites_HLA_DR;

        return $this;
    }

    public function getIncompatibilitesHLADQ(): ?int
    {
        return $this->Incompatibilites_HLA_DQ;
    }

    public function setIncompatibilitesHLADQ(int $Incompatibilites_HLA_DQ): static
    {
        $this->Incompatibilites_HLA_DQ = $Incompatibilites_HLA_DQ;

        return $this;
    }

    public function getIncompatibilitesHLADP(): ?int
    {
        return $this->Incompatibilites_HLA_DP;
    }

    public function setIncompatibilitesHLADP(int $Incompatibilites_HLA_DP): static
    {
        $this->Incompatibilites_HLA_DP = $Incompatibilites_HLA_DP;

        return $this;
    }
}
