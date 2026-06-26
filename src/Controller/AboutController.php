<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class AboutController extends AbstractController
{
    #[Route('/about', name: 'app_about', methods: ['GET'])]
    public function index(): Response
    {
        $partners = [
            [
                'key' => 'phg_academy',
                'name' => 'PHG Academy',
                'logoPath' => '/images/partners/phg-academy-logo.png',
            ],
            [
                'key' => 'crous_strasbourg',
                'name' => 'Crous de Strasbourg',
                'logoPath' => '/images/partners/crous-strasbourg-logo.jpg',
            ],
            [
                'key' => 'mulhouse_alsace_agglomeration',
                'name' => 'Mulhouse Alsace Agglomération',
                'logoPath' => '/images/partners/mulhouse-alsace-agglomeration-logo.png',
            ],
            [
                'key' => 'universite_haute_alsace',
                'name' => 'Université de Haute-Alsace',
                'logoPath' => '/images/partners/universite-haute-alsace-logo.png',
            ],
            [
                'key' => 'ikoula',
                'name' => 'Ikoula',
                'logoPath' => '/images/partners/ikoula-logo.svg',
                'url' => 'https://www.ikoula.com/fr?utm_source=enscmu&utm_medium=banner&utm_campaign=iksvp',
            ],
            [
                'key' => 'enscmu',
                'name' => 'ENSCMu',
                'logoPath' => '/images/partners/enscmu-logo.png',
            ],
            [
                'key' => 'ensisa',
                'name' => 'ENSISA',
                'logoPath' => '/images/partners/ensisa-logo.png',
            ],
        ];

        return $this->render('about/index.html.twig', [
            'partners' => $partners,
        ]);
    }
}
