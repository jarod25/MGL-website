<?php

namespace App\Event\Mail;

use App\Event\Mail\MailEvent;

class ParticipantRegistrationEvent extends MailEvent
{
    public function getTemplate(): string { return 'mail/participant_registration.html.twig';}
    public function getSubject(): string { return "Confirmation d'inscription";}
}