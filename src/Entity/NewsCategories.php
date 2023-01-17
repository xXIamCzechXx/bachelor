<?php

namespace App\Entity;

use App\Repository\NewsCategoriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=NewsCategoriesRepository::class)
 */
class NewsCategories
{
  use TimestampableEntity;
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
   * @ORM\Column(type="text", nullable=true)
   */
  private $description;

  /**
   * @ORM\Column(type="string", length=64, nullable=true)
   */
  private $color;

  /**
   * @ORM\ManyToMany(targetEntity=News::class, mappedBy="newsCategories")
   */
  private $news;

  /**
   * @ORM\Column(type="smallint", options={"default": 0})
   */
  private $view = 0;

  public function __construct()
  {
      $this->news = new ArrayCollection();
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

  public function getDescription(): ?string
  {
    return $this->description;
  }

  public function setDescription(?string $description): self
  {
    $this->description = $description;

    return $this;
  }

  public function getColor(): ?string
  {
    return $this->color;
  }

  public function setColor(?string $color): self
  {
    $this->color = $color;

    return $this;
  }

  /**
   * @return Collection|News[]
   */
  public function getNews(): Collection
  {
      return $this->news;
  }

  public function addNews(News $news): self
  {
      if (!$this->news->contains($news)) {
          $this->news[] = $news;
          $news->addNewsCategory($this);
      }

      return $this;
  }

  public function removeNews(News $news): self
  {
      if ($this->news->removeElement($news)) {
          $news->removeNewsCategory($this);
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
