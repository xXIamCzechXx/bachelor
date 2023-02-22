<?php

namespace App\Controller;

use App\Entity\Constants;
use App\Entity\Log;
use App\Entity\Tournaments;
use App\Entity\TournamentsScores;
use App\Entity\User;
use App\Repository\TournamentsScoresRepository;
use App\Service\UserNormalizer;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EditorTournamentController extends BaseEditorController
{
    /**
     * @Route("/editor-tournament", name="editor_tournament")
     */
    public function index(): Response
    {
        $tournaments = $this->em->getRepository(Tournaments::class)->findAll();
        return $this->render('editor/editor_tournament/tournament.html.twig', [
            'tournaments' => $tournaments,
        ]);
    }

    /**
     * @Route("/editor-tournament-scores", name="editor_tournament_scores")
     */
    public function renderScores(TournamentsScoresRepository $tournamentsScoresRepo): Response
    {
        $tournamentsScores = $tournamentsScoresRepo->findAllOrderBy('createdAt', 'DESC');
        return $this->render('editor/editor_tournament/tournament_scores.html.twig', [
            'tournamentsScores' => $tournamentsScores,
        ]);
    }

    /**
     * @Route("/editor-edit/{id}/tournament", name="editor_edit_tournament", methods="POST")
     */
    public function editTournament(Tournaments $tournament, Request $request)
    {
        if ($this->isGranted(self::SUPER_ADMIN)) {
            $data = $request->request;
            $this->processRequest($tournament, $data, $data->get('tournament-action'));
        } else {
            $this->addFlash(FLASH_DANGER, NO_RIGHTS);
        }

        return $this->redirectToRoute('editor_tournament');
    }

    /**
     * @Route("/editor-add/tournament", name="editor_add_tournament", methods="POST")
     */
    public function addTournament(Request $request)
    {
        if ($this->isGranted(self::SUPER_ADMIN)) {
            $this->processRequest(new Tournaments(), $request->request, $request->request->get('tournament-action'));
        } else {
            $this->addFlash(FLASH_DANGER, NO_RIGHTS);
        }

        return $this->redirectToRoute('editor_tournament');
    }

    /**
     * @Route("/editor-edit/{id}/score", name="editor_edit_score", methods="POST")
     */
    public function removeScore(Request $request, TournamentsScores $tournamentsScores, UserNormalizer $userNormalizer)
    {
        if ($this->isGranted(self::SUPER_ADMIN)) {
            $data = $request->request;
            $this->processRequest($tournamentsScores, $data, $data->get('score-action'), $request->files);
        } else {
            $this->addFlash(FLASH_DANGER, NO_RIGHTS);
        }

        return $this->redirectToRoute('editor_tournament_scores');
    }

    /**
     * @param $entity
     * @param InputBag $data
     * @param string $action
     */
    protected function processRequest($entity, InputBag $data, String $action = 'undefined', $files = null, $uploadHelper = null)
    {
        $logger = new Log();
        $logger->setAction($action);

        switch ($action) {
            case 'add':
                if (!empty($data->get('tournament-name'))) {
                    $date = new \DateTimeImmutable($data->get('tournament-date'));
                    $entity
                        ->setName($data->get('tournament-name'))
                        ->setDescription($data->get('tournament-description'))
                        ->setDate($date);

                    $this->em->persist($entity);
                    $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste vytvořili turnaj '.$data->get('tournament-name'));
                    break;
                }
                $this->addFlash(FLASH_DANGER, 'Není vyplněn název turnaje, zkuste to prosím znovu');
                $logger->setType(LOGGER_TYPE_FAILED);
                break;

            case 'edit':
                if (!empty($data->get('tournament-name'))) {
                    $date = new \DateTimeImmutable($data->get('tournament-date'));
                    $entity
                        ->setName($data->get('tournament-name'))
                        ->setDescription($data->get('tournament-description'))
                        ->setDate($date);

                    $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste změnili turnaje');
                    break;
                }
                $this->addFlash(FLASH_DANGER, 'Není vyplněn název turnaje, zkuste to prosím znovu');
                $logger->setType(LOGGER_TYPE_FAILED);
                break;

            case 'remove':
                $this->em->remove($entity);
                $this->addFlash(FLASH_WARNING, 'Úspěšně jste odstranili turnaj');
                break;

            case 'remove-score':
                if ($tournamentScore = $this->em->getRepository(TournamentsScores::class)->findOneBy(['id' => $entity->getId()])) {
                    $user = $tournamentScore->getUser();
                    $this->em->remove($tournamentScore);
                    $this->em->flush();
                    $userNormalizer = new UserNormalizer($this->em);
                    $user = $userNormalizer->calculateScore($user);
                    $this->em->persist($user);
                    $this->em->flush();
                    $this->addFlash(FLASH_WARNING, 'Úspěšně jste odstranili score a zaktualizovalo se skóre uživatele');
                }
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
