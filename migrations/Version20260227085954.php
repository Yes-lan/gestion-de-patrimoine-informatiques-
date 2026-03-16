<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227085954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Chirurgien (id INT AUTO_INCREMENT NOT NULL, Name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE DonneurM (id INT AUTO_INCREMENT NOT NULL, Num_CRISTAL INT NOT NULL, Groupe_Sanguin VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE DonneurV (id INT AUTO_INCREMENT NOT NULL, Num_CRISTAL INT NOT NULL, Groupe_Sanguin VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE Greffe (id INT AUTO_INCREMENT NOT NULL, Fonctionnel TINYINT NOT NULL, Date_Fin_De_Fonction DATETIME NOT NULL, Type VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE GroupageHLA (id INT AUTO_INCREMENT NOT NULL, HLA_A INT NOT NULL, HLA_B INT NOT NULL, HLA_Cw INT NOT NULL, HLA_DR INT NOT NULL, HLA_DQ INT NOT NULL, HLA_DP INT NOT NULL, Incompatibilites_HLA_A INT NOT NULL, Incompatibilites_HLA_B INT NOT NULL, Incompatibilites_HLA_Cw INT NOT NULL, Incompatibilites_HLA_DR INT NOT NULL, Incompatibilites_HLA_DQ INT NOT NULL, Incompatibilites_HLA_DP INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE Materiel (id INT AUTO_INCREMENT NOT NULL, Sonde_JJ TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE Patient (id INT AUTO_INCREMENT NOT NULL, Name VARCHAR(255) NOT NULL, FirstName VARCHAR(255) NOT NULL, Ville VARCHAR(255) NOT NULL, Num_Dossier INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        // sample patients
        $this->addSql("INSERT INTO Patient (Name, FirstName, Ville, Num_Dossier) VALUES
            ('Dupont','Jean','Paris',1001),
            ('Martin','Marie','Lyon',1002),
            ('Durand','Luc','Marseille',1003),
            ('Petit','Anne','Toulouse',1004),
            ('Bernard','Paul','Nice',1005)");

        $this->addSql('CREATE TABLE Serologie (id INT AUTO_INCREMENT NOT NULL, Selorogie_CMV TINYINT NOT NULL, Serologie_EBV TINYINT NOT NULL, Serologie_toxoplasmose TINYINT NOT NULL, Selorogie_HIV TINYINT NOT NULL, Selorogie_HTVL TINYINT NOT NULL, Selorogie_syphilis TINYINT NOT NULL, Selorogie_HCV TINYINT NOT NULL, Selorogie_Ag_HBS TINYINT NOT NULL, Selorogie_Ac_HBS TINYINT NOT NULL, Selorogie_Ac_HBC TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE Transfusion (id INT AUTO_INCREMENT NOT NULL, CGR INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE Chirurgien');
        $this->addSql('DROP TABLE DonneurM');
        $this->addSql('DROP TABLE DonneurV');
        $this->addSql('DROP TABLE Greffe');
        $this->addSql('DROP TABLE GroupageHLA');
        $this->addSql('DROP TABLE Materiel');
        $this->addSql('DROP TABLE Patient');
        $this->addSql('DROP TABLE Serologie');
        $this->addSql('DROP TABLE Transfusion');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
