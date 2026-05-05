<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260505120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create LAN game table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE lan__game (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, max_players_per_team INTEGER NOT NULL, max_teams INTEGER NOT NULL, is_active BOOLEAN NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_47D350B6989D9B62 ON lan__game (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE lan__game');
    }
}
