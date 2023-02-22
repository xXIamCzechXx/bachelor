<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Tournaments;
use App\Entity\TournamentsMaps;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\InputBag;
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
            'tournamentsMaps' => $tournamentsMaps,
        ]);
    }

    /**
     * @Route("/editor-edit/{id}/tournament-map", name="editor_edit_tournament_map", methods="POST")
     */
    public function editTournamentMap(TournamentsMaps $tournamentMap, Request $request)
    {
        if ($this->isGranted(self::ADMIN)) {
            $data = $request->request;
            $this->processRequest($tournamentMap, $data, $data->get('map-action'));
        } else {
            $this->addFlash(FLASH_DANGER, NO_RIGHTS);
        }

        return $this->redirectToRoute('editor_tournament_maps');
    }

    /**
     * @Route("/editor-add/tournament-map", name="editor_add_tournament_map", methods="POST")
     */
    public function addTournamentMap(Request $request)
    {
        if ($this->isGranted(self::SUPER_ADMIN)) {
            $this->processRequest(new TournamentsMaps(), $request->request, $request->request->get('map-action'));
        } else {
            $this->addFlash(FLASH_DANGER, NO_RIGHTS);
        }

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

    /**
     * @param $entity
     * @param InputBag $data
     * @param string $action
     */
    protected function processRequest($entity, InputBag $data, String $action = 'undefined')
    {
        $logger = new Log();
        $logger->setAction($action);

        switch ($action) {
            case 'add':
                if (!empty($data->get('map-bsr')) && !empty($data->get('map-difficulty'))) {
                    $entity
                        ->setBsr($data->get('map-bsr'))
                        ->setDifficulty($data->get('map-difficulty'))
                        ->setPool($data->get('map-pool'));

                    $tournamentMap = $this->getMapInfo($entity);
                    if (empty($tournamentMap->getName()) || empty($tournamentMap->getMaxScore())) {
                        $this->addFlash(FLASH_DANGER, 'Mapa nebo obtížnost nebyly nalezeny na bsaber.com');
                        $logger->setType(LOGGER_TYPE_FAILED);
                        $tournamentMap = null;
                        break;
                    }
                    $this->em->persist($entity);
                    $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste vložili mapu '.$data->get('map-name'));
                    break;
                }
                $this->addFlash(FLASH_DANGER, 'Není vyplněn bsr mapy nebo obtížnost, zkuste to prosím znovu');
                $logger->setType(LOGGER_TYPE_FAILED);
                break;

            case 'edit':
                if (!empty($data->get('map-bsr')) && !empty($data->get('map-difficulty'))) {
                    $entity
                        ->setBsr($data->get('map-bsr'))
                        ->setDifficulty($data->get('map-difficulty'))
                        ->setPool($data->get('map-pool'));

                    $tournamentMap = $this->getMapInfo($entity);
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

            case 'remove':
                foreach ($entity->getTournamentsScores() as $scores) {
                    $this->em->remove($entity->removeTournamentsScore($scores));
                }
                $this->em->remove($entity);
                $this->addFlash(FLASH_WARNING, 'Úspěšně jste odstranili mapu');
                break;

            default:
                $this->addFlash(FLASH_DANGER, UNEXPECTED_ERROR_FLASH);
                $logger
                    ->setOperation(UNEXPECTED_ERROR)
                    ->setType(LOGGER_TYPE_FAILED);
                break;
        }

        $logger = $this->completeLogger($logger, MODULE_TOURNAMENT, "ID: [ ".$entity->getId()." ] ");

        $this->em->persist($logger);
        $this->em->flush();
    }
}
