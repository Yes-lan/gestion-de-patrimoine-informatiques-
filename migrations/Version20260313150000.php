<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260313150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create care teams with members and linked patients';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE care_team (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE care_team_member (care_team_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_AEAE93891526159E (care_team_id), INDEX IDX_AEAE9389A76ED395 (user_id), PRIMARY KEY(care_team_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE care_team_patient (care_team_id INT NOT NULL, patient_id INT NOT NULL, INDEX IDX_7783DD0A1526159E (care_team_id), INDEX IDX_7783DD0A6B899279 (patient_id), PRIMARY KEY(care_team_id, patient_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE care_team_member ADD CONSTRAINT FK_AEAE93891526159E FOREIGN KEY (care_team_id) REFERENCES care_team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE care_team_member ADD CONSTRAINT FK_AEAE9389A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE care_team_patient ADD CONSTRAINT FK_7783DD0A1526159E FOREIGN KEY (care_team_id) REFERENCES care_team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE care_team_patient ADD CONSTRAINT FK_7783DD0A6B899279 FOREIGN KEY (patient_id) REFERENCES Patient (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE care_team_patient');
        $this->addSql('DROP TABLE care_team_member');
        $this->addSql('DROP TABLE care_team');
    }
}
