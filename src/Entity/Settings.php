<?php

namespace App\Entity;

use App\Repository\SettingsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SettingsRepository::class)
 */
class Settings
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $recaptchaSecretKey;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $recaptchaSiteKey;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $instagramFeedToken;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecaptchaSecretKey(): ?string
    {
        return $this->recaptchaSecretKey;
    }

    public function setRecaptchaSecretKey(?string $recaptchaSecretKey): self
    {
        $this->recaptchaSecretKey = $recaptchaSecretKey;

        return $this;
    }

    public function getRecaptchaSiteKey(): ?string
    {
        return $this->recaptchaSiteKey;
    }

    public function setRecaptchaSiteKey(?string $recaptchaSiteKey): self
    {
        $this->recaptchaSiteKey = $recaptchaSiteKey;

        return $this;
    }

    public function getInstagramFeedToken(): ?string
    {
        return $this->instagramFeedToken;
    }

    public function setInstagramFeedToken(?string $instagramFeedToken): self
    {
        $this->instagramFeedToken = $instagramFeedToken;

        return $this;
    }
}
