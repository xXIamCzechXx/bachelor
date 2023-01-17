<?php

namespace App\Controller;

use App\Connector\TournamentAssistantApi;
use App\Entity\Log;
use App\Entity\Tournaments;
use App\Entity\TournamentsMaps;
use App\Entity\TournamentsScores;
use App\Entity\User;
use App\Service\UserNormalizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TournamentController extends BaseController
{
    /**
     * @Route("/control-panel", name="controlPanel")
     * @IsGranted("ROLE_ADMIN")
     */
    public function renderTournamentControlPanel(Request $request): Response
    {
        $tournamentsMaps = $this->em->getRepository(TournamentsMaps::class)->findBy(['pool' => 'Kvalifikace']);
        $users = $this->em->getRepository(User::class)->findAll();
        return $this->render('default/connector/tournament_control_panel.html.twig', [
            'page' => null,
            'users' => $users,
            'tournamentsMaps' => $tournamentsMaps,
        ]);
    }

    /**
     * @Route("/control-panel/request", name="send_tournament_request", methods="POST")
     */
    public function processData(Request $request, UserNormalizer $userNormalizer)
    {
        $data = $request->request;
        $log = new Log();

        $approvedScores = 0;
        $notSubmitedPlayers = array();

        if ($data && !empty($request->request->get('data'))) {
            if (!empty($data->get('map'))) {
                $map = $this->em->getRepository(TournamentsMaps::class)->findOneBy(['id'=>$data->get('map')]);
                foreach(preg_split("/((\r?\n)|(\r\n?))/", $data->get('data')) as $key => $row){
                    $player = $this->em->getRepository(User::class)->findOneBy(['id' => $data->get('Player'.$key)]);
                    $scores = explode(' - ', $row);
                    array_shift($scores);
                    foreach ($scores as $score) {
                        $percentage = ((int)$score/(int)$map->getMaxScore())*100;
                        if ($map instanceof TournamentsMaps && $player instanceof User && (int)$score > 0 && (int)$percentage >=0) {
                            $tournament = $this->em->getRepository(Tournaments::class)->findOneBy(['name' => 'CST S2 - PlayZONE Arena']);
                            $tournamentScore = new TournamentsScores();
                            $tournamentScore
                                ->setUser($player)
                                ->setMap($map)
                                ->setPercentage($percentage)
                                ->setScore($score)
                                ->setTournament($tournament)
                            ;
                            $this->em->persist($tournamentScore);
                            $this->em->flush();
                            $player = $userNormalizer->calculateScore($player);
                            $this->em->persist($player);
                            $this->em->flush();
                            $approvedScores++;
                        } else {
                            $notSubmitedPlayers[] = $score;
                        }
                        break;
                    }
                    array_shift($scores);
                }
            } else {
                $this->addFlash(FLASH_DANGER, "Není vyplněná mapa");
            }
        } else {
            $this->addFlash("DANGER", "Nejsou vyplněná žádná scóre");
        }
        $log->setType(LOGGER_TYPE_SUCCESS)
            ->setUser($this->getUser())
            ->setAction("scoreboard submit")
            ->setModule("Scoreboard")
            ->setOperation("Submitlo se ".$approvedScores." výsledků, nepovedlo se submitnout skóre: ".implode(", ", $notSubmitedPlayers));
        $this->em->persist($log);
        $this->em->flush();

        $this->addFlash(FLASH_SUCCESS, "Submitlo se ".$approvedScores." výsledků, nepovedlo se submitnout skóre: ".implode(", ", $notSubmitedPlayers));
        return $this->redirectToRoute('controlPanel');
    }

    /**
     * @param string $bsr
     * @return mixed
     */
    public function getMapInfo($bsr = "")
    {
        $context = stream_context_create(array('https' => array('header'=>'Connection: close\r\n')));
        $map = json_decode(@file_get_contents('https://api.beatsaver.com/maps/id/'.$bsr, false, $context));
        return $map;
    }

    /**
     * @Route("/scoreboard", name="scoreboard")
     */
    public function renderScoreboard(Request $request, UserNormalizer $userNormalizer)
    {
        $users = $this->em->getRepository(User::class)->findAllSortByPercentage(300);
        $users = $userNormalizer->calculateScores($users);

        return $this->render('default/beat_saber/scoreboard.html.twig', [
            'users' => $users,
            'page' => null,
        ]);

        return $connector->processRequest($request);
    }

    /**
     * @Route("/scoreboard-update", name="scoreboardUpdate")
     */
    public function scoreboardAction(Request $request, UserNormalizer $userNormalizer)
    {
        $users = $this->em->getRepository(User::class)->findAllSortByPercentage(300);
        $users = $userNormalizer->calculateScores($users);

        if ($request->isXmlHttpRequest()) {
            return $this->render("default/beat_saber/scoreboard_data.html.twig", ['users' => $users]);
        }

        return $this->render('default/beat_saber/scoreboard.html.twig', [
            'users' => $users,
            'page' => null,
        ]);
    }

    /**
     * @Route("/api", name="api", methods={"GET"})
     */
    public function renderApi(Request $request)
    {
        $connector = new TournamentAssistantApi($request);

        return $connector->processRequest($request);
    }
}
