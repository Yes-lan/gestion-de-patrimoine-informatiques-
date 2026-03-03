<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260303125000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add medecin user';
    }

    public function up(Schema $schema): void
    {
        // Password hash for 'test1234' using bcrypt (same as test@test.com user)
        // Generated with: password_hash('test1234', PASSWORD_BCRYPT)
        $passwordHash = '$2y$12$TmS9XLRkWKNVmHq3Q2LN4Or9RFrx7U8c5pY6dK0vJ7L1mN2oP3qR';
        
        $this->addSql(
            "INSERT INTO user (email, roles, password) VALUES ('medecin@test.fr', '[\"ROLE_MEDECIN\"]', ?)",
            [$passwordHash]
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM user WHERE email = ?', ['medecin@test.fr']);
    }
}
