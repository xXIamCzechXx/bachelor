<?php

namespace App\Controller;

use App\Entity\GalleryImages;
use App\Entity\GalleryCategories;
use App\Entity\Log;
use App\Entity\Pages;
use App\Service\UploadHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EditorPagesController extends BaseEditorController
{
    use Traits\EditorErrorHandlers;

    /**
     * @Route("/editor-pages", name="editor_pages")
     */
    public function index()
    {
        $pages = $this->em->getRepository(Pages::class)->findAll();

        return $this->render('editor/editor_pages/pages.html.twig', [
            'pages' => $pages,
        ]);
    }

    /**
     * @Route("/editor-edit/{id}/page", name="editor_edit_page", methods="POST")
     */
    public function editPage(Pages $page, Request $request, UploadHelper $uploadHelper)
    {
        if ($this->isGranted(self::SUPER_ADMIN) || $this->isGranted(self::EDITOR)) {
            $data = $request->request;
            $this->processRequest($page, $data, $data->get('page-action'), $request->files, $uploadHelper);
        } else {
            $this->addFlash(FLASH_DANGER, NO_RIGHTS);
        }

        return $this->redirectToRoute('editor_pages');
    }

    /**
     * @param $entity
     * @param InputBag $data
     * @param string $action
     */
    protected function processRequest($entity, InputBag $data, String $action = 'undefined', $files = null, $uploadHelper = null)
    {
        $logger = new Log();
        $logger->setAction($action);

        switch ($action) {
            case 'edit':
                /** @var UploadedFile $uploadedFile */
                $uploadedFile = $files->get('page-image');

                if ($uploadedFile) {
                    $newFileName = $uploadHelper->uploadImage($uploadedFile, 'logos', $entity->getImgName());
                    $entity->setImgName($newFileName);
                }
                $entity
                    //->setName($data->get('page-name'))
                    //->setUrl($data->get('page-url'))
                    ->setTitle($data->get('page-title'))
                    ->setHeading($data->get('page-heading'))
                    ->setAlt($data->get('page-alt'))
                    ->setInstagramToken($data->get('page-instagram'))
                    ->setMetaDescription($data->get('page-description'))
                    ->setKeywords($data->get('page-keywords'))
                ;
                $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste aktualizovali stránku');
                $logger->setType(LOGGER_TYPE_SUCCESS);
                break;

            default:
                $this->addFlash(FLASH_DANGER, UNEXPECTED_ERROR_FLASH);
                $logger
                    ->setOperation(UNEXPECTED_ERROR)
                    ->setType(LOGGER_TYPE_FAILED);
                break;
        }

        $logger = $this->completeLogger($logger, MODULE_PAGES, $entity->getName() ." [ ".$entity->getId()." ] ");

        $this->em->persist($logger);
        $this->em->flush();
    }
}
