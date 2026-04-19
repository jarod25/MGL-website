<?php

namespace App\Event\Mail;

use App\Event\Mail\MailEvent;

class ParticipantUnregistrationEvent extends MailEvent
{
    public function getTemplate(): string { return 'mail/participant_unregistration.html.twig';}
    public function getSubject(): string { return "Confirmation de désinscription";}
}