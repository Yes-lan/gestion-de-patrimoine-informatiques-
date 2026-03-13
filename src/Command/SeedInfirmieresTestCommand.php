<?php

namespace App\Command;

use App\Entity\Infirmiere;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed:infirmieres-test',
    description: 'Crée un jeu de données de test pour les infirmières.',
)]
class SeedInfirmieresTestCommand extends Command
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

        $io->title('Création du jeu de données de test pour les infirmières');

        try {
            // Vérifier que la table existe
            $connection = $this->entityManager->getConnection();
            $schemaManager = $connection->createSchemaManager();
            $tables = $schemaManager->listTableNames();
            
            $io->writeln('Tables existantes: ' . implode(', ', $tables));
            
            if (!in_array('infirmiere', $tables, true)) {
                $io->error('La table "infirmiere" n\'existe pas. Exécutez: php bin/console doctrine:schema:update --force');
                return Command::FAILURE;
            }

            $data = [
                ['nom' => 'Martin', 'prenom' => 'Claire', 'email' => 'claire.martin.infirmiere@test.local'],
                ['nom' => 'Bernard', 'prenom' => 'Sophie', 'email' => 'sophie.bernard.infirmiere@test.local'],
                ['nom' => 'Dubois', 'prenom' => 'Nadia', 'email' => 'nadia.dubois.infirmiere@test.local'],
                ['nom' => 'Roux', 'prenom' => 'Lea', 'email' => 'lea.roux.infirmiere@test.local'],
                ['nom' => 'Petit', 'prenom' => 'Camille', 'email' => 'camille.petit.infirmiere@test.local'],
            ];

            $created = 0;
            $skipped = 0;

            foreach ($data as $item) {
                $existing = $this->entityManager->getRepository(Infirmiere::class)->findOneBy(['email' => $item['email']]);
                if ($existing) {
                    $io->writeln(sprintf('  [SKIP] %s %s (%s) - existe déjà', $item['prenom'], $item['nom'], $item['email']));
                    ++$skipped;
                    continue;
                }

                $infirmiere = new Infirmiere();
                $infirmiere->setNom($item['nom']);
                $infirmiere->setPrenom($item['prenom']);
                $infirmiere->setEmail($item['email']);

                $user = new User();
                $user->setEmail($item['email']);
                $user->setRoles(['ROLE_INFIRMIERE']);
                $user->setPassword($this->passwordHasher->hashPassword($user, 'Test1234!'));

                $infirmiere->setUser($user);

                $this->entityManager->persist($infirmiere);
                $io->writeln(sprintf('  [OK] %s %s (%s)', $item['prenom'], $item['nom'], $item['email']));
                ++$created;
            }

            $this->entityManager->flush();

            $io->newLine();
            $io->success(sprintf('Jeu de test terminé: %d créée(s), %d ignorée(s). Mot de passe par défaut: Test1234!', $created, $skipped));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur lors de la création du jeu de test: ' . $e->getMessage());
            $io->writeln($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
