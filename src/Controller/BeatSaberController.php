<?php

namespace App\Controller;

use App\Entity\Pages;
use App\Entity\TournamentsMaps;
use App\Entity\TournamentsScores;
use App\Entity\User;
use App\Service\UserNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class BeatSaberController extends BaseController
{
    /**
     * @Route("/beatsaber", name="beatsaber")
     */
    public function index(Request $request, UserNormalizer $userNormalizer): Response
    {
        if(!$page = $this->em->getRepository(Pages::class)->findOneBy(['name' => 'beatsaber'])) {
            throw $this->createNotFoundException();
        }

        $qualMaps = $this->em->getRepository(TournamentsMaps::class)->findby(['pool' => 'Kvalifikace']);
        $miniMaps = $this->em->getRepository(TournamentsMaps::class)->findby(['pool' => 'Minifinále']);
        $semiMaps = $this->em->getRepository(TournamentsMaps::class)->findby(['pool' => 'Semifinále']);
        $grandMaps = $this->em->getRepository(TournamentsMaps::class)->findby(['pool' => 'Grandfinále']);

        $scores = $this->em->getRepository(TournamentsScores::class)->findAll();
        $users = $this->em->getRepository(User::class)->findAllSortByPercentage(300);

        $users = $userNormalizer->calculateScores($users);

        return $this->render('default/beat_saber/beat_saber.html.twig', [
            'page' => $page,
            'qualMaps' => $qualMaps,
            'miniMaps' => $miniMaps,
            'semiMaps' => $semiMaps,
            'grandMaps' => $grandMaps,
            'scores' => $scores,
            'users' => $users,
        ]);
    }
}
