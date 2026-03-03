<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260303130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix medecin password';
    }

    public function up(Schema $schema): void
    {
        // Password hash for 'test1234' using bcrypt (correct hash from test@test.com)
        $passwordHash = '$2y$13$Do6vSZWJVD.vHQWaBq.Bd.L5jg4MJjVkG6wRaug5f1If2w79QVKhK';
        
        $this->addSql(
            "UPDATE user SET password = ? WHERE email = 'medecin@test.fr'",
            [$passwordHash]
        );
    }

    public function down(Schema $schema): void
    {
        // No rollback needed
    }
}
