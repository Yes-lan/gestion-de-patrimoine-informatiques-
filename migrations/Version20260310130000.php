<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260310130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create operation table, participant tables, and move rapport relation from patient to operation';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('operation')) {
            $this->addSql('CREATE TABLE operation (id INT AUTO_INCREMENT NOT NULL, patient_id INT NOT NULL, titre VARCHAR(255) NOT NULL, date_operation DATETIME NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_C1A7E48D6B899279 (patient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE operation ADD CONSTRAINT FK_C1A7E48D6B899279 FOREIGN KEY (patient_id) REFERENCES Patient (id) ON DELETE CASCADE');
        }

        if (!$schema->hasTable('operation_medecin')) {
            $this->addSql('CREATE TABLE operation_medecin (operation_id INT NOT NULL, medecin_id INT NOT NULL, INDEX IDX_3ED52B987D85C95D (operation_id), INDEX IDX_3ED52B98B5A53D8 (medecin_id), PRIMARY KEY(operation_id, medecin_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE operation_medecin ADD CONSTRAINT FK_3ED52B987D85C95D FOREIGN KEY (operation_id) REFERENCES operation (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE operation_medecin ADD CONSTRAINT FK_3ED52B98B5A53D8 FOREIGN KEY (medecin_id) REFERENCES medecin (id) ON DELETE CASCADE');
        }

        if (!$schema->hasTable('operation_infirmiere')) {
            $this->addSql('CREATE TABLE operation_infirmiere (operation_id INT NOT NULL, infirmiere_id INT NOT NULL, INDEX IDX_4C3DF6A67D85C95D (operation_id), INDEX IDX_4C3DF6A6EAB4A0A5 (infirmiere_id), PRIMARY KEY(operation_id, infirmiere_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE operation_infirmiere ADD CONSTRAINT FK_4C3DF6A67D85C95D FOREIGN KEY (operation_id) REFERENCES operation (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE operation_infirmiere ADD CONSTRAINT FK_4C3DF6A6EAB4A0A5 FOREIGN KEY (infirmiere_id) REFERENCES infirmiere (id) ON DELETE CASCADE');
        }

        if ($schema->hasTable('rapport')) {
            $rapportTable = $schema->getTable('rapport');

            if (!$rapportTable->hasColumn('operation_id')) {
                $this->addSql('ALTER TABLE rapport ADD operation_id INT DEFAULT NULL');
                $this->addSql('CREATE INDEX IDX_C8D93F5D7D85C95D ON rapport (operation_id)');
            }

            if ($rapportTable->hasColumn('patient_id')) {
                $this->addSql("INSERT INTO operation (patient_id, titre, date_operation, description) SELECT DISTINCT r.patient_id, 'Opération importée', NOW(), 'Créée automatiquement lors de la migration des rapports' FROM rapport r LEFT JOIN operation o ON o.patient_id = r.patient_id WHERE r.patient_id IS NOT NULL AND o.id IS NULL");
                $this->addSql('UPDATE rapport r INNER JOIN operation o ON o.patient_id = r.patient_id SET r.operation_id = o.id WHERE r.operation_id IS NULL');
            }

            $this->addSql('ALTER TABLE rapport MODIFY operation_id INT NOT NULL');
            $this->addSql('ALTER TABLE rapport ADD CONSTRAINT FK_C8D93F5D7D85C95D FOREIGN KEY (operation_id) REFERENCES operation (id)');

            if ($rapportTable->hasColumn('patient_id')) {
                $this->addSql('ALTER TABLE rapport DROP FOREIGN KEY FK_C8D93F5D6B899279');
                $this->addSql('DROP INDEX IDX_C8D93F5D6B899279 ON rapport');
                $this->addSql('ALTER TABLE rapport DROP patient_id');
            }
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('rapport')) {
            $rapportTable = $schema->getTable('rapport');

            if (!$rapportTable->hasColumn('patient_id')) {
                $this->addSql('ALTER TABLE rapport ADD patient_id INT DEFAULT NULL');
                $this->addSql('CREATE INDEX IDX_C8D93F5D6B899279 ON rapport (patient_id)');
            }

            if ($rapportTable->hasColumn('operation_id')) {
                $this->addSql('ALTER TABLE rapport DROP FOREIGN KEY FK_C8D93F5D7D85C95D');
                $this->addSql('DROP INDEX IDX_C8D93F5D7D85C95D ON rapport');
                $this->addSql('ALTER TABLE rapport DROP operation_id');
            }

            $this->addSql('ALTER TABLE rapport ADD CONSTRAINT FK_C8D93F5D6B899279 FOREIGN KEY (patient_id) REFERENCES Patient (id)');
        }

        if ($schema->hasTable('operation_infirmiere')) {
            $this->addSql('DROP TABLE operation_infirmiere');
        }

        if ($schema->hasTable('operation_medecin')) {
            $this->addSql('DROP TABLE operation_medecin');
        }

        if ($schema->hasTable('operation')) {
            $this->addSql('DROP TABLE operation');
        }
    }
}
