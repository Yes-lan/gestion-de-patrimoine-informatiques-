<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227102000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert comprehensive test data';
    }

    public function up(Schema $schema): void
    {
        // Chirurgien (8 enregistrements)
        $this->addSql("INSERT INTO Chirurgien (Name) VALUES
('Dr. Lefevre'),
('Dr. Durand'),
('Dr. Martin'),
('Dr. Laurent'),
('Dr. Dubois'),
('Dr. Moreau'),
('Dr. Simon'),
('Dr. Dupont')");

        // DonneurM (10 enregistrements)
        $this->addSql("INSERT INTO DonneurM (Num_CRISTAL, Groupe_Sanguin) VALUES
(1234567, 'O+'),
(1234568, 'A+'),
(1234569, 'B+'),
(1234570, 'AB+'),
(1234571, 'O-'),
(1234572, 'A-'),
(1234573, 'B-'),
(1234574, 'AB-'),
(1234575, 'O+'),
(1234576, 'A+')");

        // DonneurV (10 enregistrements)
        $this->addSql("INSERT INTO DonneurV (Num_CRISTAL, Groupe_Sanguin) VALUES
(2234567, 'O+'),
(2234568, 'A+'),
(2234569, 'B+'),
(2234570, 'AB+'),
(2234571, 'O-'),
(2234572, 'A-'),
(2234573, 'B-'),
(2234574, 'AB-'),
(2234575, 'O+'),
(2234576, 'A+')");

        // GroupageHLA (10 enregistrements)
        $this->addSql("INSERT INTO GroupageHLA (HLA_A, HLA_B, HLA_Cw, HLA_DR, HLA_DQ, HLA_DP, Incompatibilites_HLA_A, Incompatibilites_HLA_B, Incompatibilites_HLA_Cw, Incompatibilites_HLA_DR, Incompatibilites_HLA_DQ, Incompatibilites_HLA_DP) VALUES
(1, 2, 3, 4, 5, 6, 0, 1, 0, 1, 0, 0),
(2, 3, 4, 5, 6, 7, 1, 0, 1, 0, 1, 0),
(3, 4, 5, 6, 7, 8, 0, 0, 0, 1, 0, 1),
(4, 5, 6, 7, 8, 9, 1, 1, 0, 0, 1, 0),
(5, 6, 7, 8, 9, 10, 0, 1, 1, 0, 0, 1),
(6, 7, 8, 9, 10, 11, 1, 0, 1, 1, 0, 0),
(7, 8, 9, 10, 11, 12, 0, 1, 0, 1, 1, 0),
(8, 9, 10, 11, 12, 13, 1, 1, 1, 0, 1, 1),
(9, 10, 11, 12, 13, 14, 0, 0, 0, 1, 0, 1),
(10, 11, 12, 13, 14, 15, 1, 0, 1, 0, 1, 0)");

        // Transfusion (10 enregistrements)
        $this->addSql("INSERT INTO Transfusion (CGR) VALUES
(1), (2), (3), (4), (5), (1), (2), (3), (4), (5)");

        // Materiel (10 enregistrements)
        $this->addSql("INSERT INTO Materiel (Sonde_JJ) VALUES
(1), (0), (1), (1), (0), (1), (0), (1), (1), (0)");
    }

    public function down(Schema $schema): void
    {
        // Delete test data
        $this->addSql("DELETE FROM Greffe WHERE patient_id BETWEEN 1 AND 5");
        $this->addSql("DELETE FROM Chirurgien");
        $this->addSql("DELETE FROM DonneurM");
        $this->addSql("DELETE FROM DonneurV");
        $this->addSql("DELETE FROM GroupageHLA");
        $this->addSql("DELETE FROM Transfusion");
        $this->addSql("DELETE FROM Materiel");
    }
}
