<?php

namespace App\Entity;

use App\Repository\TournamentsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=TournamentsRepository::class)
 */
class Tournaments
{
    use TimestampableEntity;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="tournaments")
     */
    private $players;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=TournamentsScores::class, mappedBy="Tournament")
     */
    private $tournamentsScores;

    public function __construct()
    {
        $this->players = new ArrayCollection();
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

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(User $player): self
    {
        if (!$this->players->contains($player)) {
            $this->players[] = $player;
        }

        return $this;
    }

    public function removePlayer(User $player): self
    {
        $this->players->removeElement($player);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
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
            $tournamentsScore->setTournament($this);
        }

        return $this;
    }

    public function removeTournamentsScore(TournamentsScores $tournamentsScore): self
    {
        if ($this->tournamentsScores->removeElement($tournamentsScore)) {
            // set the owning side to null (unless already changed)
            if ($tournamentsScore->getTournament() === $this) {
                $tournamentsScore->setTournament(null);
            }
        }

        return $this;
    }
}
