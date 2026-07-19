<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260710000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add per-game slugs to LAN teams.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE lan__team ADD slug VARCHAR(255) DEFAULT NULL');

        $teams = $this->connection->fetchAllAssociative('SELECT id, game_id, name FROM lan__team ORDER BY game_id ASC, id ASC');
        $usedSlugsByGame = [];

        foreach ($teams as $team) {
            $gameId = (int) $team['game_id'];
            $baseSlug = $this->slugify((string) $team['name']);
            $slug = $baseSlug;
            $suffix = 2;

            while (isset($usedSlugsByGame[$gameId][$slug])) {
                $slug = sprintf('%s-%d', $baseSlug, $suffix);
                ++$suffix;
            }

            $usedSlugsByGame[$gameId][$slug] = true;

            $this->connection->update('lan__team', ['slug' => $slug], ['id' => (int) $team['id']]);
        }

        $this->addSql('ALTER TABLE lan__team CHANGE slug slug VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_lan_team_game_slug ON lan__team (game_id, slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_lan_team_game_slug ON lan__team');
        $this->addSql('ALTER TABLE lan__team DROP slug');
    }

    private function slugify(string $value): string
    {
        $value = trim($value);
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        if ($transliterated !== false) {
            $value = $transliterated;
        }

        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value !== '' ? $value : 'team';
    }
}
