<?php

namespace App\Event\Mail;

class ParticipantPaymentFailEvent extends MailEvent
{
    public function getTemplate(): string { return 'mail/participant_payment_fail.html.twig';}
    public function getSubject(): string { return "Erreur lors du paiement";}
}