<?php

namespace App\Connector;

use App\Entity\User;

/**
 * Entity
 * Service collecting scoresaber users data information
 */
class ScoresaberApi {

    /**
     * @param $users
     * @return array
     */
    public function mapUsersData($users): array
    {
        $players = [];

        /** @var User $user */
        foreach ($users as $user) {
            $players[$user->getId()] = $this->mapUserData($user->getScoresaberId());
        }

        return $players;
    }

    /**
     * @param $scoresaberId
     * @return array
     */
    public function mapUserData($scoresaberId): array
    {
        $return = array(
            'pp' => 0,
            'country' => 'empty',
            'rank' => 0,
            'countryRank' => 0,
            'inactive' => true,
            'averageRankedAccuracy' => 0,
            'totalPlayCount' => 0,
            'rankedPlayCount' => 0,
            'replaysWatched' => 0,
            'profilePicture' => '',
            'totalRankedScore' => 0,
            'mapped' => 0,
            'level' => 0,
        );

        if ($playerData = $this->fetchUserData($scoresaberId, 'full')) {
            $return = [
                'pp' => (float)$playerData->pp,
                'country' => (string)$playerData->country,
                'rank' => (int)$playerData->rank,
                'countryRank' => (int)$playerData->countryRank,
                'inactive' => (bool)$playerData->inactive,
                'averageRankedAccuracy' => $playerData->scoreStats !== null ? round((float)$playerData->scoreStats->averageRankedAccuracy, 2) : 0,
                'totalPlayCount' => (int)$playerData->scoreStats?->totalPlayCount,
                'rankedPlayCount' => (int)$playerData->scoreStats?->rankedPlayCount,
                'replaysWatched' => (int)$playerData->scoreStats?->replaysWatched,
                'profilePicture' => (string)$playerData->profilePicture,
                'totalRankedScore' => (int)$playerData->scoreStats?->totalRankedScore,
                'mapped' => 1,
                'level' => $this->calculateLevel((int)$playerData->scoreStats?->totalRankedScore),
            ];
        }

        return $return;
    }

    /**
     * @param null $scoresaberId
     * @param string $type
     * @return mixed
     */
    public function fetchUserData($scoresaberId = null, string $type = 'full'): mixed
    {
        if (null !== $scoresaberId && strlen($scoresaberId) === 17) {
            $context = stream_context_create(array('https' => array('header'=>'Connection: close\r\n')));
            try {
                $player = json_decode(@file_get_contents('https://scoresaber.com/api/player/' . $scoresaberId . '/' . $type, false, $context), false, 512, JSON_ERROR_NONE);
            } catch (\Exception $e) {
                $player = false;
            }

            return $player;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function fetchTopFiftyData()
    {
        $context = stream_context_create(array('https' => array('header'=>'Connection: close\r\n')));
        $players = json_decode(@file_get_contents('https://scoresaber.com/api/players?page=1&countries=cz&withMetadata=true', false, $context));
        foreach ($players->players as $key => $player) {
            $players->players[$key]->level = $this->calculateLevel((int)$player->scoreStats?->totalRankedScore);
        }
        return $players->players;
    }

    /**
     * @param int $score
     * @return int
     */
    public function calculateLevel(int $score = 0): int {
        return match (true) {
            $score <= 10000 => 1,
            $score <= 200000 => 2,
            $score <= 3000000 => 3,
            $score <= 40000000 => 4,
            $score <= 500000000 => 5,
            $score <= 600000000 => 6,
            $score <= 7000000000 => 7,
            default => 0,
        };
    }
}