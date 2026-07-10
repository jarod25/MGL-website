<?php

namespace App\Service\Lan;

use App\Entity\Game;
use App\Repository\TeamRepository;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class TeamSlugGenerator
{
    private readonly AsciiSlugger $slugger;

    public function __construct(
        private readonly TeamRepository $teamRepository,
    ) {
        $this->slugger = new AsciiSlugger();
    }

    public function generate(Game $game, string $teamName): string
    {
        $baseSlug = strtolower($this->slugger->slug(trim($teamName))->toString());
        $baseSlug = trim($baseSlug, '-');

        if ($baseSlug === '') {
            $baseSlug = 'team';
        }

        $slug = $baseSlug;
        $suffix = 2;

        while ($this->teamRepository->slugExistsForGame($game, $slug)) {
            $slug = sprintf('%s-%d', $baseSlug, $suffix);
            ++$suffix;
        }

        return $slug;
    }
}
