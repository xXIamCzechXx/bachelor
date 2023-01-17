<?php

namespace App\Controller;

use App\Entity\GalleryImages;
use App\Entity\Tournaments;
use App\Entity\User;
use App\Repository\FormAnswersRepository;
use App\Repository\GalleryImagesRepository;
use App\Repository\LogRepository;
use App\Repository\NewsRepository;
use App\Repository\TournamentsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EditorController extends BaseEditorController
{

    use Traits\EditorErrorHandlers;

    const PAGE_TITLE = 'CzechSaber';

    /**
     * @Route("/editor-overview", name="editor")
     */
    public function index(UserRepository $userRepo, TournamentsRepository $tournamentsRepo, GalleryImagesRepository $galleryImagesRepo, NewsRepository $newsRepo, FormAnswersRepository $formAnswersRepo, LogRepository $logRepo): Response
    {
        $users = $userRepo->findAllOrderBy('createdAt', 'DESC');
        $usersCounter = $this->getLastMonthUsersCount($users);
        $tournaments = $tournamentsRepo->findAllOrderBy('id', 'DESC');
        $images = $galleryImagesRepo->findAllOrderBy('id', 'DESC');
        $news = $newsRepo->findAllOrderBy('id', 'DESC');
        $formAnswers = $formAnswersRepo->findAllOrderBy('id', 'DESC', 10, 0);
        $logs = $logRepo->findAllOrderBy('id', 'DESC', 10, 0);

        return $this->render('editor/index.html.twig', [
            'title' => self::PAGE_TITLE,
            'users' => $users,
            'tournaments' => $tournaments,
            'images' => $images,
            'news' => $news,
            'formAnswers' => $formAnswers,
            'logs' => $logs,
            'thisMonthUsersCounter' => $usersCounter['thisMonthCounter'],
            'lastMonthUsersCounter' => $usersCounter['lastMonthCounter'],
        ]);
    }

    public function getLastMonthUsersCount($users): array
    {
        $counter['lastMonthCounter'] = 0;
        $counter['thisMonthCounter'] = 0;
        $now = date("Y-m-d");
        $startOfLastMonth = date("Y-m-d", strtotime(" -2 months"));
        $endOfLastMoth = date("Y-m-d", strtotime(" -1 months"));

        foreach ($users as $user) {
            $createdAt = $user->getCreatedAt()->format("Y-m-d");

            if ($createdAt >= $startOfLastMonth && $createdAt < $endOfLastMoth) {
                $counter['lastMonthCounter']++;
            } elseif ($createdAt >= $endOfLastMoth && $createdAt <= $now) {
                $counter['thisMonthCounter']++;
            }
        }

        return $counter;
    }
}
