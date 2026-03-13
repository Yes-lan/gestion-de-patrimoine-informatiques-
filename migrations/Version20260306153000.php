<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260306153000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize Serologie Ag HBS column name to Selorogie_AgS_HBS';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("SET @old_col := (SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Serologie' AND COLUMN_NAME LIKE 'Selorogie_Ag%HBS' AND COLUMN_NAME <> 'Selorogie_AgS_HBS' LIMIT 1)");
        $this->addSql("SET @sql := IF(@old_col IS NULL, 'SELECT 1', CONCAT('ALTER TABLE Serologie CHANGE `', @old_col, '` `Selorogie_AgS_HBS` TINYINT NOT NULL'))");
        $this->addSql('PREPARE stmt FROM @sql');
        $this->addSql('EXECUTE stmt');
        $this->addSql('DEALLOCATE PREPARE stmt');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("SET @has_target := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Serologie' AND COLUMN_NAME = 'Selorogie_AgS_HBS')");
        $this->addSql("SET @has_legacy := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Serologie' AND COLUMN_NAME = 'Selorogie_Ag_HBS')");
        $this->addSql("SET @sql := IF(@has_target = 1 AND @has_legacy = 0, 'ALTER TABLE Serologie CHANGE `Selorogie_AgS_HBS` `Selorogie_Ag_HBS` TINYINT NOT NULL', 'SELECT 1')");
        $this->addSql('PREPARE stmt FROM @sql');
        $this->addSql('EXECUTE stmt');
        $this->addSql('DEALLOCATE PREPARE stmt');
    }
}
