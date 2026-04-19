<?php

namespace App\Service;

use App\Entity\Event;

class AvailablePlacesService
{
    public function calculateAvailablePlaces(Event $event): int
    {
        $totalPlaces = $event->getNbMaxParticipants();
        $registeredParticipants = count($event->getParticipants());

        return $totalPlaces - $registeredParticipants;
    }
}