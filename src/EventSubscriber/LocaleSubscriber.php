<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class LocaleSubscriber implements EventSubscriberInterface
{
    private const DEFAULT_LOCALE = 'en';
    private const SUPPORTED_LOCALES = ['en', 'fr', 'de'];

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $locale = self::DEFAULT_LOCALE;

        if ($request->hasSession()) {
            $sessionLocale = $request->getSession()->get('_locale');
            if (is_string($sessionLocale) && in_array($sessionLocale, self::SUPPORTED_LOCALES, true)) {
                $locale = $sessionLocale;
            }
        }

        $request->setLocale($locale);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 20],
        ];
    }
}
