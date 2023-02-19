<?php

namespace App\Controller;

use App\Entity\Pages;
use App\Entity\User;
use App\Connector\ScoresaberApi;
use phpDocumentor\Reflection\Types\Integer;
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
        $users = $this->em->getRepository(User::class)->findVisibleUsersWithLimit(10, 0);
//        $usersData = $scoresaberApi->mapUsersData($users);

        return $this->render('default/users/users.html.twig', [
            'page' => $page,
            'users' => $users,
//            'usersData' => $usersData,
        ]);
    }

    /**
     * @Route("/players-data-ajaxize/{from}/{step}", name="playersDataAjaxize")
     * Renders amount of users and their scoresaber data
     */
    public function ajaxizePlayersData(String $from, String $step, Request $request, ScoresaberApi $scoresaberApi)
    {
        if (!$request->isXmlHttpRequest()) { // Not an Ajax request -> 404
            throw $this->createNotFoundException();
        }

        $users = $this->em->getRepository(User::class)->findVisibleUsersWithLimit(10, $from);
        $usersData = $scoresaberApi->mapUsersData($users);

        return $this->render("default/users/components/user_card.html.html.twig",
            [
                'users' => $users,
                'usersData' => $usersData,
                'hideButton' => (int)$step > count($users),
            ]
        );
    }

    /**
     * @Route("/player-data-ajaxize/{id}", name="playerDataAjaxize")
     * Loads data of specific user and return template of his profile card
     */
    public function ajaxizePlayerData(User $user, Request $request, ScoresaberApi $scoresaberApi)
    {
        if (!$request->isXmlHttpRequest()) { // Not an Ajax request -> 404
            throw $this->createNotFoundException();
        }

        $scoresaberData = $scoresaberApi->mapUserData($user->getScoresaberId());

        return $this->render("default/users/detail/users_detail.html.twig",
            [
                'user' => $user,
                'scoresaberData' => $scoresaberData,
            ]
        );
    }
}
