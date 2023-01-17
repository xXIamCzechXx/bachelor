<?php

namespace App\Entity;

use App\Repository\AnswerTypesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=AnswerTypesRepository::class)
 */
class AnswerTypes
{
    use TimestampableEntity;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=FormAnswers::class, mappedBy="answerTypes")
     */
    private $id_answer;

    public function __construct()
    {
        $this->id_answer = new ArrayCollection();
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
     * @return Collection|FormAnswers[]
     */
    public function getIdAnswer(): Collection
    {
        return $this->id_answer;
    }

    public function addIdAnswer(FormAnswers $idAnswer): self
    {
        if (!$this->id_answer->contains($idAnswer)) {
            $this->id_answer[] = $idAnswer;
            $idAnswer->setAnswerTypesId($this);
        }

        return $this;
    }

    public function removeIdAnswer(FormAnswers $idAnswer): self
    {
        if ($this->id_answer->removeElement($idAnswer)) {
            // set the owning side to null (unless already changed)
            if ($idAnswer->getAnswerTypesId() === $this) {
                $idAnswer->setAnswerTypesId(null);
            }
        }

        return $this;
    }

    public function __toString() {

        return $this->getName();

    }


}
