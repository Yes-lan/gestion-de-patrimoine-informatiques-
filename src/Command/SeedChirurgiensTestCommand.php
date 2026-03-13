<?php

namespace App\Command;

use App\Entity\Chirurgien;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed:chirurgiens-test',
    description: 'Crée un jeu de données de test pour les chirurgiens.',
)]
class SeedChirurgiensTestCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Création du jeu de données de test pour les chirurgiens');

        try {
            // Vérifier que la table existe
            $connection = $this->entityManager->getConnection();
            $schemaManager = $connection->createSchemaManager();
            $tables = $schemaManager->listTableNames();
            
            $io->writeln('Tables existantes: ' . implode(', ', $tables));
            
            if (!in_array('chirurgien', array_map('strtolower', $tables), true) && 
                !in_array('Chirurgien', $tables, true)) {
                $io->error('La table "chirurgien" n\'existe pas. Exécutez: php bin/console doctrine:migrations:migrate');
                return Command::FAILURE;
            }

            $data = [
                ['nom' => 'Dupont', 'prenom' => 'Jean', 'email' => 'jean.dupont.chirurgien@test.local'],
                ['nom' => 'Lefort', 'prenom' => 'Marc', 'email' => 'marc.lefort.chirurgien@test.local'],
                ['nom' => 'Moreau', 'prenom' => 'Pierre', 'email' => 'pierre.moreau.chirurgien@test.local'],
                ['nom' => 'Lambert', 'prenom' => 'Thomas', 'email' => 'thomas.lambert.chirurgien@test.local'],
                ['nom' => 'Girard', 'prenom' => 'Nicolas', 'email' => 'nicolas.girard.chirurgien@test.local'],
            ];

            $created = 0;
            $skipped = 0;

            foreach ($data as $item) {
                $existing = $this->entityManager->getRepository(Chirurgien::class)->findOneBy(['email' => $item['email']]);
                if ($existing) {
                    $io->writeln(sprintf('  [SKIP] %s %s (%s) - existe déjà', $item['prenom'], $item['nom'], $item['email']));
                    ++$skipped;
                    continue;
                }

                $chirurgien = new Chirurgien();
                $chirurgien->setNom($item['nom']);
                $chirurgien->setPrenom($item['prenom']);
                $chirurgien->setEmail($item['email']);

                $user = new User();
                $user->setEmail($item['email']);
                $user->setRoles(['ROLE_CHIRURGIEN']);
                $user->setPassword($this->passwordHasher->hashPassword($user, 'Test1234!'));

                $chirurgien->setUser($user);

                $this->entityManager->persist($chirurgien);
                $io->writeln(sprintf('  [OK] %s %s (%s)', $item['prenom'], $item['nom'], $item['email']));
                ++$created;
            }

            $this->entityManager->flush();

            $io->newLine();
            $io->success(sprintf('Jeu de test terminé: %d créé(s), %d ignoré(s). Mot de passe par défaut: Test1234!', $created, $skipped));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur lors de la création du jeu de test: ' . $e->getMessage());
            $io->writeln($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
