<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260313170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace care teams with direct patient caregiver relation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE patient_caregiver (patient_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_8D1A82336B899279 (patient_id), INDEX IDX_8D1A8233A76ED395 (user_id), PRIMARY KEY(patient_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE patient_caregiver ADD CONSTRAINT FK_8D1A82336B899279 FOREIGN KEY (patient_id) REFERENCES Patient (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE patient_caregiver ADD CONSTRAINT FK_8D1A8233A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');

        $this->addSql('INSERT IGNORE INTO patient_caregiver (patient_id, user_id) SELECT DISTINCT ctp.patient_id, ctm.user_id FROM care_team_patient ctp INNER JOIN care_team_member ctm ON ctm.care_team_id = ctp.care_team_id');
        $this->addSql('INSERT IGNORE INTO patient_caregiver (patient_id, user_id) SELECT DISTINCT o.patient_id, c.user_id FROM operation_chirurgien oc INNER JOIN operation o ON o.id = oc.operation_id INNER JOIN Chirurgien c ON c.id = oc.chirurgien_id WHERE c.user_id IS NOT NULL');
        $this->addSql('INSERT IGNORE INTO patient_caregiver (patient_id, user_id) SELECT DISTINCT o.patient_id, i.user_id FROM operation_infirmiere oi INNER JOIN operation o ON o.id = oi.operation_id INNER JOIN infirmiere i ON i.id = oi.infirmiere_id WHERE i.user_id IS NOT NULL');

        $this->addSql('DROP TABLE care_team_patient');
        $this->addSql('DROP TABLE care_team_member');
        $this->addSql('DROP TABLE care_team');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE care_team (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE care_team_member (care_team_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_AEAE93891526159E (care_team_id), INDEX IDX_AEAE9389A76ED395 (user_id), PRIMARY KEY(care_team_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE care_team_patient (care_team_id INT NOT NULL, patient_id INT NOT NULL, INDEX IDX_7783DD0A1526159E (care_team_id), INDEX IDX_7783DD0A6B899279 (patient_id), PRIMARY KEY(care_team_id, patient_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE care_team_member ADD CONSTRAINT FK_AEAE93891526159E FOREIGN KEY (care_team_id) REFERENCES care_team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE care_team_member ADD CONSTRAINT FK_AEAE9389A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE care_team_patient ADD CONSTRAINT FK_7783DD0A1526159E FOREIGN KEY (care_team_id) REFERENCES care_team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE care_team_patient ADD CONSTRAINT FK_7783DD0A6B899279 FOREIGN KEY (patient_id) REFERENCES Patient (id) ON DELETE CASCADE');

        $this->addSql("INSERT INTO care_team (id, name, description) SELECT p.id, CONCAT('Équipe ', p.Name, ' ', p.FirstName), NULL FROM Patient p");
        $this->addSql('INSERT INTO care_team_patient (care_team_id, patient_id) SELECT p.id, p.id FROM Patient p');
        $this->addSql('INSERT INTO care_team_member (care_team_id, user_id) SELECT pc.patient_id, pc.user_id FROM patient_caregiver pc');

        $this->addSql('DROP TABLE patient_caregiver');
    }
}
