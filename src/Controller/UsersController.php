<?php

namespace App\Controller;

use App\Entity\Pages;
use App\Entity\User;
use App\Connector\ScoresaberApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends DefaultController
{
    /**
     * @Route("/uzivatele", name="users")
     */
    public function index(Request $request): Response
    {
        if(!$page = $this->em->getRepository(Pages::class)->findOneBy(['name' => 'users'])) {
            throw $this->createNotFoundException();
        }

        $scoresaberApi = new ScoresaberApi();
        $users = $this->em->getRepository(User::class)->findVisibleUsersWithLimit(100, 0);
        $usersData = $scoresaberApi->mapScoresaberUsersData($users);

        return $this->render('default/users/users.html.twig', [
            'page' => $page,
            'users' => $users,
            'usersData' => $usersData,
        ]);
    }

    /**
     * @Route("/player-data-ajaxize/{id}", name="playerDataAjaxize")
     */
    public function ajaxizePlayerData(User $user, Request $request, ScoresaberApi $scoresaberApi)
    {
        $scoresaberData = $scoresaberApi->mapScoresaberUserData($user->getScoresaberId());

        if ($request->isXmlHttpRequest()) {
            return $this->render("default/users/detail/users_detail.html.twig",
                [
                    'user' => $user,
                    'scoresaberData' => $scoresaberData,
                ]
            );
        }
    }
}
