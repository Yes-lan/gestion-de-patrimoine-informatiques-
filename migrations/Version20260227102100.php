<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify it to your needs!
 */
final class Version20260227102100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop and recreate Greffe to ensure patient_id exists';
    }

    public function up(Schema $schema): void
    {
        // Drop existing Greffe table
        $this->addSql('DROP TABLE IF EXISTS Greffe');
        
        // Recreate with patient_id from the start
        $this->addSql('CREATE TABLE Greffe (id INT AUTO_INCREMENT NOT NULL, patient_id INT NOT NULL, Fonctionnel TINYINT NOT NULL, Date_Fin_De_Fonction DATETIME NOT NULL, Type VARCHAR(255) NOT NULL, CONSTRAINT FK_6E50C18D6B899279 FOREIGN KEY (patient_id) REFERENCES Patient (id), INDEX IDX_6E50C18D6B899279 (patient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE Greffe');
    }
}
