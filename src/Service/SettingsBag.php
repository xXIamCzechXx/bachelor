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
    private $em;

    /**
     * @var Settings
     */
    public $data;

    /**
     * @var SettingsBag
     */
    private static $instance = null;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        $this->data = $this->em->getRepository(Settings::class)->find(1);
    }

    public function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new Settings();
        }

        return self::$instance;
    }
}