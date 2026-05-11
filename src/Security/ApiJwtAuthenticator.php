<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\MobileJwtService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiJwtAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly MobileJwtService $jwtService,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization') && 
               str_starts_with($request->headers->get('Authorization', ''), 'Bearer ');
    }

    public function authenticate(Request $request): Passport
    {
        $authHeader = $request->headers->get('Authorization', '');
        $token = substr($authHeader, 7);

        $payload = $this->jwtService->decodeToken($token);
        if ($payload === null || !isset($payload['sub'])) {
            throw new AuthenticationException('Token invalide ou expiré.');
        }

        $userId = (int) $payload['sub'];

        return new SelfValidatingPassport(
            new UserBadge($userId, function ($userId) {
                $user = $this->userRepository->find($userId);
                if (!$user instanceof User) {
                    throw new AuthenticationException('Utilisateur introuvable.');
                }
                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }
}
