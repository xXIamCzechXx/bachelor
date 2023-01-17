<?php

namespace App\Controller;

use App\Entity\GalleryImages;
use App\Entity\GalleryCategories;
use App\Entity\Log;
use App\Entity\Pages;
use App\Service\UploadHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
            'title' => EditorController::PAGE_TITLE,
            'pages' => $pages,
            'pagesNameLength' => 100,
            'pagesDescriptionLength' => 120,
        ]);
    }

    /**
     * @Route("/editor-edit/{id}/page", name="editor_edit_page", methods="POST")
     */
    public function modifyPage(Pages $page, Request $request, UploadHelper $uploadHelper)
    {
        $logger = new Log();
        $data = $request->request;
        $logger->setAction($data->get('page-action'));

        switch ($data->get('page-action')) {
            case 'edit':
                if ($this->isGranted(SUPER_ADMIN, EDITOR)) {
                    /** @var UploadedFile $uploadedFile */
                    $uploadedFile = $request->files->get('page-image');

                    if ($uploadedFile) {
                        $newFileName = $uploadHelper->uploadImage($uploadedFile, 'logos', $page->getImgName());
                        $page->setImgName($newFileName);
                    }
                    $page
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
                }
                $this->addFlash(FLASH_DANGER, NO_RIGHTS);
                $logger->setType(LOGGER_TYPE_FAILED);
                break;

            default:
                $logger->setOperation(UNEXPECTED_ERROR);
                $this->addFlash(FLASH_DANGER, UNEXPECTED_ERROR_FLASH);
                $logger->setType(LOGGER_TYPE_FAILED);
                break;
        }

        if(empty($logger->getOperation())) {
            $logger->setOperation($page->getName()." [ ".$page->getId()." ] ");
        }

        $logger
            ->setModule($this->getModuleName(MODULE_PAGES))
            ->setUser($this->getUser())
            ->setUserName($this->getUser()->getFirstName());

        $this->em->persist($logger);
        $this->em->flush();

        return $this->redirectToRoute('editor_pages');
    }
}
