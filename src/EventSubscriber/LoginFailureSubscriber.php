<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

class LoginFailureSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire(service: 'monolog.logger.failed_login')]
        private readonly LoggerInterface $failedLoginLogger,
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

        $this->failedLoginLogger->info('Failed login event', $payload);
        $this->appendFailedLoginToFile($payload);
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
