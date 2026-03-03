<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227101500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert test data into all tables';
    }

    public function up(Schema $schema): void
    {
        // Just insert more patients
        $this->addSql("INSERT INTO Patient (Name, FirstName, Ville, Num_Dossier) VALUES
('Test1', 'Patient1', 'Paris', 21001),
('Test2', 'Patient2', 'Lyon', 21002),
('Test3', 'Patient3', 'Marseille', 21003)");

        // Greffe (5 enregistrements) - Use the existing patients from initial migration (IDs 1-5)
        $this->addSql("INSERT INTO Greffe (patient_id, Fonctionnel, Date_Fin_De_Fonction, Type) VALUES
(1, 1, DATE_SUB(NOW(), INTERVAL 30 DAY), 'Rein'),
(2, 1, DATE_SUB(NOW(), INTERVAL 60 DAY), 'Foie'),
(3, 0, DATE_SUB(NOW(), INTERVAL 90 DAY), 'Cœur'),
(4, 1, DATE_SUB(NOW(), INTERVAL 15 DAY), 'Pancréas'),
(5, 1, DATE_SUB(NOW(), INTERVAL 45 DAY), 'Rein')");
    }

    public function down(Schema $schema): void
    {
        // Delete test patients
        $this->addSql("DELETE FROM Patient WHERE Num_Dossier >= 21001");
    }
}
