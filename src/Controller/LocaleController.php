<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class LocaleController extends AbstractController
{
    private const SUPPORTED_LOCALES = ['en', 'fr', 'de'];

    #[Route('/language/{locale}', name: 'app_locale_switch', requirements: ['locale' => 'en|fr|de'], methods: ['GET'])]
    public function switch(string $locale, Request $request): RedirectResponse
    {
        if (!in_array($locale, self::SUPPORTED_LOCALES, true)) {
            throw $this->createNotFoundException();
        }

        $request->getSession()->set('_locale', $locale);

        $referer = $request->headers->get('referer');
        if (is_string($referer) && $referer !== '' && !str_contains($referer, $request->getPathInfo())) {
            return new RedirectResponse($referer, Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_home');
    }
}
