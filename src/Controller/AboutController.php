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
                'url' => 'https://phg.academy/',
            ],
            [
                'key' => 'crous_strasbourg',
                'name' => 'Crous de Strasbourg',
                'logoPath' => '/images/partners/crous-strasbourg-logo.jpg',
                'url' => 'https://www.crous-strasbourg.fr/',
            ],
            [
                'key' => 'universite_haute_alsace',
                'name' => 'Université de Haute-Alsace',
                'logoPath' => '/images/partners/universite-haute-alsace-logo.png',
                'url' => 'https://www.uha.fr/',
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
                'url' => 'https://www.enscmu.uha.fr/',
            ],
            [
                'key' => 'ensisa',
                'name' => 'ENSISA',
                'logoPath' => '/images/partners/ensisa-logo.png',
                'url' => 'https://www.ensisa.uha.fr/',
            ],
        ];

        $organizers = [
            [
                'key' => 'raphael',
                'firstname' => 'Raphaël B.',
                'roleKey' => 'organizer',
                'affiliation' => 'ENSCMu',
                'photoPath' => '/images/team/raphael.jpg',
            ],
            [
                'key' => 'alex',
                'firstname' => 'Alexandre M.',
                'roleKey' => 'organizer',
                'affiliation' => 'ENSCMu',
                'photoPath' => '/images/team/alex.png',
            ],
            [
                'key' => 'dominic',
                'firstname' => 'Dominic D.',
                'roleKey' => 'organizer',
                'affiliation' => 'ENSCMu',
                'photoPath' => '/images/team/dominic.png',
            ],
            [
                'key' => 'loic',
                'firstname' => 'Loïc',
                'roleKey' => 'organizer',
                'affiliation' => 'ENSCMu',
//                'photoPath' => '/images/team/loic.jpg',
            ],
            [
                'key' => 'martin',
                'firstname' => 'Martin C.',
                'roleKey' => 'organizer',
                'affiliation' => 'ENSCMu',
                'photoPath' => '/images/team/martin.png',
            ],
            [
                'key' => 'nathan',
                'firstname' => 'Nathan D.',
                'roleKey' => 'organizer',
                'affiliation' => 'ENSCMu',
                'photoPath' => '/images/team/nathan.jpg',
            ],
            [
                'key' => 'jean',
                'firstname' => 'Jean N.',
                'roleKey' => 'organizer',
                'affiliation' => 'ENSISA / XID',
                'photoPath' => '/images/team/jean.jpg',
            ],
            [
                'key' => 'jarod',
                'firstname' => 'Jarod K.',
                'roleKey' => 'developer',
                'affiliation' => 'ENSISA / XID',
                'photoPath' => '/images/team/jarod.jpg',
            ],
        ];

        return $this->render('about/index.html.twig', [
            'partners' => $partners,
            'organizers' => $organizers,
        ]);
    }
}
