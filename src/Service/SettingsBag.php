<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Settings;

/**
 * Singleton
 */
class SettingsBag {

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var Settings
     */
    public mixed $data;

    /**
     * @var SettingsBag
     */
    private static SettingsBag $instance;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        $this->data = $this->em->getRepository(Settings::class)->find(1);
    }

    /**
     * @return SettingsBag
     */
    public function getInstance(): SettingsBag
    {
        if (self::$instance === null)
        {
            self::$instance = new SettingsBag($this->em);
        }

        return self::$instance;
    }
}