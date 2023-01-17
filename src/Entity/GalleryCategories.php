<?php

namespace App\Entity;

use App\Repository\GalleryCategoriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=GalleryCategoriesRepository::class)
 */
class GalleryCategories
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
     * @ORM\Column(type="string", length=128)
     * @Gedmo\Slug(fields={"name"})
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $metaDescription;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $keywords;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $heading;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $color;

    /**
     * @ORM\ManyToMany(targetEntity=GalleryImages::class, mappedBy="GalleryCategories")
     */
    private $galleryImages;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $view = 0;

    public function __construct()
    {
        $this->galleryImages = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function setKeywords(?string $keywords): self
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function getHeading(): ?string
    {
        return $this->heading;
    }

    public function setHeading(?string $heading): self
    {
        $this->heading = $heading;

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
     * @return Collection|GalleryImages[]
     */
    public function getGalleryImages(): Collection
    {
        return $this->galleryImages;
    }

    public function addGalleryImage(GalleryImages $galleryImage): self
    {
        if (!$this->galleryImages->contains($galleryImage)) {
            $this->galleryImages[] = $galleryImage;
            $galleryImage->addGalleryCategory($this);
        }

        return $this;
    }

    public function removeGalleryImage(GalleryImages $galleryImage): self
    {
        if ($this->galleryImages->removeElement($galleryImage)) {
            $galleryImage->removeGalleryCategory($this);
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
