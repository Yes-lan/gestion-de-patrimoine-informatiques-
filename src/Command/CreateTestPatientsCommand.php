<?php

namespace App\Command;

use App\Entity\Patient;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-test-patients',
    description: 'Creates test patients and assigns them to doctors (caregivers)',
)]
class CreateTestPatientsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Get doctors from database
        $doctors = $this->entityManager->getRepository(User::class)
            ->findAll();

        // Filter doctors (anyone with ROLE_MEDECIN in their roles)
        $doctors = array_filter($doctors, static function (User $user) {
            $roles = $user->getRoles();
            return in_array('ROLE_MEDECIN', $roles, true);
        });
        
        // Reindex array keys (important!)
        $doctors = array_values($doctors);

        if (empty($doctors)) {
            $io->error('❌ No doctors found! Run: php bin/console app:create-test-users');
            return Command::FAILURE;
        }

        $io->info(sprintf('Found %d doctor(s)', count($doctors)));

        // Test patients data
        $testPatients = [
            ['name' => 'Dupont', 'firstName' => 'Jean', 'ville' => 'Paris'],
            ['name' => 'Martin', 'firstName' => 'Marie', 'ville' => 'Lyon'],
            ['name' => 'Durand', 'firstName' => 'Luc', 'ville' => 'Marseille'],
            ['name' => 'Petit', 'firstName' => 'Anne', 'ville' => 'Toulouse'],
            ['name' => 'Bernard', 'firstName' => 'Paul', 'ville' => 'Nice'],
            ['name' => 'Robert', 'firstName' => 'Pierre', 'ville' => 'Nantes'],
            ['name' => 'Richard', 'firstName' => 'Sophie', 'ville' => 'Strasbourg'],
            ['name' => 'Thomas', 'firstName' => 'Michel', 'ville' => 'Montpellier'],
            ['name' => 'Gérard', 'firstName' => 'Claire', 'ville' => 'Bordeaux'],
            ['name' => 'Laurent', 'firstName' => 'Francoise', 'ville' => 'Lille'],
        ];

        // Disable foreign key checks to allow deletion
        $conn = $this->entityManager->getConnection();
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        
        // Clear existing patients and related data
        $this->entityManager->createQuery('DELETE FROM App\Entity\Greffe')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Patient')->execute();
        $io->writeln('✅ Cleared existing patients');
        
        // Re-enable foreign key checks
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS=1');

        // Create patients and assign to doctors
        $createdPatients = [];
        $docIndex = 0;
        foreach ($testPatients as $patientData) {
            $patient = new Patient();
            $patient->setName($patientData['name']);
            $patient->setFirstName($patientData['firstName']);
            $patient->setVille($patientData['ville']);
            $patient->setIsAlive(true);

            // Assign to doctor (round-robin)
            $doctor = $doctors[$docIndex % count($doctors)];
            $patient->addCaregiver($doctor);
            $docIndex++;

            $this->entityManager->persist($patient);
            $createdPatients[] = $patient;
            
            $io->writeln(sprintf('✓ Created patient: %s %s (assigned to %s)', 
                $patientData['firstName'], 
                $patientData['name'],
                $doctor->getEmail()
            ));
        }

        $this->entityManager->flush();
        $io->success(sprintf('All %d test patients created successfully!', count($testPatients)));

        return Command::SUCCESS;
    }
}
