<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserNormalizer {

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function generateUniquePlayerId()
    {
        $randNumber = rand(1,999);
        $charOptions = "ABCD";
        $char = $charOptions[rand(0, strlen($charOptions)-1)];

        switch (mb_strlen($randNumber)) {
            case 1:
                $playerId = $char."00".$randNumber;
                break;
            case 2:
                $playerId = $char."0".$randNumber;
                break;
            default:
                $playerId = $char.$randNumber;
                break;
        }

        $userExists = $this->em->getRepository(User::class)->findOneBy(['uniqueId' => $playerId]);

        if ($userExists) {
            return $this->generateUniquePlayerId();
        }

        return $playerId;
    }

    public function calculateScores($users)
    {
        foreach ($users as $user) {
            $mapArray = array();
            $scores = $user->getTournamentsScores();

            foreach ($scores as $score) {
                if ($score->getMap()->getId() > 0 && $score->getPercentage() > 0) {
                    if (!isset($mapArray[$score->getMap()->getId()])) {
                        $mapArray[$score->getMap()->getId()] = array(
                                'percentage' => $score->getPercentage())
                        ;
                    } else {
                        if ($mapArray[$score->getMap()->getId()]['percentage'] < $score->getPercentage()) {
                            $mapArray[$score->getMap()->getId()] = array(
                                    'percentage' => $score->getPercentage())
                            ;
                        }
                    }
                }
            }

            $totalPercentage = 0;
            foreach ($mapArray as $key => $map) {
                $totalPercentage += $map['percentage'];
                if ($key == 1) {
                    break; // Ošetření proti odehrání vícero map
                }
            }
            $user->setAvgPercentage($totalPercentage/2);
        }

        return $users;
    }

    public function calculateScore($player)
    {
        $mapArray = array();
        $scores = $player->getTournamentsScores();

        foreach ($scores as $score) {
            if ($score->getMap()->getId() > 0 && $score->getPercentage() > 0) {
                if (!isset($mapArray[$score->getMap()->getId()])) {
                    $mapArray[$score->getMap()->getId()] = array(
                            'percentage' => $score->getPercentage())
                    ;
                } else {
                    if ($mapArray[$score->getMap()->getId()]['percentage'] < $score->getPercentage()) {
                        $mapArray[$score->getMap()->getId()] = array(
                                'percentage' => $score->getPercentage())
                        ;
                    }
                }
            }
        }

        $totalPercentage = 0;
        foreach ($mapArray as $key => $map) {
            $totalPercentage += $map['percentage'];
            if ($key == 1) {
                break; // Ošetření proti odehrání vícero map
            }
        }
        $player->setAvgPercentage($totalPercentage/2);

        return $player;
    }
}