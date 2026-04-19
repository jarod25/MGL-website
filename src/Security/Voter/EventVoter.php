<?php

namespace App\Security\Voter;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class EventVoter extends Voter
{
    const EDIT = 'edit';
    const DELETE = 'delete';
    const VIEW = 'view';

    public function __construct(
        private readonly Security $security
    )
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE, self::VIEW])
            && $subject instanceof Event;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::VIEW => $this->canView($subject),
            default => false,
        };

    }

    private function canEdit(Event $event, ?User $user): bool
    {
        return $event->getOwner() === $user;
    }

    private function canDelete(Event $event, User $user): bool
    {
        return canEdit($event, $user);
    }

    private function canView(Event $event): bool
    {
        if ($event->isIsPublic()) {
            return true;
        }

        return $this->security->isGranted('IS_AUTHENTICATED_FULLY');
    }
}
