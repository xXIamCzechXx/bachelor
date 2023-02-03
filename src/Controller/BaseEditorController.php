<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\News;
use App\Entity\Pages;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class BaseEditorController extends SharedController
{
    use Traits\EditorErrorHandlers;
    use Traits\EditorConfiguration;

    protected function getValidSlug($slug)
    {
        if (!empty($slug) && null !== $slug) {
            $pages = $this->em->getRepository(Pages::class)->findOneBy(['url' => $slug]);
            $news = $this->em->getRepository(News::class)->findOneBy(['slug' => $slug]);
            if (empty($pages) && empty($news)) {
                return $slug;
            }
        }

        return $this->getValidSlug($slug."-1");
    }

    /**
     * @param Log $logger
     * @param string $module
     * @return Log
     */
    protected function completeLogger(Log $logger, string $module = 'Undefined', string $operation = 'Undefined'): Log {
        $logger
            ->setModule($this->getModuleName($module))
            ->setUser($this->getUser())
            ->setUserName($this->getUser()->getFirstName());

        if(empty($logger->getOperation()) || $logger->getOperation() === 'Undefined')
            $logger->setOperation($operation);

        return $logger;
    }
}
