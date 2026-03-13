<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260310143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add nb_medecins and nb_infirmieres columns to operation table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE operation ADD nb_medecins INT NOT NULL DEFAULT 0, ADD nb_infirmieres INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE operation DROP nb_medecins, DROP nb_infirmieres');
    }
}
