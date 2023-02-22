<?php

namespace App\Entity;

use App\Repository\MethodRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=MethodRepository::class)
 */
class Method
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $alias;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $slug;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="text")
     */
    private string $log;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAlias(): string {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias(string $alias): void {
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getLog(): string {
        return $this->log;
    }

    /**
     * @param string $log
     */
    public function setLog(string $log): void {
        $this->log = $log;
    }

    /**
     * @return string
     */
    public function getSlug(): string {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug): void {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void {
        $this->type = $type;
    }
}
