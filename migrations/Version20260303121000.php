<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260303121000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix nolan password';
    }

    public function up(Schema $schema): void
    {
        // Password hash for 'admin' using bcrypt with cost 12
        $passwordHash = '$2y$12$2giChfFQ55GsF8gwyfgmZeZi6pK6iwenybq2E/Gs3U1ZmIXh7TlPO';
        
        $this->addSql(
            "UPDATE user SET password = ? WHERE email = 'nolan.pichon87@gmail.com'",
            [$passwordHash]
        );
    }

    public function down(Schema $schema): void
    {
        // No rollback needed
    }
}
