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
            ['key' => 'phg_academy', 'name' => 'PHG Academy', 'logo' => 'phg-academy-logo.svg'],
            ['key' => 'crous_strasbourg', 'name' => 'Crous de Strasbourg', 'logo' => 'crous-strasbourg-logo.svg'],
            ['key' => 'mulhouse_alsace_agglomeration', 'name' => 'Mulhouse Alsace Agglomération', 'logo' => 'mulhouse-alsace-agglomeration-logo.svg'],
            ['key' => 'universite_haute_alsace', 'name' => 'Université de Haute-Alsace', 'logo' => 'universite-haute-alsace-logo.svg'],
            ['key' => 'ikoula', 'name' => 'Ikoula', 'logo' => 'ikoula-logo.svg', 'url' => 'https://www.ikoula.com/fr?utm_source=enscmu&utm_medium=banner&utm_campaign=iksvp'],
            ['key' => 'enscmu', 'name' => 'ENSCMu', 'logo' => 'enscmu-logo.svg'],
            ['key' => 'ensisa', 'name' => 'ENSISA', 'logo' => 'ensisa-logo.svg'],
        ];

        foreach ($partners as &$partner) {
            $partner['logoExists'] = file_exists($this->getParameter('kernel.project_dir').'/public/images/partners/'.$partner['logo']);
        }

        return $this->render('about/index.html.twig', [
            'partners' => $partners,
        ]);
    }
}
