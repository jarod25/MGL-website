<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260516135502 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE events__events_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE events__participants_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE lan__game_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE lan__participant_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE lan__subscription_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE lan__team_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE lan__team_member_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user__user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE events__events (id INT NOT NULL, owner_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, nb_max_participants INT NOT NULL, is_public BOOLEAN NOT NULL, is_payable BOOLEAN NOT NULL, price INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FCD5C9D37E3C61F9 ON events__events (owner_id)');
        $this->addSql('CREATE TABLE events__participants (id INT NOT NULL, event_id INT DEFAULT NULL, user_id INT DEFAULT NULL, payment_status VARCHAR(255) DEFAULT NULL, has_paid BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9616731C71F7E88B ON events__participants (event_id)');
        $this->addSql('CREATE INDEX IDX_9616731CA76ED395 ON events__participants (user_id)');
        $this->addSql('CREATE TABLE lan__game (id INT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, max_players_per_team INT NOT NULL, max_teams INT NOT NULL, is_active BOOLEAN NOT NULL, day VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DE62FD2B989D9B62 ON lan__game (slug)');
        $this->addSql('CREATE TABLE lan__participant (id INT NOT NULL, subscription_id INT DEFAULT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, discord_pseudo VARCHAR(255) DEFAULT NULL, is_major_confirmed BOOLEAN NOT NULL, registration_status VARCHAR(255) NOT NULL, internal_reference VARCHAR(64) NOT NULL, locked_day VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, paid_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1256AC7C9A1887DC ON lan__participant (subscription_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_lan_participant_email ON lan__participant (email)');
        $this->addSql('CREATE UNIQUE INDEX uniq_lan_participant_internal_reference ON lan__participant (internal_reference)');
        $this->addSql('COMMENT ON COLUMN lan__participant.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN lan__participant.paid_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE lan__subscription (id INT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, hello_asso_tier_id VARCHAR(255) DEFAULT NULL, price INT NOT NULL, duration_days INT NOT NULL, is_active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_lan_subscription_slug ON lan__subscription (slug)');
        $this->addSql('CREATE UNIQUE INDEX uniq_lan_subscription_helloasso_tier_id ON lan__subscription (hello_asso_tier_id)');
        $this->addSql('CREATE TABLE lan__team (id INT NOT NULL, game_id INT NOT NULL, captain_id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_39A96AB8E48FD905 ON lan__team (game_id)');
        $this->addSql('CREATE INDEX IDX_39A96AB83346729B ON lan__team (captain_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_lan_team_game_name ON lan__team (game_id, name)');
        $this->addSql('COMMENT ON COLUMN lan__team.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE lan__team_member (id INT NOT NULL, team_id INT NOT NULL, participant_id INT NOT NULL, game_id INT NOT NULL, in_game_pseudo VARCHAR(255) NOT NULL, joined_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C3367ACC296CD8AE ON lan__team_member (team_id)');
        $this->addSql('CREATE INDEX IDX_C3367ACC9D1C3019 ON lan__team_member (participant_id)');
        $this->addSql('CREATE INDEX IDX_C3367ACCE48FD905 ON lan__team_member (game_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_lan_team_member_participant_game ON lan__team_member (participant_id, game_id)');
        $this->addSql('COMMENT ON COLUMN lan__team_member.joined_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE user__user (id INT NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN user__user.roles IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE events__events ADD CONSTRAINT FK_FCD5C9D37E3C61F9 FOREIGN KEY (owner_id) REFERENCES user__user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE events__participants ADD CONSTRAINT FK_9616731C71F7E88B FOREIGN KEY (event_id) REFERENCES events__events (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE events__participants ADD CONSTRAINT FK_9616731CA76ED395 FOREIGN KEY (user_id) REFERENCES user__user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lan__participant ADD CONSTRAINT FK_1256AC7C9A1887DC FOREIGN KEY (subscription_id) REFERENCES lan__subscription (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lan__team ADD CONSTRAINT FK_39A96AB8E48FD905 FOREIGN KEY (game_id) REFERENCES lan__game (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lan__team ADD CONSTRAINT FK_39A96AB83346729B FOREIGN KEY (captain_id) REFERENCES lan__participant (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lan__team_member ADD CONSTRAINT FK_C3367ACC296CD8AE FOREIGN KEY (team_id) REFERENCES lan__team (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lan__team_member ADD CONSTRAINT FK_C3367ACC9D1C3019 FOREIGN KEY (participant_id) REFERENCES lan__participant (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lan__team_member ADD CONSTRAINT FK_C3367ACCE48FD905 FOREIGN KEY (game_id) REFERENCES lan__game (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lan__subscription ADD CONSTRAINT chk_lan_subscription_duration_days CHECK (duration_days IN (1, 2));');
        $this->addSql('ALTER TABLE lan__subscription ADD CONSTRAINT chk_lan_subscription_price_positive CHECK (price >= 0);');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE events__events_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE events__participants_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE lan__game_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE lan__participant_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE lan__subscription_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE lan__team_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE lan__team_member_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user__user_id_seq CASCADE');
        $this->addSql('ALTER TABLE events__events DROP CONSTRAINT FK_FCD5C9D37E3C61F9');
        $this->addSql('ALTER TABLE events__participants DROP CONSTRAINT FK_9616731C71F7E88B');
        $this->addSql('ALTER TABLE events__participants DROP CONSTRAINT FK_9616731CA76ED395');
        $this->addSql('ALTER TABLE lan__participant DROP CONSTRAINT FK_1256AC7C9A1887DC');
        $this->addSql('ALTER TABLE lan__team DROP CONSTRAINT FK_39A96AB8E48FD905');
        $this->addSql('ALTER TABLE lan__team DROP CONSTRAINT FK_39A96AB83346729B');
        $this->addSql('ALTER TABLE lan__team_member DROP CONSTRAINT FK_C3367ACC296CD8AE');
        $this->addSql('ALTER TABLE lan__team_member DROP CONSTRAINT FK_C3367ACC9D1C3019');
        $this->addSql('ALTER TABLE lan__team_member DROP CONSTRAINT FK_C3367ACCE48FD905');
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
