<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260609123157 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE events__events (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, nb_max_participants INT NOT NULL, is_public TINYINT(1) NOT NULL, is_payable TINYINT(1) NOT NULL, price INT DEFAULT NULL, INDEX IDX_FCD5C9D37E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE events__participants (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, user_id INT DEFAULT NULL, payment_status VARCHAR(255) DEFAULT NULL, has_paid TINYINT(1) DEFAULT NULL, INDEX IDX_9616731C71F7E88B (event_id), INDEX IDX_9616731CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lan__game (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, max_players_per_team INT NOT NULL, max_teams INT NOT NULL, is_active TINYINT(1) NOT NULL, day VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_DE62FD2B989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lan__participant (id INT AUTO_INCREMENT NOT NULL, subscription_id INT DEFAULT NULL, user_id INT DEFAULT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, discord_pseudo VARCHAR(255) DEFAULT NULL, is_major_confirmed TINYINT(1) NOT NULL, registration_status VARCHAR(255) NOT NULL, internal_reference VARCHAR(64) NOT NULL, locked_day VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', paid_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_1256AC7C9A1887DC (subscription_id), UNIQUE INDEX UNIQ_1256AC7CA76ED395 (user_id), UNIQUE INDEX uniq_lan_participant_email (email), UNIQUE INDEX uniq_lan_participant_internal_reference (internal_reference), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lan__subscription (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, hello_asso_tier_id VARCHAR(255) DEFAULT NULL, price INT NOT NULL, duration_days INT NOT NULL, is_active TINYINT(1) NOT NULL, UNIQUE INDEX uniq_lan_subscription_slug (slug), UNIQUE INDEX uniq_lan_subscription_helloasso_tier_id (hello_asso_tier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lan__team (id INT AUTO_INCREMENT NOT NULL, game_id INT NOT NULL, captain_id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_39A96AB8E48FD905 (game_id), INDEX IDX_39A96AB83346729B (captain_id), UNIQUE INDEX uniq_lan_team_game_name (game_id, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lan__team_member (id INT AUTO_INCREMENT NOT NULL, team_id INT NOT NULL, participant_id INT NOT NULL, game_id INT NOT NULL, in_game_pseudo VARCHAR(255) NOT NULL, joined_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C3367ACC296CD8AE (team_id), INDEX IDX_C3367ACC9D1C3019 (participant_id), INDEX IDX_C3367ACCE48FD905 (game_id), UNIQUE INDEX uniq_lan_team_member_participant_game (participant_id, game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user__user (id INT AUTO_INCREMENT NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', UNIQUE INDEX uniq_user_email (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE events__events ADD CONSTRAINT FK_FCD5C9D37E3C61F9 FOREIGN KEY (owner_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE events__participants ADD CONSTRAINT FK_9616731C71F7E88B FOREIGN KEY (event_id) REFERENCES events__events (id)');
        $this->addSql('ALTER TABLE events__participants ADD CONSTRAINT FK_9616731CA76ED395 FOREIGN KEY (user_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE lan__participant ADD CONSTRAINT FK_1256AC7C9A1887DC FOREIGN KEY (subscription_id) REFERENCES lan__subscription (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE lan__participant ADD CONSTRAINT FK_1256AC7CA76ED395 FOREIGN KEY (user_id) REFERENCES user__user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lan__team ADD CONSTRAINT FK_39A96AB8E48FD905 FOREIGN KEY (game_id) REFERENCES lan__game (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE lan__team ADD CONSTRAINT FK_39A96AB83346729B FOREIGN KEY (captain_id) REFERENCES lan__participant (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE lan__team_member ADD CONSTRAINT FK_C3367ACC296CD8AE FOREIGN KEY (team_id) REFERENCES lan__team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lan__team_member ADD CONSTRAINT FK_C3367ACC9D1C3019 FOREIGN KEY (participant_id) REFERENCES lan__participant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lan__team_member ADD CONSTRAINT FK_C3367ACCE48FD905 FOREIGN KEY (game_id) REFERENCES lan__game (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE events__events DROP FOREIGN KEY FK_FCD5C9D37E3C61F9');
        $this->addSql('ALTER TABLE events__participants DROP FOREIGN KEY FK_9616731C71F7E88B');
        $this->addSql('ALTER TABLE events__participants DROP FOREIGN KEY FK_9616731CA76ED395');
        $this->addSql('ALTER TABLE lan__participant DROP FOREIGN KEY FK_1256AC7C9A1887DC');
        $this->addSql('ALTER TABLE lan__participant DROP FOREIGN KEY FK_1256AC7CA76ED395');
        $this->addSql('ALTER TABLE lan__team DROP FOREIGN KEY FK_39A96AB8E48FD905');
        $this->addSql('ALTER TABLE lan__team DROP FOREIGN KEY FK_39A96AB83346729B');
        $this->addSql('ALTER TABLE lan__team_member DROP FOREIGN KEY FK_C3367ACC296CD8AE');
        $this->addSql('ALTER TABLE lan__team_member DROP FOREIGN KEY FK_C3367ACC9D1C3019');
        $this->addSql('ALTER TABLE lan__team_member DROP FOREIGN KEY FK_C3367ACCE48FD905');
        $this->addSql('DROP TABLE events__events');
        $this->addSql('DROP TABLE events__participants');
        $this->addSql('DROP TABLE lan__game');
        $this->addSql('DROP TABLE lan__participant');
        $this->addSql('DROP TABLE lan__subscription');
        $this->addSql('DROP TABLE lan__team');
        $this->addSql('DROP TABLE lan__team_member');
        $this->addSql('DROP TABLE user__user');
    }
}
