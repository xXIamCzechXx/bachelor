<?php

namespace App\Entity;

use App\Repository\TournamentsMapsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=TournamentsMapsRepository::class)
 */
class TournamentsMaps
{
    use TimestampableEntity;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $bsr;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxScore;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $difficulty;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $pool;

    /**
     * @ORM\OneToMany(targetEntity=TournamentsScores::class, mappedBy="Map")
     */
    private $tournamentsScores;

    public function __construct()
    {
        $this->tournamentsScores = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBsr(): ?string
    {
        return $this->bsr;
    }

    public function setBsr(?string $bsr): self
    {
        $this->bsr = $bsr;

        return $this;
    }

    public function getMaxScore(): ?int
    {
        return $this->maxScore;
    }

    public function setMaxScore(?int $maxScore): self
    {
        $this->maxScore = $maxScore;

        return $this;
    }

    public function getDifficulty(): ?string
    {
        return $this->difficulty;
    }

    public function setDifficulty(?string $difficulty): self
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getPool(): ?string
    {
        return $this->pool;
    }

    public function setPool(string $pool): self
    {
        $this->pool = $pool;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getPools($pool = '')
    {
        if (empty($pool)) {
            return array(
                0 => 'Kvalifikace',
                1 => 'Minifinále',
                2 => 'Semifinále',
                3 => 'Gradfinále'
            );
        }
    }

    /**
     * @return Collection|TournamentsScores[]
     */
    public function getTournamentsScores(): Collection
    {
        return $this->tournamentsScores;
    }

    public function addTournamentsScore(TournamentsScores $tournamentsScore): self
    {
        if (!$this->tournamentsScores->contains($tournamentsScore)) {
            $this->tournamentsScores[] = $tournamentsScore;
            $tournamentsScore->setMap($this);
        }

        return $this;
    }

    public function removeTournamentsScore(TournamentsScores $tournamentsScore): self
    {
        if ($this->tournamentsScores->removeElement($tournamentsScore)) {
            // set the owning side to null (unless already changed)
            if ($tournamentsScore->getMap() === $this) {
                $tournamentsScore->setMap(null);
            }
        }

        return $this;
    }
}
