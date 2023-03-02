<?php

namespace App\Controller;

use App\Connector\ScoresaberApi;
use App\Entity\Pages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LeaderboardController extends BaseController
{
    #[Route('/leaderboard', name: 'app_leaderboard')]
    /**
     * @Route("/leaderboard", name="leaderboard")
     */
    public function index(Request $request, ScoresaberApi $scoresaberApi): Response
    {
        if(!$page = $this->em->getRepository(Pages::class)->findOneBy(['name' => 'leaderboard'])) {
            throw $this->createNotFoundException();
        }

        $players = $scoresaberApi->fetchTopFiftyData();

        return $this->render('default/leaderboard/leaderboard.html.twig', [
            'page' => $page,
            'players' => $players,
        ]);
    }
}
