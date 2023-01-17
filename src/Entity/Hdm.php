<?php

namespace App\Entity;

use App\Repository\HdmRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=HdmRepository::class)
 */
class Hdm
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $name = '';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imgName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $alt;

    /**
     * @ORM\Column(type="smallint")
     */
    private $view = 1;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="hdm")
     */
    private $users;

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
        return $this->imgName;
    }

    public function setImgName(?string $imgName): self
    {
        $this->imgName = $imgName;

        return $this;
    }

    public function getImgPath($index = null): ?string
    {
        if (null !== $index && '' !== $index) {
            if (file_exists('uploads/hdms/'.$index)) {
                return 'uploads/hdms/'.$index;
            } else if (file_exists('build/images/hdms/'.$index)) {
                return 'build/images/hdms/'.$index;
            }
        }

        return 'build/images/utilities/pripravujeme-dark.png';
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(?string $alt): self
    {
        $this->alt = $alt;

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
            $user->setHdm($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getHdm() === $this) {
                $user->setHdm(null);
            }
        }

        return $this;
    }
}
