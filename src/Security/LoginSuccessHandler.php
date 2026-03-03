<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = $token->getUser();
        
        // Vérifier si l'utilisateur est admin
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return new RedirectResponse('/admin-pannel');
        }

        // Sinon redirection par défaut vers les greffes
        return new RedirectResponse('/greffe');
    }
}
