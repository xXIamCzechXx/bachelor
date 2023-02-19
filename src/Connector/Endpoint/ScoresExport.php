<?php

namespace App\Connector\Endpoint;

use App\Entity\TournamentsScores;

class ScoresExport
{
    public function mapData($em): array
    {
        $scores = $em->getRepository(TournamentsScores::class)->findAll();

        $data = [];

        foreach ($scores as $key => $score) {
            /** @var TournamentsScores $score */
            $data[$key]['id'] = $score->getId();
            $data[$key]['map'] = $score->getMap()->getName();
            $data[$key]['mapId'] = $score->getMap()->getId();
            $data[$key]['tournament'] = $score->getTournament()->getName();
            $data[$key]['score'] = $score->getScore();
            $data[$key]['userId'] = $score->getUser()->getId();
            $data[$key]['userScoresaberId'] = $score->getUser()->getScoresaberId();
            $data[$key]['userNickname'] = $score->getUser()->getNickname();
            $data[$key]['created'] = $score->getCreatedAt();
        }

        return $data;
    }
}