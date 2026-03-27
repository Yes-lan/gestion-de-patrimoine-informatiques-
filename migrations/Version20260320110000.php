<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260320110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migrate staff entities to user roles and drop medecin/chirurgien/infirmiere tables';
    }

    public function up(Schema $schema): void
    {
        if (!$this->columnExists('user', 'nom')) {
            $this->addSql('ALTER TABLE `user` ADD nom VARCHAR(100) DEFAULT NULL');
        }

        if (!$this->columnExists('user', 'prenom')) {
            $this->addSql('ALTER TABLE `user` ADD prenom VARCHAR(100) DEFAULT NULL');
        }

        $chirurgienTable = $this->resolveExistingTable(['chirurgien', 'Chirurgien']);
        if ($chirurgienTable !== null && $this->columnExists($chirurgienTable, 'user_id')) {
            $this->addSql(sprintf("UPDATE `user` u INNER JOIN `%s` c ON c.user_id = u.id SET u.nom = COALESCE(NULLIF(c.nom, ''), u.nom), u.prenom = COALESCE(NULLIF(c.prenom, ''), u.prenom)", $chirurgienTable));
        }

        if ($this->tableExists('infirmiere') && $this->columnExists('infirmiere', 'user_id')) {
            $this->addSql("UPDATE `user` u INNER JOIN infirmiere i ON i.user_id = u.id SET u.nom = COALESCE(NULLIF(i.nom, ''), u.nom), u.prenom = COALESCE(NULLIF(i.prenom, ''), u.prenom)");
        }

        if ($this->tableExists('medecin') && $this->columnExists('medecin', 'user_id')) {
            $this->addSql("UPDATE `user` u INNER JOIN medecin m ON m.user_id = u.id SET u.nom = COALESCE(NULLIF(m.nom, ''), u.nom), u.prenom = COALESCE(NULLIF(m.prenom, ''), u.prenom)");
        }

        if ($this->tableExists('operation_chirurgien') && $this->columnExists('operation_chirurgien', 'chirurgien_id')) {
            $this->addSql('CREATE TABLE operation_chirurgien_tmp (operation_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(operation_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

            if ($chirurgienTable !== null) {
                $this->addSql(sprintf('INSERT IGNORE INTO operation_chirurgien_tmp(operation_id, user_id) SELECT oc.operation_id, c.user_id FROM operation_chirurgien oc INNER JOIN `%s` c ON c.id = oc.chirurgien_id WHERE c.user_id IS NOT NULL', $chirurgienTable));
            }

            $this->addSql('DROP TABLE operation_chirurgien');
            $this->addSql('RENAME TABLE operation_chirurgien_tmp TO operation_chirurgien');
            $this->addSql('ALTER TABLE operation_chirurgien ADD INDEX IDX_EA0A4C5D7D85C95D (operation_id), ADD INDEX IDX_EA0A4C5D8D93D649 (user_id)');
            $this->addSql('ALTER TABLE operation_chirurgien ADD CONSTRAINT FK_EA0A4C5D7D85C95D FOREIGN KEY (operation_id) REFERENCES operation (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE operation_chirurgien ADD CONSTRAINT FK_EA0A4C5D8D93D649 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        }

        if ($this->tableExists('operation_infirmiere') && $this->columnExists('operation_infirmiere', 'infirmiere_id')) {
            $this->addSql('CREATE TABLE operation_infirmiere_tmp (operation_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(operation_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('INSERT IGNORE INTO operation_infirmiere_tmp(operation_id, user_id) SELECT oi.operation_id, i.user_id FROM operation_infirmiere oi INNER JOIN infirmiere i ON i.id = oi.infirmiere_id WHERE i.user_id IS NOT NULL');
            $this->addSql('DROP TABLE operation_infirmiere');
            $this->addSql('RENAME TABLE operation_infirmiere_tmp TO operation_infirmiere');
            $this->addSql('ALTER TABLE operation_infirmiere ADD INDEX IDX_4C3DF6A67D85C95D (operation_id), ADD INDEX IDX_4C3DF6A68D93D649 (user_id)');
            $this->addSql('ALTER TABLE operation_infirmiere ADD CONSTRAINT FK_4C3DF6A67D85C95D FOREIGN KEY (operation_id) REFERENCES operation (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE operation_infirmiere ADD CONSTRAINT FK_4C3DF6A68D93D649 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        }

        if ($this->tableExists('operation_medecin')) {
            $this->addSql('DROP TABLE operation_medecin');
        }

        if ($this->tableExists('medecin')) {
            $this->addSql('DROP TABLE medecin');
        }

        if ($this->tableExists('infirmiere')) {
            $this->addSql('DROP TABLE infirmiere');
        }

        if ($chirurgienTable !== null && $this->tableExists($chirurgienTable)) {
            $this->addSql(sprintf('DROP TABLE `%s`', $chirurgienTable));
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('SET FOREIGN_KEY_CHECKS=0');

        if (!$this->tableExists('medecin')) {
            $this->addSql('CREATE TABLE medecin (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, UNIQUE INDEX UNIQ_MEDECIN_USER (user_id), UNIQUE INDEX UNIQ_MEDECIN_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE medecin ADD CONSTRAINT FK_MEDECIN_USER FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        }

        if (!$this->tableExists('infirmiere')) {
            $this->addSql('CREATE TABLE infirmiere (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, UNIQUE INDEX UNIQ_INFIRMIERE_USER (user_id), UNIQUE INDEX UNIQ_INFIRMIERE_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE infirmiere ADD CONSTRAINT FK_INFIRMIERE_USER FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        }

        if (!$this->tableExists('Chirurgien') && !$this->tableExists('chirurgien')) {
            $this->addSql('CREATE TABLE Chirurgien (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, Name VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_CHIRURGIEN_USER (user_id), UNIQUE INDEX UNIQ_CHIRURGIEN_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE Chirurgien ADD CONSTRAINT FK_CHIRURGIEN_USER FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        }

        $chirurgienTable = $this->resolveExistingTable(['chirurgien', 'Chirurgien']);

            $this->addSql("INSERT IGNORE INTO medecin(user_id, nom, prenom, email) SELECT id, COALESCE(nom, ''), COALESCE(prenom, ''), email FROM `user` WHERE roles LIKE '%\"ROLE_MEDECIN\"%'");
            $this->addSql("INSERT IGNORE INTO infirmiere(user_id, nom, prenom, email) SELECT id, COALESCE(nom, ''), COALESCE(prenom, ''), email FROM `user` WHERE roles LIKE '%\"ROLE_INFIRMIERE\"%'");

        if ($chirurgienTable !== null) {
            $this->addSql(sprintf("INSERT IGNORE INTO `%s`(user_id, nom, prenom, email, Name) SELECT id, COALESCE(nom, ''), COALESCE(prenom, ''), email, CONCAT(COALESCE(prenom, ''), ' ', COALESCE(nom, '')) FROM `user` WHERE roles LIKE '%\"ROLE_CHIRURGIEN\"%'", $chirurgienTable));
        }

        if ($this->tableExists('operation_chirurgien') && $this->columnExists('operation_chirurgien', 'user_id') && $chirurgienTable !== null) {
            $this->addSql('CREATE TABLE operation_chirurgien_old (operation_id INT NOT NULL, chirurgien_id INT NOT NULL, PRIMARY KEY(operation_id, chirurgien_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql(sprintf('INSERT IGNORE INTO operation_chirurgien_old(operation_id, chirurgien_id) SELECT oc.operation_id, c.id FROM operation_chirurgien oc INNER JOIN `%s` c ON c.user_id = oc.user_id', $chirurgienTable));
            $this->addSql('DROP TABLE operation_chirurgien');
            $this->addSql('RENAME TABLE operation_chirurgien_old TO operation_chirurgien');
            $this->addSql('ALTER TABLE operation_chirurgien ADD INDEX IDX_EA0A4C5D7D85C95D (operation_id), ADD INDEX IDX_EA0A4C5DF39DECC7 (chirurgien_id)');
            $this->addSql('ALTER TABLE operation_chirurgien ADD CONSTRAINT FK_EA0A4C5D7D85C95D FOREIGN KEY (operation_id) REFERENCES operation (id) ON DELETE CASCADE');
            $this->addSql(sprintf('ALTER TABLE operation_chirurgien ADD CONSTRAINT FK_EA0A4C5DF39DECC7 FOREIGN KEY (chirurgien_id) REFERENCES `%s` (id) ON DELETE CASCADE', $chirurgienTable));
        }

        if ($this->tableExists('operation_infirmiere') && $this->columnExists('operation_infirmiere', 'user_id')) {
            $this->addSql('CREATE TABLE operation_infirmiere_old (operation_id INT NOT NULL, infirmiere_id INT NOT NULL, PRIMARY KEY(operation_id, infirmiere_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('INSERT IGNORE INTO operation_infirmiere_old(operation_id, infirmiere_id) SELECT oi.operation_id, i.id FROM operation_infirmiere oi INNER JOIN infirmiere i ON i.user_id = oi.user_id');
            $this->addSql('DROP TABLE operation_infirmiere');
            $this->addSql('RENAME TABLE operation_infirmiere_old TO operation_infirmiere');
            $this->addSql('ALTER TABLE operation_infirmiere ADD INDEX IDX_4C3DF6A67D85C95D (operation_id), ADD INDEX IDX_4C3DF6A6EAB4A0A5 (infirmiere_id)');
            $this->addSql('ALTER TABLE operation_infirmiere ADD CONSTRAINT FK_4C3DF6A67D85C95D FOREIGN KEY (operation_id) REFERENCES operation (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE operation_infirmiere ADD CONSTRAINT FK_4C3DF6A6EAB4A0A5 FOREIGN KEY (infirmiere_id) REFERENCES infirmiere (id) ON DELETE CASCADE');
        }

        if ($this->columnExists('user', 'nom')) {
            $this->addSql('ALTER TABLE `user` DROP COLUMN nom');
        }

        if ($this->columnExists('user', 'prenom')) {
            $this->addSql('ALTER TABLE `user` DROP COLUMN prenom');
        }

        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }

    private function tableExists(string $tableName): bool
    {
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table',
            ['table' => $tableName],
        ) > 0;
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column',
            [
                'table' => $tableName,
                'column' => $columnName,
            ],
        ) > 0;
    }

    private function resolveExistingTable(array $candidates): ?string
    {
        foreach ($candidates as $tableName) {
            if ($this->tableExists($tableName)) {
                return $tableName;
            }
        }

        return null;
    }
}
