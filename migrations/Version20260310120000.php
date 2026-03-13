<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260310120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create rapport table for patient reports';
    }

    public function up(Schema $schema): void
    {
        // Create rapport table
            if (!$schema->hasTable('rapport')) {
                $this->addSql('CREATE TABLE rapport (
                    id INT AUTO_INCREMENT NOT NULL,
                    patient_id INT NOT NULL,
                    auteur_id INT DEFAULT NULL,
                    titre VARCHAR(255) NOT NULL,
                    contenu_html LONGTEXT NOT NULL,
                    contenu_texte LONGTEXT NOT NULL,
                    date_creation DATETIME NOT NULL,
                    date_modification DATETIME NOT NULL,
                    version INT NOT NULL,
                    statut VARCHAR(50) DEFAULT NULL,
                    INDEX IDX_C8D93F5D6B899279 (patient_id),
                    INDEX IDX_C8D93F5D73F0D95E (auteur_id),
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
                $this->addSql('ALTER TABLE rapport ADD CONSTRAINT FK_C8D93F5D6B899279 FOREIGN KEY (patient_id) REFERENCES Patient (id)');
                $this->addSql('ALTER TABLE rapport ADD CONSTRAINT FK_C8D93F5D73F0D95E FOREIGN KEY (auteur_id) REFERENCES `user` (id)');
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('rapport')) {
            $this->addSql('DROP TABLE IF EXISTS rapport');
        }
    }
}
