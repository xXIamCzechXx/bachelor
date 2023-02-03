<?php

namespace App\Connector;

use App\Entity\User;

class ScoresaberApi {

    /**
     * @return mixed
     */
    public function fetchTopFiftyScoresaberData()
    {
        $context = stream_context_create(array('https' => array('header'=>'Connection: close\r\n')));
        $players = json_decode(@file_get_contents('https://scoresaber.com/api/players?page=1&countries=cz&withMetadata=false', false, $context));
        return $players->players;
    }

    /**
     * @return mixed
     */
    public function fetchUserScoresaberData($scoresaberId = null, $type = 'full')
    {
        if (null !== $scoresaberId || 0 !== $scoresaberId) {
            $context = stream_context_create(array('https' => array('header'=>'Connection: close\r\n')));
            $player = json_decode(@file_get_contents('https://scoresaber.com/api/player/'.$scoresaberId.'/'.$type, false, $context));
            if ($player) {
                return $player;
            }
        }

        return false;
    }

    /**
     * @param $users
     * @return array
     */
    public function mapScoresaberUsersData($users)
    {
        $players = [];

        /** @var User $user */
        foreach ($users as $user) {

            $players[$user->getId()] = array(
                "pp" => 0,
                "country" => 'empty',
                "rank" => 0,
                "countryRank" => 0,
                "inactive" => true,
                "averageRankedAccuracy" => 0,
                "totalPlayCount" => 0,
                "rankedPlayCount" => 0,
                "replaysWatched" => 0,
            );

            if ($playerData = $this->fetchUserScoresaberData($user->getScoresaberId(), 'basic')) {
                $players[$user->getId()] = array(
                    "pp" => (float)$playerData->pp,
                    "country" => (string)$playerData->country,
                    "rank" => (int)$playerData->rank,
                    "countryRank" => (int)$playerData->countryRank,
                    "inactive" => (bool)$playerData->inactive,
                    "averageRankedAccuracy" => $playerData->scoreStats !== null ? round((float)$playerData->scoreStats->averageRankedAccuracy, 2) : 0,
                    "totalPlayCount" => $playerData->scoreStats !== null ? (int)$playerData->scoreStats->totalPlayCount : 0,
                    "rankedPlayCount" => $playerData->scoreStats !== null ? (int)$playerData->scoreStats->rankedPlayCount : 0,
                    "replaysWatched" => $playerData->scoreStats !== null ? (int)$playerData->scoreStats->replaysWatched : 0,
                );
            }
        }
        return $players;
    }

    /**
     * @param $users
     * @return array
     */
    public function mapScoresaberUserData($scoresaberId)
    {
        $player = array(
            "pp" => 0,
            "country" => '---',
            "rank" => 0,
            "countryRank" => 0,
            "inactive" => true,
            "averageRankedAccuracy" => 0,
            "totalPlayCount" => 0,
            "rankedPlayCount" => 0,
            "replaysWatched" => 0,
        );

        if ($playerData = $this->fetchUserScoresaberData($scoresaberId)) {
            $player = array(
                "pp" => (float)$playerData->pp,
                "country" => $playerData->country,
                "rank" => (int)$playerData->rank,
                "countryRank" => (int)$playerData->countryRank,
                "inactive" => (bool)$playerData->inactive,
                "averageRankedAccuracy" => $playerData->scoreStats !== null ? round((float)$playerData->scoreStats->averageRankedAccuracy, 2) : 0,
                "totalPlayCount" => $playerData->scoreStats !== null ? (int)$playerData->scoreStats->totalPlayCount : 0,
                "rankedPlayCount" => $playerData->scoreStats !== null ? (int)$playerData->scoreStats->rankedPlayCount : 0,
                "replaysWatched" => $playerData->scoreStats !== null ? (int)$playerData->scoreStats->replaysWatched : 0,
            );
        }

        return $player;
    }
}