<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260313120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create operation_chirurgien join table for operation participants';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS operation_chirurgien (operation_id INT NOT NULL, chirurgien_id INT NOT NULL, INDEX IDX_EA0A4C5D7D85C95D (operation_id), INDEX IDX_EA0A4C5DF39DECC7 (chirurgien_id), PRIMARY KEY(operation_id, chirurgien_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE operation_chirurgien ADD CONSTRAINT FK_EA0A4C5D7D85C95D FOREIGN KEY (operation_id) REFERENCES operation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE operation_chirurgien ADD CONSTRAINT FK_EA0A4C5DF39DECC7 FOREIGN KEY (chirurgien_id) REFERENCES Chirurgien (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE operation_chirurgien');
    }
}
