<?php

namespace App\Entity;

use App\Repository\CountriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CountriesRepository::class)
 */
class Countries
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="country")
     */
    private $users;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $img_name;

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
            $user->setCountry($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCountry() === $this) {
                $user->setCountry(null);
            }
        }

        return $this;
    }

    public function __toString(){
        return $this->getName();
    }

    public function getImgName(): ?string
    {
        return $this->img_name;
    }

    public function setImgName(?string $img_name): self
    {
        $this->img_name = $img_name;

        return $this;
    }

    public function getImgPath($index = null): ?string
    {
        if (null !== $index && '' !== $index) {
            if (file_exists('uploads/countries/'.$index)) {
                return 'uploads/countries/'.$index;
            } else if (file_exists('build/images/countries/'.$index)) {
                return 'build/images/countries/'.$index;
            } else if (file_exists('build/images/countries/empty.jpg')) {
                return 'build/images/countries/empty.jpg';
            }
        }

        return 'build/images/utilities/pripravujeme-dark.png';
    }
}
