<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227100318 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Greffe ADD patient_id INT NOT NULL');
        $this->addSql('ALTER TABLE Greffe ADD CONSTRAINT FK_6E50C18D6B899279 FOREIGN KEY (patient_id) REFERENCES Patient (id)');
        $this->addSql('CREATE INDEX IDX_6E50C18D6B899279 ON Greffe (patient_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Greffe DROP FOREIGN KEY FK_6E50C18D6B899279');
        $this->addSql('DROP INDEX IDX_6E50C18D6B899279 ON Greffe');
        $this->addSql('ALTER TABLE Greffe DROP patient_id');
    }
}
