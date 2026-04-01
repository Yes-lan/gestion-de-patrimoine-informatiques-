<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260331091500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create test users with proper bcrypt hashes';
    }

    public function up(Schema $schema): void
    {
        // Clear existing users
        $this->addSql('DELETE FROM `user`');
        
        // Admin user - password: admin123
        $this->addSql(
            "INSERT INTO `user` (email, roles, password, nom, prenom) VALUES (?, ?, ?, ?, ?)",
            ['admin@hospital.fr', '["ROLE_ADMIN"]', '$2y$12$yDyXZarlmthrX4vhyQfCx.RHOk0pPydGYxrhgY9yx4JU.ICqkOGlS', 'Admin', 'Principal']
        );
        
        // Medecins - password: medecin123
        $this->addSql(
            "INSERT INTO `user` (email, roles, password, nom, prenom) VALUES (?, ?, ?, ?, ?)",
            ['dr.dupont@hospital.fr', '["ROLE_MEDECIN"]', '$2y$12$mxfXM2cjBxDjgoCgX6591uCs4ZkBB5SmaLf3K4Sbm0lhngz.YPHwm', 'Dupont', 'Jacques']
        );
        $this->addSql(
            "INSERT INTO `user` (email, roles, password, nom, prenom) VALUES (?, ?, ?, ?, ?)",
            ['dr.martin@hospital.fr', '["ROLE_MEDECIN"]', '$2y$12$ahAxdpVGDHDo694ZHWLh0u4FoiBLQfjG2mkXwATH8QeHGF8dzJk12', 'Martin', 'Sophie']
        );
        $this->addSql(
            "INSERT INTO `user` (email, roles, password, nom, prenom) VALUES (?, ?, ?, ?, ?)",
            ['dr.bernard@hospital.fr', '["ROLE_MEDECIN"]', '$2y$12$/yReQCIwO9UyIQU1iqKgUOWVkHico8Vam4BkocBPqgNpg3lrm4IDW', 'Bernard', 'Michel']
        );
        
        // Chirurgiens - password: chirurgien123
        $this->addSql(
            "INSERT INTO `user` (email, roles, password, nom, prenom) VALUES (?, ?, ?, ?, ?)",
            ['dr.rousseau@hospital.fr', '["ROLE_CHIRURGIEN"]', '$2y$12$gX.lrXE8j2l3SKvv7t17R.v8UHK/YbBMDnrFcVIdqGzWfU6ViQnCe', 'Rousseau', 'Pierre']
        );
        $this->addSql(
            "INSERT INTO `user` (email, roles, password, nom, prenom) VALUES (?, ?, ?, ?, ?)",
            ['dr.leclerc@hospital.fr', '["ROLE_CHIRURGIEN"]', '$2y$12$70LobEdjDaIq395GQTv13uEO5MA3fR23jvNL8QDh4082t3BS4cyD.', 'Leclerc', 'Francoise']
        );
        $this->addSql(
            "INSERT INTO `user` (email, roles, password, nom, prenom) VALUES (?, ?, ?, ?, ?)",
            ['dr.moreau@hospital.fr', '["ROLE_CHIRURGIEN"]', '$2y$12$yDdI1UykvhDh9Mu9U6HysuJ2uRYdkqKMRts6bO2S5OkB68bWZ34le', 'Moreau', 'Laurent']
        );
        
        // Infirmieres - password: infirm123
        $this->addSql(
            "INSERT INTO `user` (email, roles, password, nom, prenom) VALUES (?, ?, ?, ?, ?)",
            ['infirmiere.durand@hospital.fr', '["ROLE_INFIRMIERE"]', '$2y$12$.0kMTQx4c/oCMewa4Of7KOL.kK9r.V5go1dwOuL7DEP9Lnk9A1usS', 'Durand', 'Marie']
        );
        $this->addSql(
            "INSERT INTO `user` (email, roles, password, nom, prenom) VALUES (?, ?, ?, ?, ?)",
            ['infirmiere.petit@hospital.fr', '["ROLE_INFIRMIERE"]', '$2y$12$MBanOcerBQVG32TWFiixnuV7Am6NNzNA1WpfT1OfHQ2FaBnUa.yc.', 'Petit', 'Anne']
        );
        $this->addSql(
            "INSERT INTO `user` (email, roles, password, nom, prenom) VALUES (?, ?, ?, ?, ?)",
            ['infirmiere.richard@hospital.fr', '["ROLE_INFIRMIERE"]', '$2y$12$OHLupf06mVpLfg2enzAMieb8u3jkeQ5ta7eB01ZmypazVFHBv4PC.', 'Richard', 'Claire']
        );
        $this->addSql(
            "INSERT INTO `user` (email, roles, password, nom, prenom) VALUES (?, ?, ?, ?, ?)",
            ['infirmiere.thomas@hospital.fr', '["ROLE_INFIRMIERE"]', '$2y$12$aCSvgrA1aLXBKJRe1SisF.1Qv6hWVVKtv5.tgDLpBj7y.1yhvAfLa', 'Thomas', 'Isabelle']
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM `user`');
    }
}
