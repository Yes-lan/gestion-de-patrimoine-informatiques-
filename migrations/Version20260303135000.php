<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260303135000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove Num_Dossier column from Patient table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Patient DROP COLUMN Num_Dossier');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Patient ADD Num_Dossier INT NOT NULL');
    }
}
