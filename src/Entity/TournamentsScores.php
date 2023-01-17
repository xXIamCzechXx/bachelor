<?php

namespace App\Entity;

use App\Repository\TournamentsScoresRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=TournamentsScoresRepository::class)
 */
class TournamentsScores
{
    use TimestampableEntity;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="tournamentsScores")
     */
    private $User;

    /**
     * @ORM\ManyToOne(targetEntity=TournamentsMaps::class, inversedBy="tournamentsScores")
     */
    private $Map;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $score;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $percentage;

    /**
     * @ORM\ManyToOne(targetEntity=Tournaments::class, inversedBy="tournamentsScores")
     */
    private $Tournament;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): self
    {
        $this->User = $User;

        return $this;
    }

    public function getMap(): ?TournamentsMaps
    {
        return $this->Map;
    }

    public function setMap(?TournamentsMaps $Map): self
    {
        $this->Map = $Map;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(?int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getPercentage(): ?float
    {
        return $this->percentage;
    }

    public function setPercentage(?float $percentage): self
    {
        $this->percentage = $percentage;

        return $this;
    }

    public function getTournament(): ?Tournaments
    {
        return $this->Tournament;
    }

    public function setTournament(?Tournaments $Tournament): self
    {
        $this->Tournament = $Tournament;

        return $this;
    }
}
