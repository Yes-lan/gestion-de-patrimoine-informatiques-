<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260306130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add nom, prenom, email, password fields to Chirurgien table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Chirurgien ADD nom VARCHAR(100) DEFAULT NULL, ADD prenom VARCHAR(100) DEFAULT NULL, ADD email VARCHAR(180) DEFAULT NULL, ADD password VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CHIRURGIEN_EMAIL ON Chirurgien (email)');
        $this->addSql('ALTER TABLE Chirurgien MODIFY Name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_CHIRURGIEN_EMAIL ON Chirurgien');
        $this->addSql('ALTER TABLE Chirurgien DROP nom, DROP prenom, DROP email, DROP password');
        $this->addSql('ALTER TABLE Chirurgien MODIFY Name VARCHAR(255) NOT NULL');
    }
}
