<?php

namespace App\Entity;

use App\Repository\NewsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=NewsRepository::class)
 */
class News
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
    private $title;

    /**
     * @ORM\Column(type="string", length=128, unique=true)
     * @Gedmo\Slug(fields={"title"})
     */
    private $slug;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $view = 0;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="news")
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $metaDescription;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $keywords;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $instagramToken;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $heading;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $parentPage = "Články";

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $img_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $alt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true, options={"default": "CURRENT_TIMESTAMP"})
     */
    private $addedAt;

    /**
     * @ORM\ManyToMany(targetEntity=NewsCategories::class, inversedBy="news")
     */
    private $newsCategories;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $notation;

    public function __construct()
    {
        $this->newsCategories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

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

    public function getInstagramToken(): ?string
    {
        return $this->instagramToken;
    }

    public function setInstagramToken(?string $instagramToken): self
    {
        $this->instagramToken = $instagramToken;

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

    public function getParentPage(): ?string
    {
        return $this->parentPage;
    }

    public function setParentPage(?string $parentPage): self
    {
        $this->parentPage = $parentPage;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getView(): ?int
    {
        return $this->view;
    }

    public function setView($view): self
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

    public function getImgName(): ?string
    {
        return $this->img_name;
    }

    public function setImgName(string $img_name): self
    {
        $this->img_name = $img_name;

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
            if (file_exists('uploads/news/'.$index)) {
                return 'uploads/news/'.$index;
            } else if (file_exists('build/images/news/'.$index)) {
                return 'build/images/news/'.$index;
            }
        }

        return 'build/images/utilities/pripravujeme-dark.png';
    }

    public function getAddedAt(): ?\DateTimeImmutable
    {
        return $this->addedAt;
    }

    public function setAddedAt(?\DateTimeImmutable $addedAt): self
    {
        $this->addedAt = $addedAt;

        return $this;
    }

    /**
     * @return Collection|NewsCategories[]
     */
    public function getNewsCategories(): Collection
    {
        return $this->newsCategories;
    }

    public function addNewsCategory(NewsCategories $newsCategory): self
    {
        if (!$this->newsCategories->contains($newsCategory)) {
            $this->newsCategories[] = $newsCategory;
        }

        return $this;
    }

    public function removeNewsCategory(NewsCategories $newsCategory): self
    {
        $this->newsCategories->removeElement($newsCategory);

        return $this;
    }

    public function getNotation(): ?string
    {
        return $this->notation;
    }

    public function setNotation(?string $notation): self
    {
        $this->notation = $notation;

        return $this;
    }
}
