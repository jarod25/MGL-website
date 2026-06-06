<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260516170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert user roles from serialized ARRAY to JSONB preserving ROLE_USER/ROLE_ADMIN';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE user__user ADD roles_json JSONB DEFAULT '[]'::jsonb NOT NULL");
        $this->addSql(<<<'SQL'
UPDATE user__user
SET roles_json = (
    CASE
        WHEN roles LIKE '%ROLE_ADMIN%' AND roles LIKE '%ROLE_USER%' THEN '["ROLE_USER","ROLE_ADMIN"]'::jsonb
        WHEN roles LIKE '%ROLE_ADMIN%' THEN '["ROLE_ADMIN","ROLE_USER"]'::jsonb
        WHEN roles LIKE '%ROLE_USER%' THEN '["ROLE_USER"]'::jsonb
        ELSE '["ROLE_USER"]'::jsonb
    END
)
SQL);
        $this->addSql('ALTER TABLE user__user DROP roles');
        $this->addSql('ALTER TABLE user__user RENAME COLUMN roles_json TO roles');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE user__user ADD roles_array TEXT DEFAULT 'a:1:{i:0;s:9:\"ROLE_USER\";}' NOT NULL");
        $this->addSql(<<<'SQL'
UPDATE user__user
SET roles_array = (
    CASE
        WHEN roles::text LIKE '%ROLE_ADMIN%' AND roles::text LIKE '%ROLE_USER%' THEN 'a:2:{i:0;s:9:"ROLE_USER";i:1;s:10:"ROLE_ADMIN";}'
        WHEN roles::text LIKE '%ROLE_ADMIN%' THEN 'a:2:{i:0;s:10:"ROLE_ADMIN";i:1;s:9:"ROLE_USER";}'
        ELSE 'a:1:{i:0;s:9:"ROLE_USER";}'
    END
)
SQL);
        $this->addSql('ALTER TABLE user__user DROP roles');
        $this->addSql('ALTER TABLE user__user RENAME COLUMN roles_array TO roles');
        $this->addSql("COMMENT ON COLUMN user__user.roles IS '(DC2Type:array)'");
    }
}
