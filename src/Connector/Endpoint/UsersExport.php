<?php

namespace App\Connector\Endpoint;

use App\Entity\User;

class UsersExport
{
    public function mapData($em): array
    {
        $users = $em->getRepository(User::class)->findAll();

        $data = [];

        foreach ($users as $key => $user) {
            /** @var User $user */
            $data[$key]['id'] = $user->getId();
            $data[$key]['username'] = $user->getNickname();
            $data[$key]['country'] = $user->getCountry()->getName();
            $data[$key]['connections']['scoresaberId'] = $user->getScoresaberId();
            $data[$key]['connections']['twitch'] = $user->getTwitchNickname();
            $data[$key]['connections']['discord'] = $user->getDiscordNickname();
            $data[$key]['website']['active'] = $user->getActive();
            $data[$key]['website']['color'] = $user->getColor();
            $data[$key]['image'] = $_SERVER['HTTP_HOST'].'/'.$user->getImgPath($user->getImgName());
            foreach ($user->getRoles() as $roleKey => $role) {
                $data[$key]['roles'][$roleKey] = $role;
            }
            foreach ($user->getBadges() as $badgeKey => $badge) {
                $data[$key]['badges'][$badge->getId()] = $badge->getName();
            }
            foreach ($user->getTournamentsScores() as $scoreKey => $tournamentsScore) {
                $data[$key]['score']['percentage'] = $tournamentsScore->getPercentage();
                $data[$key]['score']['score'] = $tournamentsScore->getScore();
                $data[$key]['score']['map'] = $tournamentsScore->getMap()->getName();
                $data[$key]['score']['tournament'] = $tournamentsScore->getTournament()->getName();
            }
            $data[$key]['scoresaber']['averagePercentage'] = $user->getAvgPercentage();
            $data[$key]['scoresaber']['pp'] = $user->getPp();
            $data[$key]['scoresaber']['rank'] = $user->getRank();
            $data[$key]['scoresaber']['countryRank'] = $user->getCountryRank();
            $data[$key]['created'] = $user->getCreatedAt();
//            $data[$key]['description'] = $user->getDescription();
        }

        return $data;
    }
}