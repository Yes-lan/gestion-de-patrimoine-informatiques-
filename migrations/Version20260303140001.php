<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303140001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename is_alive column to alive';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Patient CHANGE COLUMN is_alive alive TINYINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Patient CHANGE COLUMN alive is_alive TINYINT DEFAULT NULL');
    }
}
