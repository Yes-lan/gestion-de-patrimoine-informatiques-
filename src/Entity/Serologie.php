<?php

namespace App\Entity;

use App\Repository\SerologieRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SerologieRepository::class)]
class Serologie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $Selorogie_CMV = null;

    #[ORM\Column]
    private ?bool $Selorogie_EBV = null;

    #[ORM\Column]
    private ?bool $Selorogie_toxoplasmose = null;

    #[ORM\Column]
    private ?bool $Selorogie_HIV = null;

    #[ORM\Column]
    private ?bool $Selorogie_HTVL = null;

    #[ORM\Column]
    private ?bool $Selorogie_syphilis = null;

    #[ORM\Column]
    private ?bool $Selorogie_HCV = null;

    #[ORM\Column]
    private ?bool $Selorogie_AgÂ_HBS = null;

    #[ORM\Column]
    private ?bool $Selorogie_Ac_HBS = null;

    #[ORM\Column]
    private ?bool $Selorogie_Ac_HBC = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isSelorogieCMV(): ?bool
    {
        return $this->Selorogie_CMV;
    }

    public function setSelorogieCMV(bool $Selorogie_CMV): static
    {
        $this->Selorogie_CMV = $Selorogie_CMV;

        return $this;
    }

    public function isSelorogieEBV(): ?bool
    {
        return $this->Selorogie_EBV;
    }

    public function setSelorogieEBV(bool $Selorogie_EBV): static
    {
        $this->Selorogie_EBV = $Selorogie_EBV;

        return $this;
    }

    public function isSelorogieToxoplasmose(): ?bool
    {
        return $this->Selorogie_toxoplasmose;
    }

    public function setSelorogieToxoplasmose(bool $Selorogie_toxoplasmose): static
    {
        $this->Selorogie_toxoplasmose = $Selorogie_toxoplasmose;

        return $this;
    }

    public function isSelorogieHIV(): ?bool
    {
        return $this->Selorogie_HIV;
    }

    public function setSelorogieHIV(bool $Selorogie_HIV): static
    {
        $this->Selorogie_HIV = $Selorogie_HIV;

        return $this;
    }

    public function isSelorogieHTVL(): ?bool
    {
        return $this->Selorogie_HTVL;
    }

    public function setSelorogieHTVL(bool $Selorogie_HTVL): static
    {
        $this->Selorogie_HTVL = $Selorogie_HTVL;

        return $this;
    }

    public function isSelorogieSyphilis(): ?bool
    {
        return $this->Selorogie_syphilis;
    }

    public function setSelorogieSyphilis(bool $Selorogie_syphilis): static
    {
        $this->Selorogie_syphilis = $Selorogie_syphilis;

        return $this;
    }

    public function isSelorogieHCV(): ?bool
    {
        return $this->Selorogie_HCV;
    }

    public function setSelorogieHCV(bool $Selorogie_HCV): static
    {
        $this->Selorogie_HCV = $Selorogie_HCV;

        return $this;
    }

    public function isSelorogieAgÂHBS(): ?bool
    {
        return $this->Selorogie_AgÂ_HBS;
    }

    public function setSelorogieAgÂHBS(bool $Selorogie_AgÂ_HBS): static
    {
        $this->Selorogie_AgÂ_HBS = $Selorogie_AgÂ_HBS;

        return $this;
    }

    public function isSelorogieAcHBS(): ?bool
    {
        return $this->Selorogie_Ac_HBS;
    }

    public function setSelorogieAcHBS(bool $Selorogie_Ac_HBS): static
    {
        $this->Selorogie_Ac_HBS = $Selorogie_Ac_HBS;

        return $this;
    }

    public function isSelorogieAcHBC(): ?bool
    {
        return $this->Selorogie_Ac_HBC;
    }

    public function setSelorogieAcHBC(bool $Selorogie_Ac_HBC): static
    {
        $this->Selorogie_Ac_HBC = $Selorogie_Ac_HBC;

        return $this;
    }
}
