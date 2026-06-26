<?php

namespace App\Controller;

use App\Service\PublicImageResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class AboutController extends AbstractController
{
    private const IKOULA_LOGO_URL = 'https://www.ikoula.com/themes/ikoula/images/logo_IKOULA_light_fr.svg';

    #[Route('/about', name: 'app_about', methods: ['GET'])]
    public function index(PublicImageResolver $publicImageResolver): Response
    {
        $partners = [
            ['key' => 'phg_academy', 'name' => 'PHG Academy', 'logoBasename' => 'phg-academy-logo'],
            ['key' => 'crous_strasbourg', 'name' => 'Crous de Strasbourg', 'logoBasename' => 'crous-strasbourg-logo'],
            ['key' => 'mulhouse_alsace_agglomeration', 'name' => 'Mulhouse Alsace Agglomération', 'logoBasename' => 'mulhouse-alsace-agglomeration-logo'],
            ['key' => 'universite_haute_alsace', 'name' => 'Université de Haute-Alsace', 'logoBasename' => 'universite-haute-alsace-logo'],
            ['key' => 'ikoula', 'name' => 'Ikoula', 'logoBasename' => 'ikoula-logo', 'url' => 'https://www.ikoula.com/fr?utm_source=enscmu&utm_medium=banner&utm_campaign=iksvp', 'remoteLogoPath' => self::IKOULA_LOGO_URL],
            ['key' => 'enscmu', 'name' => 'ENSCMu', 'logoBasename' => 'enscmu-logo'],
            ['key' => 'ensisa', 'name' => 'ENSISA', 'logoBasename' => 'ensisa-logo'],
        ];

        foreach ($partners as &$partner) {
            $partner['logoPath'] = $publicImageResolver->resolve('images/partners', $partner['logoBasename']);
        }
        unset($partner);

        return $this->render('about/index.html.twig', [
            'partners' => $partners,
        ]);
    }
}
