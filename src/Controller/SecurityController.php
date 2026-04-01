<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Model\ChangePasswordInput;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


final class SecurityController extends AbstractController
{
    public function __construct(
        #[Autowire(service: 'monolog.logger.failed_login')]
        private readonly LoggerInterface $failedLoginLogger,
        #[Autowire('%kernel.logs_dir%/failed_login.log')]
        private readonly string $failedLoginLogFile,
    ) {
    }

    #[Route(path: '/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        if ($this->getUser() instanceof User) {
            if ($this->isGranted("ROLE_ADMIN")) {
                return $this->redirectToRoute('app_admin_pannel');
            }
            return $this->redirectToRoute('app_greffe');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($error) {
            $payload = [
                'source' => 'web_form_login',
                'attempted_login' => mb_strtolower(trim((string) $lastUsername)),
                'ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('User-Agent'),
                'path' => $request->getPathInfo(),
                'failed_at_utc' => (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format(\DateTimeInterface::ATOM),
                'error' => $error->getMessageKey(),
            ];

            $this->failedLoginLogger->info('Failed web login attempt', $payload);
            $this->appendFailedLoginToFile($payload);
            $this->addFlash('danger', 'Identifiants invalides.');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/parametres', name: 'app_settings', methods: ['GET', 'POST'])]
    public function settings(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur invalide.');
        }

        $input = new ChangePasswordInput();
        $form = $this->createForm(ChangePasswordType::class, $input);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($passwordHasher->isPasswordValid($user, (string) $input->getNewPassword())) {
                $this->addFlash('danger', 'Le nouveau mot de passe doit être différent de l\'ancien.');
            } else {
                $user->setPassword($passwordHasher->hashPassword($user, (string) $input->getNewPassword()));
                $em->flush();

                $this->addFlash('success', 'Mot de passe mis à jour avec succès.');
                return $this->redirectToRoute('app_settings');
            }
        }

        return $this->render('security/settings.html.twig', [
            'form' => $form->createView(),
        ]);
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
