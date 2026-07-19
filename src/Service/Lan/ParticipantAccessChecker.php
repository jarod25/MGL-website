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
            throw new LanRegistrationException('team.error.participant_must_be_paid');
        }

        $subscription = $participant->getSubscription();
        if ($subscription === null) {
            throw new LanRegistrationException('team.error.participant_subscription_required');
        }

        if (!$game->isActive()) {
            throw new LanRegistrationException('team.error.game_not_active');
        }

        $durationDays = $subscription->getDurationDays();
        if ($durationDays === null) {
            throw new LanRegistrationException('team.error.subscription_duration_invalid');
        }

        if ($durationDays >= 2) {
            return;
        }

        if ($durationDays !== 1) {
            throw new LanRegistrationException('team.error.subscription_duration_unsupported');
        }

        $lockedDay = $participant->getLockedDay();
        if ($lockedDay === null) {
            $participant->setLockedDay($game->getDay());
            return;
        }

        if ($lockedDay !== $game->getDay()) {
            throw new LanRegistrationException('team.error.participant_day_lock_mismatch');
        }
    }
}
