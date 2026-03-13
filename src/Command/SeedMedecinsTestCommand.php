<?php

namespace App\Command;

use App\Entity\Medecin;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed:medecins-test',
    description: 'Crée un jeu de données de test pour les médecins.',
)]
class SeedMedecinsTestCommand extends Command
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

        $io->title('Création du jeu de données de test pour les médecins');

        try {
            $connection = $this->entityManager->getConnection();
            $schemaManager = $connection->createSchemaManager();
            $tables = $schemaManager->listTableNames();
            
            $io->writeln('Tables existantes: ' . implode(', ', $tables));
            
            if (!in_array('medecin', array_map('strtolower', $tables), true)) {
                $io->error('La table "medecin" n\'existe pas. Exécutez: php bin/console doctrine:migrations:migrate');
                return Command::FAILURE;
            }

            $data = [
                ['nom' => 'Rousseau', 'prenom' => 'Marie', 'email' => 'marie.rousseau.medecin@test.local'],
                ['nom' => 'Garnier', 'prenom' => 'Julien', 'email' => 'julien.garnier.medecin@test.local'],
                ['nom' => 'Blanc', 'prenom' => 'Catherine', 'email' => 'catherine.blanc.medecin@test.local'],
                ['nom' => 'Fournier', 'prenom' => 'Philippe', 'email' => 'philippe.fournier.medecin@test.local'],
                ['nom' => 'Andre', 'prenom' => 'Isabelle', 'email' => 'isabelle.andre.medecin@test.local'],
            ];

            $created = 0;
            $skipped = 0;

            foreach ($data as $item) {
                $existing = $this->entityManager->getRepository(Medecin::class)->findOneBy(['email' => $item['email']]);
                if ($existing) {
                    $io->writeln(sprintf('  [SKIP] %s %s (%s) - existe déjà', $item['prenom'], $item['nom'], $item['email']));
                    ++$skipped;
                    continue;
                }

                $medecin = new Medecin();
                $medecin->setNom($item['nom']);
                $medecin->setPrenom($item['prenom']);
                $medecin->setEmail($item['email']);

                $user = new User();
                $user->setEmail($item['email']);
                $user->setRoles(['ROLE_MEDECIN']);
                $user->setPassword($this->passwordHasher->hashPassword($user, 'Test1234!'));

                $medecin->setUser($user);

                $this->entityManager->persist($medecin);
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
