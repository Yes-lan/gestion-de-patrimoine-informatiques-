<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-test-users',
    description: 'Creates test users for development/testing',
)]
class CreateTestUsersCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $testUsers = [
            // ADMIN
            ['email' => 'admin@hospital.fr', 'password' => 'admin123', 'roles' => ['ROLE_ADMIN'], 'nom' => 'Admin', 'prenom' => 'Principal'],
            
            // MEDECINS
            ['email' => 'dr.dupont@hospital.fr', 'password' => 'medecin123', 'roles' => ['ROLE_MEDECIN'], 'nom' => 'Dupont', 'prenom' => 'Jacques'],
            ['email' => 'dr.martin@hospital.fr', 'password' => 'medecin123', 'roles' => ['ROLE_MEDECIN'], 'nom' => 'Martin', 'prenom' => 'Sophie'],
            ['email' => 'dr.bernard@hospital.fr', 'password' => 'medecin123', 'roles' => ['ROLE_MEDECIN'], 'nom' => 'Bernard', 'prenom' => 'Michel'],
            
            // CHIRURGIENS
            ['email' => 'dr.rousseau@hospital.fr', 'password' => 'chirurgien123', 'roles' => ['ROLE_CHIRURGIEN'], 'nom' => 'Rousseau', 'prenom' => 'Pierre'],
            ['email' => 'dr.leclerc@hospital.fr', 'password' => 'chirurgien123', 'roles' => ['ROLE_CHIRURGIEN'], 'nom' => 'Leclerc', 'prenom' => 'Francoise'],
            ['email' => 'dr.moreau@hospital.fr', 'password' => 'chirurgien123', 'roles' => ['ROLE_CHIRURGIEN'], 'nom' => 'Moreau', 'prenom' => 'Laurent'],
            
            // INFIRMIERES
            ['email' => 'infirmiere.durand@hospital.fr', 'password' => 'infirm123', 'roles' => ['ROLE_INFIRMIERE'], 'nom' => 'Durand', 'prenom' => 'Marie'],
            ['email' => 'infirmiere.petit@hospital.fr', 'password' => 'infirm123', 'roles' => ['ROLE_INFIRMIERE'], 'nom' => 'Petit', 'prenom' => 'Anne'],
            ['email' => 'infirmiere.richard@hospital.fr', 'password' => 'infirm123', 'roles' => ['ROLE_INFIRMIERE'], 'nom' => 'Richard', 'prenom' => 'Claire'],
            ['email' => 'infirmiere.thomas@hospital.fr', 'password' => 'infirm123', 'roles' => ['ROLE_INFIRMIERE'], 'nom' => 'Thomas', 'prenom' => 'Isabelle'],
        ];

        // Clear existing users
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $io->writeln('✅ Cleared existing users');

        // Create test users
        $createdUsers = [];
        foreach ($testUsers as $userData) {
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setRoles($userData['roles']);
            $user->setNom($userData['nom']);
            $user->setPrenom($userData['prenom']);
            
            // Hash password with bcrypt
            $hashedPassword = $this->passwordHasher->hashPassword($user, $userData['password']);
            $user->setPassword($hashedPassword);
            
            $this->entityManager->persist($user);
            $createdUsers[] = [
                'email' => $userData['email'],
                'password' => $userData['password'],
                'roles' => $userData['roles'],
                'nom' => $userData['prenom'] . ' ' . $userData['nom'],
            ];
            
            $io->writeln('✓ Created user: ' . $userData['email']);
        }

        $this->entityManager->flush();
        $io->success('All test users created successfully!');

        // Generate credentials file
        $credentialsFile = dirname(__DIR__, 2) . '/TEST_CREDENTIALS.txt';
        $credentialsContent = $this->generateCredentialsFile($createdUsers);
        file_put_contents($credentialsFile, $credentialsContent);
        
        $io->writeln('📄 Credentials saved to: TEST_CREDENTIALS.txt');

        return Command::SUCCESS;
    }

    private function generateCredentialsFile(array $users): string
    {
        $content = "═══════════════════════════════════════════════════════════════════\n";
        $content .= "              🏥 HÔPITAL - TEST CREDENTIALS 🏥\n";
        $content .= "═══════════════════════════════════════════════════════════════════\n";
        $content .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        $content .= "═══════════════════════════════════════════════════════════════════\n\n";
        
        $content .= "⚠️  WARNING: This file contains sensitive information for testing purposes only!\n";
        $content .= "Do NOT use these credentials in production!\n\n";
        
        // Group users by role
        $byRole = [];
        foreach ($users as $user) {
            $role = $user['roles'][0] ?? 'UNKNOWN';
            if (!isset($byRole[$role])) {
                $byRole[$role] = [];
            }
            $byRole[$role][] = $user;
        }
        
        $roleLabels = [
            'ROLE_ADMIN' => '👨‍💼 ADMINISTRATEUR',
            'ROLE_MEDECIN' => '👨‍⚕️ MEDECIN',
            'ROLE_CHIRURGIEN' => '👨‍🔬 CHIRURGIEN',
            'ROLE_INFIRMIERE' => '👩‍⚕️ INFIRMIERE',
        ];
        
        foreach ($byRole as $role => $roleUsers) {
            $label = $roleLabels[$role] ?? $role;
            $content .= "\n" . str_repeat("─", 65) . "\n";
            $content .= $label . "\n";
            $content .= str_repeat("─", 65) . "\n";
            
            foreach ($roleUsers as $user) {
                $content .= sprintf(
                    "\n  Email: %s\n  Mot de passe: %s\n  Nom: %s\n",
                    $user['email'],
                    $user['password'],
                    $user['nom']
                );
            }
        }
        
        $content .= "\n" . str_repeat("═", 65) . "\n";
        $content .= "SUMMARY\n";
        $content .= str_repeat("═", 65) . "\n\n";
        
        foreach ($byRole as $role => $roleUsers) {
            $label = $roleLabels[$role] ?? $role;
            $content .= sprintf("  %s: %d user(s)\n", $label, count($roleUsers));
        }
        
        $content .= sprintf("\n  Total: %d users\n\n", count($users));
        
        $content .= "═══════════════════════════════════════════════════════════════════\n";
        
        return $content;
    }
}
