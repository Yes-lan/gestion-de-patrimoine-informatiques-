<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260313081837 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE PasswordHistory (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, password_hash VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_141801F268D3EA09 (User_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE PasswordHistory ADD CONSTRAINT FK_141801F268D3EA09 FOREIGN KEY (User_id) REFERENCES PasswordHistory (id)');
        $this->addSql('CREATE TRIGGER limit_password_history AFTER INSERT ON password_history FOR EACH ROW BEGIN DELETE FROM password_history WHERE user_id = NEW.user_id AND id NOT IN (SELECT id FROM (SELECT id FROM password_history WHERE user_id = NEW.user_id ORDER BY created_at DESC LIMIT 5) AS tmp);END;');
        $this->addSql('ALTER TABLE operation_medecin DROP FOREIGN KEY `FK_3ED52B987D85C95D`');
        $this->addSql('ALTER TABLE operation_medecin DROP FOREIGN KEY `FK_3ED52B98B5A53D8`');
        $this->addSql('DROP TABLE operation_medecin');
        $this->addSql('ALTER TABLE operation DROP FOREIGN KEY `FK_C1A7E48D6B899279`');
        $this->addSql('ALTER TABLE operation ADD CONSTRAINT FK_1981A66D6B899279 FOREIGN KEY (patient_id) REFERENCES Patient (id)');
        $this->addSql('ALTER TABLE operation RENAME INDEX idx_c1a7e48d6b899279 TO IDX_1981A66D6B899279');
        $this->addSql('ALTER TABLE operation_chirurgien RENAME INDEX idx_ea0a4c5d7d85c95d TO IDX_4FAF228344AC3583');
        $this->addSql('ALTER TABLE operation_chirurgien RENAME INDEX idx_ea0a4c5df39decc7 TO IDX_4FAF22836DB64F5D');
        $this->addSql('ALTER TABLE operation_infirmiere RENAME INDEX idx_4c3df6a67d85c95d TO IDX_134FF2FA44AC3583');
        $this->addSql('ALTER TABLE operation_infirmiere RENAME INDEX idx_4c3df6a6eab4a0a5 TO IDX_134FF2FA17A43BB9');
        $this->addSql('ALTER TABLE rapport ADD contenuHtml LONGTEXT NOT NULL, ADD contenuTexte LONGTEXT NOT NULL, ADD dateCreation DATETIME NOT NULL, ADD dateModification DATETIME NOT NULL, DROP contenu_html, DROP contenu_texte, DROP date_creation, DROP date_modification, CHANGE version version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE rapport RENAME INDEX idx_c8d93f5d7d85c95d TO IDX_BE34A09C44AC3583');
        $this->addSql('ALTER TABLE rapport RENAME INDEX idx_c8d93f5d73f0d95e TO IDX_BE34A09C60BB6FE6');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE operation_medecin (operation_id INT NOT NULL, medecin_id INT NOT NULL, INDEX IDX_3ED52B98B5A53D8 (medecin_id), INDEX IDX_3ED52B987D85C95D (operation_id), PRIMARY KEY (operation_id, medecin_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE operation_medecin ADD CONSTRAINT `FK_3ED52B987D85C95D` FOREIGN KEY (operation_id) REFERENCES operation (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE operation_medecin ADD CONSTRAINT `FK_3ED52B98B5A53D8` FOREIGN KEY (medecin_id) REFERENCES medecin (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE PasswordHistory DROP FOREIGN KEY FK_141801F268D3EA09');
        $this->addSql('DROP TABLE PasswordHistory');
        $this->addSql("DROP TRIGGER IF EXISTS limit_password_history");
        $this->addSql('ALTER TABLE operation DROP FOREIGN KEY FK_1981A66D6B899279');
        $this->addSql('ALTER TABLE operation ADD CONSTRAINT `FK_C1A7E48D6B899279` FOREIGN KEY (patient_id) REFERENCES Patient (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE operation RENAME INDEX idx_1981a66d6b899279 TO IDX_C1A7E48D6B899279');
        $this->addSql('ALTER TABLE operation_chirurgien RENAME INDEX idx_4faf22836db64f5d TO IDX_EA0A4C5DF39DECC7');
        $this->addSql('ALTER TABLE operation_chirurgien RENAME INDEX idx_4faf228344ac3583 TO IDX_EA0A4C5D7D85C95D');
        $this->addSql('ALTER TABLE operation_infirmiere RENAME INDEX idx_134ff2fa44ac3583 TO IDX_4C3DF6A67D85C95D');
        $this->addSql('ALTER TABLE operation_infirmiere RENAME INDEX idx_134ff2fa17a43bb9 TO IDX_4C3DF6A6EAB4A0A5');
        $this->addSql('ALTER TABLE rapport ADD contenu_html LONGTEXT NOT NULL, ADD contenu_texte LONGTEXT NOT NULL, ADD date_creation DATETIME NOT NULL, ADD date_modification DATETIME NOT NULL, DROP contenuHtml, DROP contenuTexte, DROP dateCreation, DROP dateModification, CHANGE version version INT NOT NULL');
        $this->addSql('ALTER TABLE rapport RENAME INDEX idx_be34a09c60bb6fe6 TO IDX_C8D93F5D73F0D95E');
        $this->addSql('ALTER TABLE rapport RENAME INDEX idx_be34a09c44ac3583 TO IDX_C8D93F5D7D85C95D');
    }
}
