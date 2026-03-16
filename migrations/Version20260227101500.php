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

        // Greffe (5 enregistrements) - avoid hardcoded patient IDs to prevent FK issues
        $this->addSql("INSERT INTO Greffe (patient_id, Fonctionnel, Date_Fin_De_Fonction, Type)
    SELECT id, 1, DATE_SUB(NOW(), INTERVAL 30 DAY), 'Rein' FROM Patient ORDER BY id ASC LIMIT 1 OFFSET 0");
        $this->addSql("INSERT INTO Greffe (patient_id, Fonctionnel, Date_Fin_De_Fonction, Type)
    SELECT id, 1, DATE_SUB(NOW(), INTERVAL 60 DAY), 'Foie' FROM Patient ORDER BY id ASC LIMIT 1 OFFSET 1");
        $this->addSql("INSERT INTO Greffe (patient_id, Fonctionnel, Date_Fin_De_Fonction, Type)
    SELECT id, 0, DATE_SUB(NOW(), INTERVAL 90 DAY), 'Cœur' FROM Patient ORDER BY id ASC LIMIT 1 OFFSET 2");
        $this->addSql("INSERT INTO Greffe (patient_id, Fonctionnel, Date_Fin_De_Fonction, Type)
    SELECT id, 1, DATE_SUB(NOW(), INTERVAL 15 DAY), 'Pancréas' FROM Patient ORDER BY id ASC LIMIT 1 OFFSET 3");
        $this->addSql("INSERT INTO Greffe (patient_id, Fonctionnel, Date_Fin_De_Fonction, Type)
    SELECT id, 1, DATE_SUB(NOW(), INTERVAL 45 DAY), 'Rein' FROM Patient ORDER BY id ASC LIMIT 1 OFFSET 4");
    }

    public function down(Schema $schema): void
    {
        // Delete test patients
        $this->addSql("DELETE FROM Patient WHERE Num_Dossier >= 21001");
    }
}
