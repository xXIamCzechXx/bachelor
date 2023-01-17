<?php

namespace App\Entity;

use App\Repository\UserBadgesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=UserBadgesRepository::class)
 */
class UserBadges
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ImgName;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="badge")
     */
    private $users;

    /**
     * @ORM\Column(type="smallint")
     */
    private $view = 1;

    public function __construct()
    {
        $this->users = new ArrayCollection();
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

    public function getImgName(): ?string
    {
        return $this->ImgName;
    }

    public function setImgName(?string $ImgName): self
    {
        $this->ImgName = $ImgName;

        return $this;
    }

    public function getImgPath($index = null): ?string
    {
        if (null !== $index && '' !== $index) {
            if (file_exists('uploads/badges/'.$index)) {
                return 'uploads/badges/'.$index;
            } else if (file_exists('build/images/badges/'.$index)) {
                return 'build/images/badges/'.$index;
            }
        }

        return 'build/images/utilities/pripravujeme-dark.png';
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addBadge($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeBadge($this);
        }

        return $this;
    }

    public function getView(): ?int
    {
        return $this->view;
    }

    public function setView(int $view): self
    {
        $this->view = $view;

        return $this;
    }

    public function hide(): self
    {
        $this->view = 0;

        return $this;
    }

    public function show(): self
    {
        $this->view = 1;

        return $this;
    }
}
