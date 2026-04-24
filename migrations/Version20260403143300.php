<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260403143300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Patient CHANGE needs_greffe needs_greffe TINYINT DEFAULT NULL');
        $this->addSql('ALTER TABLE Patient RENAME INDEX uniq_patient_user TO UNIQ_D567EE77A76ED395');
        $this->addSql('ALTER TABLE patient_caregiver RENAME INDEX idx_8d1a82336b899279 TO IDX_81220CFA6B899279');
        $this->addSql('ALTER TABLE patient_caregiver RENAME INDEX idx_8d1a8233a76ed395 TO IDX_81220CFAA76ED395');
        $this->addSql('ALTER TABLE Serologie ADD Selorogie_EBV TINYINT NOT NULL, ADD Selorogie_toxoplasmose TINYINT NOT NULL, DROP Serologie_EBV, DROP Serologie_toxoplasmose');
        $this->addSql('ALTER TABLE operation_chirurgien RENAME INDEX idx_ea0a4c5d7d85c95d TO IDX_4FAF228344AC3583');
        $this->addSql('ALTER TABLE operation_chirurgien RENAME INDEX idx_ea0a4c5d8d93d649 TO IDX_4FAF2283A76ED395');
        $this->addSql('ALTER TABLE operation_infirmiere RENAME INDEX idx_4c3df6a67d85c95d TO IDX_134FF2FA44AC3583');
        $this->addSql('ALTER TABLE operation_infirmiere RENAME INDEX idx_4c3df6a68d93d649 TO IDX_134FF2FAA76ED395');
        $this->addSql('ALTER TABLE patient_note CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE patient_note RENAME INDEX idx_6a8a76b6b899279 TO IDX_462CB7DC6B899279');
        $this->addSql('ALTER TABLE patient_note RENAME INDEX idx_6a8a76b6b03a8386 TO IDX_462CB7DCB03A8386');
        $this->addSql('ALTER TABLE patient_photo DROP FOREIGN KEY `FK_6B2B6D68AE249D06`');
        $this->addSql('DROP INDEX IDX_6B2B6D68AE249D06 ON patient_photo');
        $this->addSql('ALTER TABLE patient_photo ADD createdAt DATETIME NOT NULL, DROP created_at, CHANGE original_name originalName VARCHAR(255) NOT NULL, CHANGE mime_type mimeType VARCHAR(100) NOT NULL, CHANGE uploaded_by_id uploadedBy_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE patient_photo ADD CONSTRAINT FK_81815FD7E91BE56 FOREIGN KEY (uploadedBy_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_81815FD7E91BE56 ON patient_photo (uploadedBy_id)');
        $this->addSql('ALTER TABLE patient_photo RENAME INDEX idx_6b2b6d686b899279 TO IDX_81815FD76B899279');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY `FK_55AFA7A8B03A8386`');
        $this->addSql('DROP INDEX IDX_55AFA7A8B03A8386 ON rendez_vous');
        $this->addSql('ALTER TABLE rendez_vous ADD scheduledAt DATETIME NOT NULL, ADD createdAt DATETIME NOT NULL, DROP scheduled_at, DROP created_at, CHANGE created_by_id createdBy_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A3174800F FOREIGN KEY (createdBy_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_65E8AA0A3174800F ON rendez_vous (createdBy_id)');
        $this->addSql('ALTER TABLE rendez_vous RENAME INDEX idx_55afa7a86b899279 TO IDX_65E8AA0A6B899279');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Patient CHANGE needs_greffe needs_greffe TINYINT DEFAULT 0');
        $this->addSql('ALTER TABLE Patient RENAME INDEX uniq_d567ee77a76ed395 TO UNIQ_PATIENT_USER');
        $this->addSql('ALTER TABLE Serologie ADD Serologie_EBV TINYINT NOT NULL, ADD Serologie_toxoplasmose TINYINT NOT NULL, DROP Selorogie_EBV, DROP Selorogie_toxoplasmose');
        $this->addSql('ALTER TABLE operation_chirurgien RENAME INDEX idx_4faf228344ac3583 TO IDX_EA0A4C5D7D85C95D');
        $this->addSql('ALTER TABLE operation_chirurgien RENAME INDEX idx_4faf2283a76ed395 TO IDX_EA0A4C5D8D93D649');
        $this->addSql('ALTER TABLE operation_infirmiere RENAME INDEX idx_134ff2faa76ed395 TO IDX_4C3DF6A68D93D649');
        $this->addSql('ALTER TABLE operation_infirmiere RENAME INDEX idx_134ff2fa44ac3583 TO IDX_4C3DF6A67D85C95D');
        $this->addSql('ALTER TABLE patient_caregiver RENAME INDEX idx_81220cfa6b899279 TO IDX_8D1A82336B899279');
        $this->addSql('ALTER TABLE patient_caregiver RENAME INDEX idx_81220cfaa76ed395 TO IDX_8D1A8233A76ED395');
        $this->addSql('ALTER TABLE patient_note CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE patient_note RENAME INDEX idx_462cb7dcb03a8386 TO IDX_6A8A76B6B03A8386');
        $this->addSql('ALTER TABLE patient_note RENAME INDEX idx_462cb7dc6b899279 TO IDX_6A8A76B6B899279');
        $this->addSql('ALTER TABLE patient_photo DROP FOREIGN KEY FK_81815FD7E91BE56');
        $this->addSql('DROP INDEX IDX_81815FD7E91BE56 ON patient_photo');
        $this->addSql('ALTER TABLE patient_photo ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP createdAt, CHANGE uploadedBy_id uploaded_by_id INT DEFAULT NULL, CHANGE originalName original_name VARCHAR(255) NOT NULL, CHANGE mimeType mime_type VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE patient_photo ADD CONSTRAINT `FK_6B2B6D68AE249D06` FOREIGN KEY (uploaded_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_6B2B6D68AE249D06 ON patient_photo (uploaded_by_id)');
        $this->addSql('ALTER TABLE patient_photo RENAME INDEX idx_81815fd76b899279 TO IDX_6B2B6D686B899279');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A3174800F');
        $this->addSql('DROP INDEX IDX_65E8AA0A3174800F ON rendez_vous');
        $this->addSql('ALTER TABLE rendez_vous ADD scheduled_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP scheduledAt, DROP createdAt, CHANGE createdBy_id created_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT `FK_55AFA7A8B03A8386` FOREIGN KEY (created_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_55AFA7A8B03A8386 ON rendez_vous (created_by_id)');
        $this->addSql('ALTER TABLE rendez_vous RENAME INDEX idx_65e8aa0a6b899279 TO IDX_55AFA7A86B899279');
    }
}
