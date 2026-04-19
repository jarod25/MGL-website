<?php

namespace App\Service;

use App\Event\Mail\MailEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

readonly class MailService
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public function sendEmail(MailEvent $mailEvent): void    {
        $email = (new TemplatedEmail())
            ->from('kohler.jarod.2004@gmail.com')
            ->to($mailEvent->getEmail())
            ->subject($mailEvent->getSubject())
            ->text('')
            ->htmlTemplate($mailEvent->getTemplate())
            ->context($mailEvent->getParams());

            $this->mailer->send($email);
    }
}
