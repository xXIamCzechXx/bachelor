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
            'title' => EditorController::PAGE_TITLE,
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
            'title' => EditorController::PAGE_TITLE,
            'tournamentsScores' => $tournamentsScores,
        ]);
    }

    /**
     * @Route("/editor-edit/{id}/tournament", name="editor_edit_tournament", methods="POST")
     */
    public function modifyTournament(Tournaments $tournament, Request $request)
    {
        $logger = new Log();
        $data = $request->request;
        $logger->setAction($data->get('tournament-action'));
        $date = new \DateTimeImmutable($data->get('tournament-date'));

        switch ($data->get('tournament-action')) {
            case 'edit':
                if ($this->isGranted(SUPER_ADMIN)) {
                    if (!empty($data->get('tournament-name'))) {

                        $tournament
                            ->setName($data->get('tournament-name'))
                            ->setDescription($data->get('tournament-description'))
                            ->setDate($date)
                        ;
                        $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste změnili turnaje');
                        break;
                    }
                    $this->addFlash(FLASH_DANGER, 'Není vyplněn název turnaje, zkuste to prosím znovu');
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
                    $this->em->remove($tournament);
                    $this->addFlash(FLASH_WARNING, 'Úspěšně jste odstranili turnaj');
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
            $logger->setOperation($tournament->getName()." [ ".$tournament->getId()." ] ");
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

        return $this->redirectToRoute('editor_tournament');
    }

    /**
     * @Route("/editor-add/tournament", name="editor_add_tournament", methods="POST")
     */
    public function addTournament(Request $request)
    {
        $logger = new Log();
        $tournament = new Tournaments();
        $data = $request->request;
        $logger->setAction($data->get('tournament-action'));
        $date = new \DateTimeImmutable($data->get('tournament-date'));

        switch ($data->get('tournament-action')) {
            case 'add':
                if ($this->isGranted(SUPER_ADMIN)) {
                    if (!empty($data->get('tournament-name'))) {
                        $tournament
                            ->setName($data->get('tournament-name'))
                            ->setDescription($data->get('tournament-description'))
                            ->setDate($date)
                        ;
                        $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste vytvořili turnaj '.$data->get('tournament-name'));
                        break;
                    }
                    $this->addFlash(FLASH_DANGER, 'Není vyplněn název turnaje, zkuste to prosím znovu');
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
            $logger->setOperation($tournament->getName());
        }

        if (empty($logger->getType())) {
            $logger->setType(LOGGER_TYPE_SUCCESS);
        }

        $logger
            ->setModule($this->getModuleName(MODULE_TOURNAMENT))
            ->setUser($this->getUser())
            ->setUserName($this->getUser()->getFirstName());

        $this->em->persist($logger);
        if ($tournament) {
            $this->em->persist($tournament);
        }
        $this->em->flush();

        return $this->redirectToRoute('editor_tournament');
    }

    /**
     * @Route("/editor-edit/{id}/score", name="editor_edit_score", methods="POST")
     */
    public function removeScore(Request $request, TournamentsScores $tournamentsScores, UserNormalizer $userNormalizer)
    {
        $logger = new Log(); // Upravit na user log
        $data = $request->request;
        $logger->setAction($data->get('score-action'));

        switch ($data->get('score-action')) {
            case 'remove':
                if($this->isGranted(SUPER_ADMIN)) {
                    $tournamentScore = $this->em->getRepository(TournamentsScores::class)->findOneBy(['id' => $tournamentsScores->getId()]);
                    $user = $tournamentScore->getUser();
                    $this->em->remove($tournamentScore);
                    $this->em->flush();
                    $user = $userNormalizer->calculateScore($user);
                    $this->em->persist($user);
                    $this->em->flush();
                    $this->addFlash(FLASH_WARNING, 'Úspěšně jste odstranili score a zaktualizovalo se skóre uživatele');
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

        if (empty($logger->getType())) {
            $logger->setType(LOGGER_TYPE_SUCCESS);
        }

        $logger->setOperation("Score delete");

        $logger
            ->setModule($this->getModuleName(MODULE_TOURNAMENT))
            ->setUser($this->getUser())
            ->setUserName($this->getUser()->getFirstName());

        $this->em->persist($logger);
        $this->em->flush();

        return $this->redirectToRoute('editor_tournament_scores');
    }
}
