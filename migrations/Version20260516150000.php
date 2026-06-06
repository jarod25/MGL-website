<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260516150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Link lan participant to user account and enforce unique user email';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE lan__participant ADD user_id INT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1256AC7CA76ED395 ON lan__participant (user_id)');
        $this->addSql('ALTER TABLE lan__participant ADD CONSTRAINT FK_1256AC7CA76ED395 FOREIGN KEY (user_id) REFERENCES user__user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_email ON user__user (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE lan__participant DROP CONSTRAINT FK_1256AC7CA76ED395');
        $this->addSql('DROP INDEX UNIQ_1256AC7CA76ED395');
        $this->addSql('ALTER TABLE lan__participant DROP user_id');
        $this->addSql('DROP INDEX uniq_user_email');
    }
}
