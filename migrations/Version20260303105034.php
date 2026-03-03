<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303105034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add isAlive field to Patient entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Patient ADD is_alive TINYINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Patient DROP COLUMN is_alive');
    }
}
