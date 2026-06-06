<?php

namespace App\Service\Lan;

use App\Entity\Game;
use App\Entity\Participant;
use App\Enum\RegistrationStatusEnum;
use App\Exception\Lan\LanRegistrationException;

final class ParticipantAccessChecker
{
    public function assertCanJoinGame(Participant $participant, Game $game): void
    {
        if ($participant->getRegistrationStatus() !== RegistrationStatusEnum::PAID) {
            throw new LanRegistrationException('Participant must be paid to join a team.');
        }

        $subscription = $participant->getSubscription();
        if ($subscription === null) {
            throw new LanRegistrationException('Participant subscription is required.');
        }

        if (!$game->isActive()) {
            throw new LanRegistrationException('Game is not active.');
        }

        $durationDays = $subscription->getDurationDays();
        if ($durationDays === null) {
            throw new LanRegistrationException('Subscription duration is invalid.');
        }

        if ($durationDays >= 2) {
            return;
        }

        if ($durationDays !== 1) {
            throw new LanRegistrationException('Unsupported subscription duration.');
        }

        $lockedDay = $participant->getLockedDay();
        if ($lockedDay === null) {
            $participant->setLockedDay($game->getDay());
            return;
        }

        if ($lockedDay !== $game->getDay()) {
            throw new LanRegistrationException('Participant day lock does not match this game day.');
        }
    }
}
