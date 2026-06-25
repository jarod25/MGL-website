<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class PracticalInformationController extends AbstractController
{
    #[Route('/practical-information', name: 'app_practical_information', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('practical_information/index.html.twig');
    }
}
