<?php

namespace App\Service;

use App\Entity\Constants;
use Doctrine\ORM\EntityManagerInterface;

class Translator {

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    /**
     * @param string $name
     * @param string $default
     * @return string
     */
    public function getValue($name, $default = ""): ?string
    {
        if (!empty($name)) {
            $constant = $this->em->getRepository(Constants::class)->findOneBy(["name" => $name]);
            return null !== $constant ? $constant->getValue() : $default;
        }

        return $default;
    }
    /**
     * @return array|null
     */
    protected function getConstants(): ?array
    {
        $constantsMapped = [];

        if ($constants = $this->em->getRepository(Constants::class)->findAll()) {
            foreach ($constants as $constant) {
                $constantsMapped[$constant->getName()] = $constant->getValue();
            }
        }
        return $constantsMapped;
    }

    /**
     * @param null $name
     * @return string|null
     */
    protected function getConstant($name = null): ?string
    {
        if ($constant = $this->em->getRepository(Constants::class)->findOneBy(['name' => $name])) {
            return $constant->getValue();
        }
        return '';
    }
}