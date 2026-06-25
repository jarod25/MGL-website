<?php

namespace App\Controller;

use App\Repository\GameRepository;
use App\Repository\SubscriptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(GameRepository $gameRepository, SubscriptionRepository $subscriptionRepository): Response
    {
        $games = $gameRepository->findActiveOrdered();
        $gamesByDay = [];

        foreach ($games as $game) {
            $gamesByDay[$game->getDay()->value][] = $game;
        }

        return $this->render('home/index.html.twig', [
            'games' => $games,
            'gamesByDay' => $gamesByDay,
            'subscriptions' => $subscriptionRepository->findActiveOrdered(),
            'eventLogoExists' => file_exists($this->getParameter('kernel.project_dir').'/public/images/branding/mgt-logo.svg'),
        ]);
    }
}
