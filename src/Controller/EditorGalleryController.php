<?php

namespace App\Controller;

use App\Entity\GalleryCategories;
use App\Entity\GalleryImages;
use App\Entity\Log;
use App\Service\UploadHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EditorGalleryController extends BaseEditorController
{
  /**
   * @Route("/editor-page/gallery", name="editor_page_gallery")
   */
  public function gallery()
  {
    $gallery = $this->em->getRepository(GalleryImages::class)->findAll();
    $categories = $this->em->getRepository(GalleryCategories::class)->findAll();

    return $this->render('editor/editor_gallery/gallery.html.twig', [
      'gallery' => $gallery,
      'categories' => $categories,
      'pagesNameLength' => 100,
      'pagesDescriptionLength' => 120,
    ]);
  }

  /**
   * @Route("/editor-edit/{id}/image", name="editor_edit_image", methods="POST")
   */
  public function editImage(GalleryImages $galleryImage, Request $request, UploadHelper $uploadHelper)
  {
      if ($this->isGranted(self::SUPER_ADMIN) || $this->isGranted(self::EDITOR)) {
          $data = $request->request;
          $this->processRequest($galleryImage, $data, $data->get('img-action'), $request->files, $uploadHelper);
      } else {
          $this->addFlash(FLASH_DANGER, NO_RIGHTS);
      }

      return $this->redirectToRoute('editor_page_gallery');
  }

  /**
   * @Route("/editor-add/image", name="editor_add_image", methods="POST")
   */
  public function addImage(Request $request, UploadHelper $uploadHelper)
  {
      if ($this->isGranted(self::SUPER_ADMIN) || $this->isGranted(self::EDITOR)) {
          $this->processRequest(new GalleryImages(), $request->request, $request->request->get('img-action'), $request->files, $uploadHelper);
      } else {
          $this->addFlash(FLASH_DANGER, NO_RIGHTS);
      }

      return $this->redirectToRoute('editor_page_gallery');
  }

  /**
   * @Route("/editor-gallery/categories", name="editor_gallery_categories")
   */
  public function galleryCategories()
  {
    $galleryCategories = $this->em->getRepository(GalleryCategories::class)->findAll();

    return $this->render('editor/editor_gallery/gallery_categories.html.twig', [
      'galleryCategories' => $galleryCategories,
    ]);
  }

  /**
   * @Route("/editor-add/gallery-category", name="editor_add_gallery_category", methods="POST")
   */
  public function addGalleryCategory(Request $request, UploadHelper $uploadHelper)
  {
      if ($this->isGranted(self::SUPER_ADMIN) || $this->isGranted(self::EDITOR)) {
          $this->processRequest(new GalleryCategories(), $request->request, $request->request->get('category-action'));
      } else {
          $this->addFlash(FLASH_DANGER, NO_RIGHTS);
      }

    return $this->redirectToRoute('editor_gallery_categories');
  }

  /**
   * @Route("/editor-edit/{id}/gallery-category", name="editor_edit_gallery_category", methods="POST")
   */
  public function editGalleryCategory(GalleryCategories $galleryCategory, Request $request)
  {
      if ($this->isGranted(self::SUPER_ADMIN) || $this->isGranted(self::EDITOR)) {
          $data = $request->request;
          $this->processRequest($galleryCategory, $data, $data->get('category-action'));
      } else {
          $this->addFlash(FLASH_DANGER, NO_RIGHTS);
      }

      return $this->redirectToRoute('editor_gallery_categories');
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
            case 'add':
                if ($entity instanceof GalleryCategories) {
                    if (!empty($data->get('category-name'))) {
                        $slug = !empty($data->get('category-slug')) ?: Urlizer::urlize($data->get('category-name'));
                        $title = !empty($data->get('category-title')) ?: $data->get('category-name');
                        $heading = !empty($data->get('category-heading')) ?: $data->get('category-name');

                        $entity
                            ->setColor($data->get('category-color'))
                            ->setName($data->get('category-name'))
                            ->setSlug($this->getValidSlug($slug))
                            ->setTitle($title)
                            ->setHeading($heading)
                            ->setMetaDescription($data->get('category-description'))
                            ->setKeywords($data->get('category-keywords'));

                        $this->em->persist($entity);
                        $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste přidali kategorii galerie');
                        break;
                    }

                    $this->addFlash(FLASH_DANGER, 'Není vyplněno políčko Název kategorie, prosím založte kategorii znovu se správnými hodnotami');
                    $logger
                        ->setType(LOGGER_TYPE_FAILED)
                        ->setOperation("Name of category was empty, name gotta be filled before adding category");
                    break;

                } else if ($entity instanceof GalleryImages) {

                    // Fills categories for photos
                    if ($data->get('category-add-action')) {
                        foreach ($data->get('category-add-action') as $category) {
                            if ($category != '0') {
                                $categoryEntity = $this->em->getRepository(GalleryCategories::class)->findOneBy(['id' => $category]);
                                $entity->addGalleryCategory($categoryEntity);
                            }
                        }
                    }

                    /** @var UploadedFile $uploadedFile */
                    if ($uploadedFile = $files->get('new-image')) {
                        $newFileName = $uploadHelper->uploadImage($uploadedFile, 'gallery');
                        $entity->setImgName($newFileName);
                        $entity->setName(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME));
                    } else {
                        $entity->setName($data->get('name') ? $data->get('name') : '');
                    }

                    $entity
                        ->setAlt(empty($data->get('img-alt')) ? $data->get('name') : $data->get('img-alt'))
                        ->setView($data->get('img-view'));

                    $this->em->persist($entity);
                    $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste přidali obrázek');
                    break;

                } else {
                    $this->addFlash(FLASH_DANGER, UNEXPECTED_ERROR_FLASH);
                    $logger
                        ->setOperation(UNEXPECTED_ERROR)
                        ->setType(LOGGER_TYPE_FAILED);
                    break;
                }

            case 'edit':
                if ($entity instanceof GalleryCategories) {

                    if (!empty($data->get('category-name'))) {
                        $slug = !empty($data->get('category-slug')) ?: Urlizer::urlize($data->get('category-name'));
                        $title = !empty($data->get('category-title')) ?: $data->get('category-name');
                        $heading = !empty($data->get('category-heading')) ?: $data->get('category-name');
                        $entity
                            ->setColor($data->get('category-color'))
                            ->setName($data->get('category-name'))
                            ->setSlug($this->getValidSlug($slug))
                            ->setTitle($title)
                            ->setHeading($heading)
                            ->setMetaDescription($data->get('category-description'))
                            ->setKeywords($data->get('category-keywords'));

                        $this->em->persist($entity);
                        $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste aktualizovali kategorii galerie');
                        break;
                    }
                    $this->addFlash(FLASH_DANGER, 'Není vyplněno políčko Název kategorie, prosím upravte kategorii znovu se správnými hodnotami');
                    $logger
                        ->setType(LOGGER_TYPE_FAILED)
                        ->setOperation("Name of category was empty, name gotta be filled before editing category");
                    break;

                } else if ($entity instanceof GalleryImages) {

                    // TODO:: Change to remove only categories, that arent choosen by user and then dont add'em
                    $allCategories = $this->em->getRepository(GalleryCategories::class)->findAll();
                    foreach ($allCategories as $categoryRow) {
                        $entity->removeGalleryCategory($categoryRow);
                    }
                    // Add categories choosen by user
                    if ($data->get('category-action')) {
                        foreach ($data->get('category-action') as $category) {
                            if ($category != '0') {
                                $categoryEntity = $this->em->getRepository(GalleryCategories::class)->findOneBy(['id' => $category]);
                                $entity->addGalleryCategory($categoryEntity);
                            }
                        }
                    }

                    /** @var UploadedFile $uploadedFile */
                    $uploadedFile = $files->get('image');

                    if ($uploadedFile) {
                        $newFileName = $uploadHelper->uploadImage($uploadedFile, 'gallery', $entity->getImgName());
                        $entity->setImgName($newFileName);
                        $entity->setName(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME));
                    } else {
                        $entity->setName($data->get('name'));
                    }
                    $entity
                        ->setAlt(empty($data->get('img-alt')) ? $data->get('name') : $data->get('img-alt'))
                        ->setView($data->get('img-view'));

                    $this->em->persist($entity);
                    $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste aktualizovali záznam');
                    break;

                } else {
                    $this->addFlash(FLASH_DANGER, UNEXPECTED_ERROR_FLASH);
                    $logger
                        ->setOperation(UNEXPECTED_ERROR)
                        ->setType(LOGGER_TYPE_FAILED);
                    break;
                }

            case 'hide':
                if ($entity instanceof GalleryCategories) {

                    $logger->setOperation($entity->getName()." [ ".$entity->getId()." ] ");
                    $entity->hide();
                    $this->addFlash(FLASH_WARNING, 'Skrili jste záznam');
                    break;

                } else if ($entity instanceof GalleryImages) {

                    $logger->setOperation($entity->getName()." [ ".$entity->getId()." ] ");
                    $entity->hide();
                    $this->addFlash(FLASH_WARNING, 'Skrili jste obrázek');
                    break;

                } else {
                    $this->addFlash(FLASH_DANGER, UNEXPECTED_ERROR_FLASH);
                    $logger
                        ->setOperation(UNEXPECTED_ERROR)
                        ->setType(LOGGER_TYPE_FAILED);
                    break;
                }

            case 'show':
                if ($entity instanceof GalleryCategories) {

                    $logger->setOperation($entity->getName()." [ ".$entity->getId()." ] ");
                    $entity->show();
                    $this->addFlash(FLASH_SUCCESS, 'Zviditelnili jste záznam');
                    break;

                } else if ($entity instanceof GalleryImages) {

                    $logger->setOperation($entity->getName()." [ ".$entity->getId()." ] ");
                    $entity->show();
                    $this->addFlash(FLASH_SUCCESS, 'Zviditelnili jste záznam');
                    break;

                } else {
                    $this->addFlash(FLASH_DANGER, UNEXPECTED_ERROR_FLASH);
                    $logger
                        ->setOperation(UNEXPECTED_ERROR)
                        ->setType(LOGGER_TYPE_FAILED);
                    break;
                }

            case 'remove':
                if ($entity instanceof GalleryCategories) {

                    $logger->setOperation($entity->getName()." [ ".$entity->getId()." ] ");
                    $this->em->remove($entity);
                    $this->addFlash(FLASH_WARNING, 'Odstranili jste záznam');
                    break;

                } else if ($entity instanceof GalleryImages) {

                    $logger->setOperation($entity->getName()." [ ".$entity->getId()." ] ");
                    $this->em->remove($entity);
                    $this->addFlash(FLASH_WARNING, 'Odstranili jste záznam');
                    break;

                } else {
                    $this->addFlash(FLASH_DANGER, UNEXPECTED_ERROR_FLASH);
                    $logger
                        ->setOperation(UNEXPECTED_ERROR)
                        ->setType(LOGGER_TYPE_FAILED);
                    break;
                }

            default:
                $this->addFlash(FLASH_DANGER, UNEXPECTED_ERROR_FLASH);
                $logger
                    ->setOperation(UNEXPECTED_ERROR)
                    ->setType(LOGGER_TYPE_FAILED);
                break;
        }

        $logger = $this->completeLogger($logger, MODULE_GALLERY, $entity->getName() ." [ ".$entity->getId()." ] ");

        $this->em->persist($logger);
        $this->em->flush();
    }
}
