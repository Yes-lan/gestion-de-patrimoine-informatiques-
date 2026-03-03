<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify it to your needs!
 */
final class Version20260227102200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert test Greffe records';
    }

    public function up(Schema $schema): void
    {
        // Greffe (5 enregistrements) - Use existing patients (IDs 1-5)
        $this->addSql("INSERT INTO Greffe (patient_id, Fonctionnel, Date_Fin_De_Fonction, Type) VALUES
(1, 1, DATE_SUB(NOW(), INTERVAL 30 DAY), 'Rein'),
(2, 1, DATE_SUB(NOW(), INTERVAL 60 DAY), 'Foie'),
(3, 0, DATE_SUB(NOW(), INTERVAL 90 DAY), 'Cœur'),
(4, 1, DATE_SUB(NOW(), INTERVAL 15 DAY), 'Pancréas'),
(5, 1, DATE_SUB(NOW(), INTERVAL 45 DAY), 'Rein')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM Greffe");
    }
}
