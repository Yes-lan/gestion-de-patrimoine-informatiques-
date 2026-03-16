<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260313183000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create mobile rendez-vous and patient photo tables';
    }

    public function up(Schema $schema): void
    {
        if (!$this->tableExists('rendez_vous')) {
            $this->addSql("CREATE TABLE rendez_vous (id INT AUTO_INCREMENT NOT NULL, patient_id INT NOT NULL, created_by_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, scheduled_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', location VARCHAR(255) DEFAULT NULL, status VARCHAR(50) NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_55AFA7A86B899279 (patient_id), INDEX IDX_55AFA7A8B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        }

        if (!$this->tableExists('patient_photo')) {
            $this->addSql("CREATE TABLE patient_photo (id INT AUTO_INCREMENT NOT NULL, patient_id INT NOT NULL, uploaded_by_id INT DEFAULT NULL, filename VARCHAR(255) NOT NULL, original_name VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL, caption VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_6B2B6D686B899279 (patient_id), INDEX IDX_6B2B6D68AE249D06 (uploaded_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        }

        if (!$this->foreignKeyExists('rendez_vous', 'FK_55AFA7A86B899279')) {
            $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_55AFA7A86B899279 FOREIGN KEY (patient_id) REFERENCES Patient (id) ON DELETE CASCADE');
        }

        if (!$this->foreignKeyExists('rendez_vous', 'FK_55AFA7A8B03A8386')) {
            $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_55AFA7A8B03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
        }

        if (!$this->foreignKeyExists('patient_photo', 'FK_6B2B6D686B899279')) {
            $this->addSql('ALTER TABLE patient_photo ADD CONSTRAINT FK_6B2B6D686B899279 FOREIGN KEY (patient_id) REFERENCES Patient (id) ON DELETE CASCADE');
        }

        if (!$this->foreignKeyExists('patient_photo', 'FK_6B2B6D68AE249D06')) {
            $this->addSql('ALTER TABLE patient_photo ADD CONSTRAINT FK_6B2B6D68AE249D06 FOREIGN KEY (uploaded_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->tableExists('rendez_vous') && $this->foreignKeyExists('rendez_vous', 'FK_55AFA7A86B899279')) {
            $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_55AFA7A86B899279');
        }

        if ($this->tableExists('rendez_vous') && $this->foreignKeyExists('rendez_vous', 'FK_55AFA7A8B03A8386')) {
            $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_55AFA7A8B03A8386');
        }

        if ($this->tableExists('patient_photo') && $this->foreignKeyExists('patient_photo', 'FK_6B2B6D686B899279')) {
            $this->addSql('ALTER TABLE patient_photo DROP FOREIGN KEY FK_6B2B6D686B899279');
        }

        if ($this->tableExists('patient_photo') && $this->foreignKeyExists('patient_photo', 'FK_6B2B6D68AE249D06')) {
            $this->addSql('ALTER TABLE patient_photo DROP FOREIGN KEY FK_6B2B6D68AE249D06');
        }

        $this->addSql('DROP TABLE IF EXISTS rendez_vous');
        $this->addSql('DROP TABLE IF EXISTS patient_photo');
    }

    private function tableExists(string $tableName): bool
    {
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table',
            ['table' => $tableName],
        ) > 0;
    }

    private function foreignKeyExists(string $tableName, string $constraintName): bool
    {
        return (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = :table AND CONSTRAINT_NAME = :constraint AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [
                'table' => $tableName,
                'constraint' => $constraintName,
            ],
        ) > 0;
    }
}
