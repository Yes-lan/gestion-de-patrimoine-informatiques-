<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260403094500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add needs_greffe boolean column to Patient';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Patient ADD needs_greffe TINYINT(1) DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Patient DROP needs_greffe');
    }
}
