<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260711000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add mandatory password change flag to users.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user__user ADD must_change_password TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user__user DROP must_change_password');
    }
}
