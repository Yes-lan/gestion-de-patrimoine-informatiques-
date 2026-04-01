<?php

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

class LoginFailureSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire('%kernel.logs_dir%/failed_login.log')]
        private readonly string $failedLoginLogFile,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginFailureEvent::class => 'onLoginFailure',
        ];
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $request = $event->getRequest();

        $attemptedLogin = mb_strtolower(trim((string) (
            $request->request->get('_username')
            ?? $request->request->get('email')
            ?? ''
        )));

        $payload = [
            'source' => 'web_login_event',
            'attempted_login' => $attemptedLogin,
            'ip' => $request->getClientIp(),
            'user_agent' => $request->headers->get('User-Agent'),
            'path' => $request->getPathInfo(),
            'failed_at_utc' => (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format(\DateTimeInterface::ATOM),
            'error' => $event->getException()->getMessageKey(),
        ];

        $this->appendFailedLoginToFile($payload);
    }

    private function appendFailedLoginToFile(array $payload): void
    {
        $logDir = \dirname($this->failedLoginLogFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }

        $line = sprintf(
            '[%s] source=%s ip=%s login=%s path=%s error="%s" user_agent="%s"',
            $payload['failed_at_utc'] ?? '',
            $payload['source'] ?? '',
            $payload['ip'] ?? '',
            $payload['attempted_login'] ?? '',
            $payload['path'] ?? '',
            str_replace('"', '\\"', (string) ($payload['error'] ?? '')),
            str_replace('"', '\\"', (string) ($payload['user_agent'] ?? '')),
        );

        @file_put_contents(
            $this->failedLoginLogFile,
            $line . PHP_EOL,
            FILE_APPEND | LOCK_EX,
        );
    }
}
