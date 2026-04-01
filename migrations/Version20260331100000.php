<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260331100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create patient_note table for notes on patients';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE patient_note (id INT AUTO_INCREMENT NOT NULL, patient_id INT NOT NULL, created_by_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6A8A76B6B899279 (patient_id), INDEX IDX_6A8A76B6B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE patient_note ADD CONSTRAINT FK_6A8A76B6B899279 FOREIGN KEY (patient_id) REFERENCES Patient (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE patient_note ADD CONSTRAINT FK_6A8A76B6B03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE patient_note');
    }
}
