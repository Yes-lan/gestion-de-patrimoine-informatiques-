<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260306150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user_id FK to Patient, Medecin, Chirurgien, Infirmiere and remove password columns';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('Patient')) {
            $table = $schema->getTable('Patient');
            if (!$table->hasColumn('user_id')) {
                $this->addSql('ALTER TABLE Patient ADD user_id INT DEFAULT NULL');
            }
            $this->addSql('UPDATE Patient p LEFT JOIN `user` u ON u.id = p.user_id SET p.user_id = NULL WHERE p.user_id IS NOT NULL AND u.id IS NULL');
            if (!$table->hasForeignKey('FK_PATIENT_USER')) {
                $this->addSql('ALTER TABLE Patient ADD CONSTRAINT FK_PATIENT_USER FOREIGN KEY (user_id) REFERENCES `user` (id)');
            }
            if (!$table->hasIndex('UNIQ_PATIENT_USER')) {
                $this->addSql('CREATE UNIQUE INDEX UNIQ_PATIENT_USER ON Patient (user_id)');
            }
        }

        if ($schema->hasTable('medecin')) {
            $table = $schema->getTable('medecin');
            if (!$table->hasColumn('user_id')) {
                $this->addSql('ALTER TABLE medecin ADD user_id INT NOT NULL');
            }
            $this->addSql('DELETE m FROM medecin m LEFT JOIN `user` u ON u.id = m.user_id WHERE u.id IS NULL');
            if ($table->hasColumn('password')) {
                $this->addSql('ALTER TABLE medecin DROP password');
            }
            if (!$table->hasForeignKey('FK_MEDECIN_USER')) {
                $this->addSql('ALTER TABLE medecin ADD CONSTRAINT FK_MEDECIN_USER FOREIGN KEY (user_id) REFERENCES `user` (id)');
            }
            if (!$table->hasIndex('UNIQ_MEDECIN_USER')) {
                $this->addSql('CREATE UNIQUE INDEX UNIQ_MEDECIN_USER ON medecin (user_id)');
            }
        }

        if ($schema->hasTable('Chirurgien')) {
            $table = $schema->getTable('Chirurgien');
            if (!$table->hasColumn('user_id')) {
                $this->addSql('ALTER TABLE Chirurgien ADD user_id INT NOT NULL');
            }
            $this->addSql('DELETE c FROM Chirurgien c LEFT JOIN `user` u ON u.id = c.user_id WHERE u.id IS NULL');
            if ($table->hasColumn('password')) {
                $this->addSql('ALTER TABLE Chirurgien DROP password');
            }
            if (!$table->hasForeignKey('FK_CHIRURGIEN_USER')) {
                $this->addSql('ALTER TABLE Chirurgien ADD CONSTRAINT FK_CHIRURGIEN_USER FOREIGN KEY (user_id) REFERENCES `user` (id)');
            }
            if (!$table->hasIndex('UNIQ_CHIRURGIEN_USER')) {
                $this->addSql('CREATE UNIQUE INDEX UNIQ_CHIRURGIEN_USER ON Chirurgien (user_id)');
            }
        }

        if ($schema->hasTable('infirmiere')) {
            $table = $schema->getTable('infirmiere');
            if (!$table->hasColumn('user_id')) {
                $this->addSql('ALTER TABLE infirmiere ADD user_id INT NOT NULL');
            }
            $this->addSql('DELETE i FROM infirmiere i LEFT JOIN `user` u ON u.id = i.user_id WHERE u.id IS NULL');
            if ($table->hasColumn('password')) {
                $this->addSql('ALTER TABLE infirmiere DROP password');
            }
            if (!$table->hasForeignKey('FK_INFIRMIERE_USER')) {
                $this->addSql('ALTER TABLE infirmiere ADD CONSTRAINT FK_INFIRMIERE_USER FOREIGN KEY (user_id) REFERENCES `user` (id)');
            }
            if (!$table->hasIndex('UNIQ_INFIRMIERE_USER')) {
                $this->addSql('CREATE UNIQUE INDEX UNIQ_INFIRMIERE_USER ON infirmiere (user_id)');
            }
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('Patient')) {
            $table = $schema->getTable('Patient');
            if ($table->hasForeignKey('FK_PATIENT_USER')) {
                $this->addSql('ALTER TABLE Patient DROP FOREIGN KEY FK_PATIENT_USER');
            }
            if ($table->hasIndex('UNIQ_PATIENT_USER')) {
                $this->addSql('DROP INDEX UNIQ_PATIENT_USER ON Patient');
            }
            if ($table->hasColumn('user_id')) {
                $this->addSql('ALTER TABLE Patient DROP user_id');
            }
        }

        if ($schema->hasTable('medecin')) {
            $table = $schema->getTable('medecin');
            if (!$table->hasColumn('password')) {
                $this->addSql('ALTER TABLE medecin ADD password VARCHAR(255) NOT NULL');
            }
            if ($table->hasForeignKey('FK_MEDECIN_USER')) {
                $this->addSql('ALTER TABLE medecin DROP FOREIGN KEY FK_MEDECIN_USER');
            }
            if ($table->hasIndex('UNIQ_MEDECIN_USER')) {
                $this->addSql('DROP INDEX UNIQ_MEDECIN_USER ON medecin');
            }
            if ($table->hasColumn('user_id')) {
                $this->addSql('ALTER TABLE medecin DROP user_id');
            }
        }

        if ($schema->hasTable('Chirurgien')) {
            $table = $schema->getTable('Chirurgien');
            if (!$table->hasColumn('password')) {
                $this->addSql('ALTER TABLE Chirurgien ADD password VARCHAR(255) NOT NULL');
            }
            if ($table->hasForeignKey('FK_CHIRURGIEN_USER')) {
                $this->addSql('ALTER TABLE Chirurgien DROP FOREIGN KEY FK_CHIRURGIEN_USER');
            }
            if ($table->hasIndex('UNIQ_CHIRURGIEN_USER')) {
                $this->addSql('DROP INDEX UNIQ_CHIRURGIEN_USER ON Chirurgien');
            }
            if ($table->hasColumn('user_id')) {
                $this->addSql('ALTER TABLE Chirurgien DROP user_id');
            }
        }

        if ($schema->hasTable('infirmiere')) {
            $table = $schema->getTable('infirmiere');
            if (!$table->hasColumn('password')) {
                $this->addSql('ALTER TABLE infirmiere ADD password VARCHAR(255) NOT NULL');
            }
            if ($table->hasForeignKey('FK_INFIRMIERE_USER')) {
                $this->addSql('ALTER TABLE infirmiere DROP FOREIGN KEY FK_INFIRMIERE_USER');
            }
            if ($table->hasIndex('UNIQ_INFIRMIERE_USER')) {
                $this->addSql('DROP INDEX UNIQ_INFIRMIERE_USER ON infirmiere');
            }
            if ($table->hasColumn('user_id')) {
                $this->addSql('ALTER TABLE infirmiere DROP user_id');
            }
        }
    }
}
