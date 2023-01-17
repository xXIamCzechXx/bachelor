<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Tournaments;
use App\Entity\TournamentsMaps;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EditorTournamentMapsController extends BaseEditorController
{
    /**
     * @Route("/editor-tournament-maps", name="editor_tournament_maps")
     */
    public function index(): Response
    {
        $tournamentsMaps = $this->em->getRepository(TournamentsMaps::class)->findAll();
        return $this->render('editor/editor_tournament/tournament_maps.html.twig', [
            'title' => EditorController::PAGE_TITLE,
            'tournamentsMaps' => $tournamentsMaps,
        ]);
    }

    /**
     * @Route("/editor-edit/{id}/tournament-map", name="editor_edit_tournament_map", methods="POST")
     */
    public function modifyTournamentMap(TournamentsMaps $tournamentMap, Request $request)
    {
        $logger = new Log();
        $data = $request->request;
        $logger->setAction($data->get('map-action'));

        switch ($data->get('map-action')) {
            case 'edit':
                if ($this->isGranted(SUPER_ADMIN)) {
                    if (!empty($data->get('map-bsr')) && !empty($data->get('map-difficulty'))) {

                        $tournamentMap
                            ->setBsr($data->get('map-bsr'))
                            ->setDifficulty($data->get('map-difficulty'))
                            ->setPool($data->get('map-pool'))
                        ;

                        $tournamentMap = $this->getMapInfo($tournamentMap);
                        if (empty($tournamentMap->getName()) || empty($tournamentMap->getMaxScore())) {
                            $this->addFlash(FLASH_DANGER, 'Mapa nebo obtížnost nebyly nalezeny na bsaber.com');
                            $logger->setType(LOGGER_TYPE_FAILED);
                            $tournamentMap = null;
                            break;
                        }
                        $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste změnili mapu');
                        break;
                    }
                    $this->addFlash(FLASH_DANGER, 'Není vyplněn bsr mapy nebo obtížnost, zkuste to prosím znovu');
                    $logger->setType(LOGGER_TYPE_FAILED);
                    break;
                }
                $this->addFlash(FLASH_DANGER, NO_RIGHTS);
                $logger
                        ->setOperation(NO_RIGHTS)
                        ->setType(LOGGER_TYPE_FAILED);
                break;

            case 'remove':
                if($this->isGranted(SUPER_ADMIN)) {
                    foreach ($tournamentMap->getTournamentsScores() as $scores) {
                        $this->em->remove($tournamentMap->removeTournamentsScore($scores));
                    }
                    $this->em->remove($tournamentMap);
                    $this->addFlash(FLASH_WARNING, 'Úspěšně jste odstranili mapu');
                    break;
                }
                $this->addFlash(FLASH_DANGER, NO_RIGHTS);
                $logger
                    ->setOperation(NO_RIGHTS)
                    ->setType(LOGGER_TYPE_FAILED);
                break;
            default:
                $this->addFlash(FLASH_DANGER, UNEXPECTED_ERROR_FLASH);
                $logger
                    ->setOperation(UNEXPECTED_ERROR)
                    ->setType(LOGGER_TYPE_FAILED);
                break;
        }

        if(empty($logger->getOperation()) && $tournamentMap !== null) {
            $logger->setOperation($tournamentMap !== null ? $tournamentMap->getName()." [ ".$tournamentMap->getId()." ] " : 'Operation was unsuccesful');
        }
        if (empty($logger->getType())) {
            $logger->setType(LOGGER_TYPE_SUCCESS);
        }

        $logger
            ->setModule($this->getModuleName(MODULE_TOURNAMENT))
            ->setUser($this->getUser())
            ->setUserName($this->getUser()->getFirstName())
        ;

        $this->em->persist($logger);
        $this->em->flush();

        return $this->redirectToRoute('editor_tournament_maps');
    }

    /**
     * @Route("/editor-add/tournament-map", name="editor_add_tournament_map", methods="POST")
     */
    public function addTournamentMap(Request $request)
    {
        $logger = new Log();
        $tournamentMap = new TournamentsMaps();
        $data = $request->request;
        $logger->setAction($data->get('map-action'));

        switch ($data->get('map-action')) {
            case 'add':
                if ($this->isGranted(SUPER_ADMIN)) {
                    if (!empty($data->get('map-bsr')) && !empty($data->get('map-difficulty'))) {

                        $tournamentMap
                            ->setBsr($data->get('map-bsr'))
                            ->setDifficulty($data->get('map-difficulty'))
                            ->setPool($data->get('map-pool'))
                        ;
                        $tournamentMap = $this->getMapInfo($tournamentMap);
                        if (empty($tournamentMap->getName()) || empty($tournamentMap->getMaxScore())) {
                            $this->addFlash(FLASH_DANGER, 'Mapa nebo obtížnost nebyly nalezeny na bsaber.com');
                            $logger->setType(LOGGER_TYPE_FAILED);
                            $tournamentMap = null;
                            break;
                        }
                        $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste vložili mapu '.$data->get('map-name'));
                        break;
                    }
                    $this->addFlash(FLASH_DANGER, 'Není vyplněn bsr mapy nebo obtížnost, zkuste to prosím znovu');
                    $logger->setType(LOGGER_TYPE_FAILED);
                    break;
                }
                $this->addFlash(FLASH_DANGER, NO_RIGHTS);
                $logger
                    ->setOperation(NO_RIGHTS)
                    ->setType(LOGGER_TYPE_FAILED);
                break;

            default:
                $this->addFlash(FLASH_DANGER, UNEXPECTED_ERROR_FLASH);
                $logger
                    ->setOperation(UNEXPECTED_ERROR)
                    ->setType(LOGGER_TYPE_FAILED);
                break;
        }

        if(empty($logger->getOperation())) {
            $logger->setOperation($tournamentMap !== null ? $tournamentMap->getName() : 'Operation was unsuccesful');
        }

        if (empty($logger->getType())) {
            $logger->setType(LOGGER_TYPE_SUCCESS);
        }

        $logger
            ->setModule($this->getModuleName(MODULE_TOURNAMENT))
            ->setUser($this->getUser())
            ->setUserName($this->getUser()->getFirstName());

        $this->em->persist($logger);
        if ($tournamentMap) {
            $this->em->persist($tournamentMap);
        }
        $this->em->flush();

        return $this->redirectToRoute('editor_tournament_maps');
    }

    /**
     * @param TournamentsMaps $tournamentsMaps
     * @return TournamentsMaps
     */
    public function getMapInfo(TournamentsMaps $tournamentMap)
    {
        $inDiff = $tournamentMap->getDifficulty();

        $context = stream_context_create(array('https' => array('header'=>'Connection: close\r\n')));
        $map = json_decode(@file_get_contents('https://api.beatsaver.com/maps/id/'.$tournamentMap->getBsr(), false, $context));
        if ($map) {
            $tournamentMap->setName($map->name);
            foreach ($map->versions[0]->diffs as $diff) {
                if ((string)$inDiff == (string)$diff->difficulty) {
                    $tournamentMap->setMaxScore($diff->maxScore);
                }
            }
        }

        return $tournamentMap;
    }
}
