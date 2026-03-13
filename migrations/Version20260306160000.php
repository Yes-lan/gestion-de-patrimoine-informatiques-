<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260306160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove nom and prenom columns from user table';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('user')) {
            return;
        }

        $table = $schema->getTable('user');

        if ($table->hasColumn('nom')) {
            $this->addSql('ALTER TABLE `user` DROP COLUMN nom');
        }

        if ($table->hasColumn('prenom')) {
            $this->addSql('ALTER TABLE `user` DROP COLUMN prenom');
        }
    }

    public function down(Schema $schema): void
    {
        if (!$schema->hasTable('user')) {
            return;
        }

        $table = $schema->getTable('user');

        if (!$table->hasColumn('nom')) {
            $this->addSql('ALTER TABLE `user` ADD nom VARCHAR(100) DEFAULT NULL');
        }

        if (!$table->hasColumn('prenom')) {
            $this->addSql('ALTER TABLE `user` ADD prenom VARCHAR(100) DEFAULT NULL');
        }
    }
}
