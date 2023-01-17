<?php

namespace App\Entity;

use App\Repository\GalleryImagesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=GalleryImagesRepository::class)
 */
class GalleryImages
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
    private $imgName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $alt;

    /**
     * @ORM\ManyToMany(targetEntity=GalleryCategories::class, inversedBy="galleryImages")
     */
    private $GalleryCategories;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $view = 1;

    public function __construct()
    {
        $this->GalleryCategories = new ArrayCollection();
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

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(?string $alt): self
    {
        $this->alt = $alt;

        return $this;
    }

    public function getImgPath($index = null): ?string
    {
        if (null !== $index && '' !== $index) {
            return 'uploads/gallery/'.$index;
        }

        return 'build/images/utilities/pripravujeme-dark.png';
    }

    /**
     * @return Collection|GalleryCategories[]
     */
    public function getGalleryCategories(): Collection
    {
        return $this->GalleryCategories;
    }

    public function addGalleryCategory(GalleryCategories $galleryCategory): self
    {
        if (!$this->GalleryCategories->contains($galleryCategory)) {
            $this->GalleryCategories[] = $galleryCategory;
        }

        return $this;
    }

    public function removeGalleryCategory(GalleryCategories $galleryCategory): self
    {
        $this->GalleryCategories->removeElement($galleryCategory);

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
