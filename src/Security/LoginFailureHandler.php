<?php

namespace App\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LoginFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LoggerInterface $failedLoginLogger,
        #[Autowire('%kernel.logs_dir%/failed_login.log')]
        private readonly string $failedLoginLogFile,
    ) {
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        $payload = [
            'source' => 'web_form_login',
            'attempted_login' => mb_strtolower(trim((string) $request->request->get('_username', ''))),
            'ip' => $request->getClientIp(),
            'user_agent' => $request->headers->get('User-Agent'),
            'path' => $request->getPathInfo(),
            'failed_at_utc' => (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format(\DateTimeInterface::ATOM),
            'error' => $exception->getMessageKey(),
        ];

        $this->failedLoginLogger->info('Failed web login attempt', $payload);
        $this->appendFailedLoginToFile($payload);

        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }

    private function appendFailedLoginToFile(array $payload): void
    {
        $logDir = \dirname($this->failedLoginLogFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }

        @file_put_contents(
            $this->failedLoginLogFile,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND | LOCK_EX,
        );
    }
}
