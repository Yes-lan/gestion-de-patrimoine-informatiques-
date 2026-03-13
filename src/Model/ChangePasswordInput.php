<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ChangePasswordInput
{
    #[Assert\NotBlank(message: 'Le nouveau mot de passe est obligatoire.')]
    #[Assert\Length(min: 8, minMessage: 'Le nouveau mot de passe doit contenir au moins {{ limit }} caractères.')]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).+$/',
        message: 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.'
    )]
    private ?string $newPassword = null;

    #[Assert\NotBlank(message: 'La confirmation du mot de passe est obligatoire.')]
    #[Assert\EqualTo(propertyPath: 'newPassword', message: 'La confirmation du mot de passe ne correspond pas.')]
    private ?string $confirmPassword = null;

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(?string $newPassword): void
    {
        $this->newPassword = $newPassword;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }

    public function setConfirmPassword(?string $confirmPassword): void
    {
        $this->confirmPassword = $confirmPassword;
    }
}
