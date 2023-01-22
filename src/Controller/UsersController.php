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
        $usersData = $scoresaberApi->mapScoresaberUsersData($users);

        return $this->render('default/users/users.html.twig', [
            'page' => $page,
            'users' => $users,
            'usersData' => $usersData,
        ]);
    }

    /**
     * @Route("/players-data-ajaxize/{from}/{step}", name="playersDataAjaxize")
     */
    public function ajaxizePlayersData(String $from, String $step, Request $request, ScoresaberApi $scoresaberApi)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $users = $this->em->getRepository(User::class)->findVisibleUsersWithLimit(10, $from);
        $usersData = $scoresaberApi->mapScoresaberUsersData($users);

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
