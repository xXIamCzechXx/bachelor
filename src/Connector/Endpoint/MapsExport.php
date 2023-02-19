<?php

namespace App\Connector\Endpoint;

use App\Entity\TournamentsMaps;

class MapsExport
{
    public function mapData($em): array
    {
        $maps = $em->getRepository(TournamentsMaps::class)->findAll();
        $data = [];

        foreach ($maps as $key => $map) {
            /** @var TournamentsMaps $map */
            $data[$key]['id'] = $map->getId();
            $data[$key]['username'] = $map->getName();
            $data[$key]['bsr'] = $map->getBsr();
            $data[$key]['maxScore'] = $map->getMaxScore();
            $data[$key]['difficulty'] = $map->getDifficulty();
            $data[$key]['pool'] = $map->getPool();
            $data[$key]['inserted'] = $map->getCreatedAt();

            foreach ($map->getPools() as $poolKey => $pool) {
                $data[$key]['availablePools'][$poolKey] = $pool;
            }
        }

        return $data;
    }
}