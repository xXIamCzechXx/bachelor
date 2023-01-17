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
      'title' => EditorController::PAGE_TITLE,
      'gallery' => $gallery,
      'categories' => $categories,
      'pagesNameLength' => 100,
      'pagesDescriptionLength' => 120,
    ]);
  }

  /**
   * @Route("/editor-edit/{id}/image", name="editor_edit_image", methods="POST")
   */
  public function modifyImage(GalleryImages $galleryImage, Request $request, UploadHelper $uploadHelper)
  {
    $logger = new Log();
    $data = $request->request;
      $logger->setOperation($data->get('img-action'));

    switch ($data->get('img-action')) {
        case 'edit':
            if ($this->isGranted(SUPER_ADMIN, EDITOR)) {

                // TODO:: Change to remove only categories, that arent choosen by user and then dont add'em
                $allCategories = $this->em->getRepository(GalleryCategories::class)->findAll();
                foreach ($allCategories as $categoryRow) {
                    $galleryImage->removeGalleryCategory($categoryRow);
                }

                // Add categories choosen by user
                if ($data->get('category-action')) {
                    foreach ($data->get('category-action') as $category) {
                        if ($category != '0') {
                            $categoryEntity = $this->em->getRepository(GalleryCategories::class)->findOneBy(['id' => $category]);
                            $galleryImage->addGalleryCategory($categoryEntity);
                        }
                    }
                }

                /** @var UploadedFile $uploadedFile */
                $uploadedFile = $request->files->get('image');

                if ($uploadedFile) {
                    $newFileName = $uploadHelper->uploadImage($uploadedFile, 'gallery', $galleryImage->getImgName());
                    $galleryImage->setImgName($newFileName);
                    $galleryImage->setName(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME));
                } else {
                    $galleryImage->setName($data->get('name'));
                }
                if (empty($data->get('img-alt'))) {
                    $galleryImage->setAlt($data->get('name'));
                } else {
                    $galleryImage->setAlt($data->get('img-alt'));
                }

                $galleryImage->setView($data->get('img-view'));

                $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste aktualizovali obrázek');
                break;
            }

            $this->addFlash(FLASH_DANGER, NO_RIGHTS);
            $logger
                ->setOperation(NO_RIGHTS)
                ->setType(LOGGER_TYPE_FAILED);
            break;

        case 'hide':
            if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
                $galleryImage->hide();
                $this->addFlash(FLASH_WARNING, 'Skrili jste obrázek');
                break;
            }
            $this->addFlash(FLASH_DANGER, NO_RIGHTS);
            $logger
                    ->setOperation(NO_RIGHTS)
                    ->setType(LOGGER_TYPE_FAILED);
            break;

        case 'show':
            if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
                $galleryImage->show();
                $this->addFlash(FLASH_SUCCESS, 'Zviditelnili jste obrázek');
                break;
            }
            $this->addFlash(FLASH_DANGER, NO_RIGHTS);
            $logger
                    ->setOperation(NO_RIGHTS)
                    ->setType(LOGGER_TYPE_FAILED);
            break;

      case 'remove':
          if ($this->isGranted(SUPER_ADMIN, EDITOR)) {
              $this->em->remove($galleryImage);
              $this->addFlash(FLASH_WARNING, 'Odstranili jste obrázek');
              break;
          }
          $this->addFlash(FLASH_DANGER, NO_RIGHTS);
          $logger
              ->setOperation(NO_RIGHTS)
              ->setType(LOGGER_TYPE_FAILED);
          break;

      default:
          $logger->setOperation(UNEXPECTED_ERROR);
          $this->addFlash(FLASH_DANGER, UNEXPECTED_ERROR_FLASH);
          $logger->setType(LOGGER_TYPE_FAILED);
          break;
    }

    if(empty($logger->getOperation())) {
        $logger->setOperation($galleryImage->getName()." [ ".$galleryImage->getId()." ] ");
    }
    if (empty($logger->getType())) {
        $logger->setType(LOGGER_TYPE_SUCCESS);
    }

    $logger
      ->setModule($this->getModuleName(MODULE_GALLERY))
      ->setUser($this->getUser())
      ->setUserName($this->getUser()->getFirstName());

    $this->em->persist($logger);
    $this->em->flush();

    return $this->redirectToRoute('editor_page_gallery');
  }

  /**
   * @Route("/editor-add/image", name="editor_add_image", methods="POST")
   */
  public function addImage(Request $request, UploadHelper $uploadHelper)
  {
    $logger = new Log();
    $galleryImage = new GalleryImages();
    $data = $request->request;
    $logger->setAction($data->get('img-action'));

    switch ($data->get('img-action')) {
      case 'add':
        if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(ADMIN) || $this->isGranted(EDITOR)) {
            // Fills categories for photos
            if ($data->get('category-add-action')) {
                foreach ($data->get('category-add-action') as $category) {
                    if ($category != '0') {
                        $categoryEntity = $this->em->getRepository(GalleryCategories::class)->findOneBy(['id' => $category]);
                        $galleryImage->addGalleryCategory($categoryEntity);
                    }
                }
            }

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $request->files->get('new-image');

            if ($uploadedFile) {
                $newFileName = $uploadHelper->uploadImage($uploadedFile, 'gallery');
                $galleryImage->setImgName($newFileName);
                $galleryImage->setName(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME));
            } else {
                $galleryImage->setName($data->get('name') ? $data->get('name') : '');
            }

            if (empty($data->get('img-alt'))) {
                $galleryImage->setAlt($data->get('name'));
            } else {
                $galleryImage->setAlt($data->get('img-alt'));
            }

            $galleryImage->setView($data->get('img-view'));

            $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste přidali obrázek');
            break;
        }
          $this->addFlash(FLASH_DANGER, NO_RIGHTS);
          $logger
              ->setOperation(NO_RIGHTS)
              ->setType(LOGGER_TYPE_FAILED);
          break;

      default:
          $this->addFlash(FLASH_DANGER, UNEXPECTED_ERROR_FLASH);
          $logger
              ->setOperation(UNEXPECTED_ERROR)
              ->setType(LOGGER_TYPE_FAILED);
          break;
    }
    if(empty($logger->getOperation())) {
        $logger->setOperation($galleryImage->getName()." [ ".$galleryImage->getId()." ] ");
    }
    if (empty($logger->getType())) {
        $logger->setType(LOGGER_TYPE_SUCCESS);
    }

    $logger
      ->setModule($this->getModuleName(MODULE_GALLERY))
      ->setUser($this->getUser())
      ->setUserName($this->getUser()->getFirstName());

    $this->em->persist($logger);
    if ($galleryImage) {
      $this->em->persist($galleryImage);
    }
    $this->em->flush();
    return $this->redirectToRoute('editor_page_gallery');
  }

  /**
   * @Route("/editor-gallery/categories", name="editor_gallery_categories")
   */
  public function galleryCategories()
  {
    $galleryCategories = $this->em->getRepository(GalleryCategories::class)->findAll();

    return $this->render('editor/editor_gallery/gallery_categories.html.twig', [
      'title' => EditorController::PAGE_TITLE,
      'galleryCategories' => $galleryCategories,
    ]);
  }

  /**
   * @Route("/editor-add/gallery-category", name="editor_add_gallery_category", methods="POST")
   */
  public function addGalleryCategory(Request $request, UploadHelper $uploadHelper)
  {
    $logger = new Log();
    $galleryCategory = new GalleryCategories();
    $data = $request->request;
    $logger->setAction($data->get('category-action'));

    $slug = $data->get('category-slug');
    $title = $data->get('category-title');
    $heading = $data->get('category-heading');

    switch ($data->get('category-action')) {
      case 'add':
          if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
              if (!empty($data->get('category-name'))) {
                  if (empty($slug)) {
                      $slug = Urlizer::urlize($data->get('category-name'));
                  }
                  if (empty($title)) {
                      $title = $data->get('category-name');
                  }
                  if (empty($heading)) {
                      $heading = $data->get('category-name');
                  }
                  $galleryCategory
                      ->setColor($data->get('category-color'))
                      ->setName($data->get('category-name'))
                      ->setSlug($this->getValidSlug($slug))
                      ->setTitle($title)
                      ->setHeading($heading)
                      ->setMetaDescription($data->get('category-description'))
                      ->setKeywords($data->get('category-keywords'));

                  $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste přidali kategorii galerie');
                  break;
              }

              $galleryCategory = null;
              $this->addFlash(
                  FLASH_DANGER,
                  'Není vyplněno políčko Název kategorie, prosím založte kategorii znovu se správnými hodnotami'
              );
              $logger
                  ->setType(LOGGER_TYPE_FAILED)
                  ->setOperation("Name of category was empty, name gotta be filled before adding category");
              break;
          }
          $this->addFlash(FLASH_DANGER, NO_RIGHTS);
          $logger
              ->setOperation(NO_RIGHTS)
              ->setType(LOGGER_TYPE_FAILED);
          break;

      default:
          $this->addFlash(FLASH_DANGER, UNEXPECTED_ERROR_FLASH);
          $logger
              ->setOperation(UNEXPECTED_ERROR)
              ->setType(LOGGER_TYPE_FAILED);
          break;
    }

    if(empty($logger->getOperation())) {
        $logger->setOperation($galleryCategory->getName());
    }
    if (empty($logger->getType())) {
         $logger->setType(LOGGER_TYPE_SUCCESS);
    }

    $logger
      ->setModule($this->getModuleName(MODULE_GALLERY_CATEGORIES))
      ->setUser($this->getUser())
      ->setUserName($this->getUser()->getFirstName());

    $this->em->persist($logger);
    if ($galleryCategory) {
      $this->em->persist($galleryCategory);
    }
    $this->em->flush();

    return $this->redirectToRoute('editor_gallery_categories');
  }

  /**
   * @Route("/editor-edit/{id}/gallery-category", name="editor_edit_gallery_category", methods="POST")
   */
  public function modifyGalleryCategory(GalleryCategories $galleryCategory, Request $request)
  {
    $logger = new Log();
    $data = $request->request;
    $logger->setAction($data->get('category-action'));

    $slug = $data->get('category-slug');
    $title = $data->get('category-title');
    $heading = $data->get('category-heading');

    switch ($data->get('category-action')) {
      case 'edit':
        if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
            if (!empty($data->get('category-name'))) {
                if (empty($slug)) {
                    $slug = Urlizer::urlize($data->get('category-name'));
                }
                if (empty($title)) {
                    $title = $data->get('category-name');
                }
                if (empty($heading)) {
                    $heading = $data->get('category-name');
                }
                $galleryCategory
                    ->setColor($data->get('category-color'))
                    ->setName($data->get('category-name'))
                    ->setSlug($this->getValidSlug($slug))
                    ->setTitle($title)
                    ->setHeading($heading)
                    ->setMetaDescription($data->get('category-description'))
                    ->setKeywords($data->get('category-keywords'));

                $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste aktualizovali kategorii galerie');
                break;
            }
            $this->addFlash(FLASH_DANGER, 'Není vyplněno políčko Název kategorie, prosím upravte kategorii znovu se správnými hodnotami');
            $logger
                ->setType(LOGGER_TYPE_FAILED)
                ->setOperation("Name of category was empty, name gotta be filled before editing category");
            break;
        }
          $this->addFlash(FLASH_DANGER, NO_RIGHTS);
          $logger
              ->setOperation(NO_RIGHTS)
              ->setType(LOGGER_TYPE_FAILED);
          break;

        case 'hide':
            if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
                $galleryCategory->hide();
                $this->addFlash(FLASH_WARNING, 'Skrili jste kategorii galerie');
                break;
            }
            $this->addFlash(FLASH_DANGER, NO_RIGHTS);
            $logger
                    ->setOperation(NO_RIGHTS)
                    ->setType(LOGGER_TYPE_FAILED);
            break;

        case 'show':
            if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
                $galleryCategory->show();
                $this->addFlash(FLASH_SUCCESS, 'Zviditelnili jste kategorii galerie');
                break;
            }
            $this->addFlash(FLASH_DANGER, NO_RIGHTS);
            $logger
                    ->setOperation(NO_RIGHTS)
                    ->setType(LOGGER_TYPE_FAILED);
            break;

      case 'remove':
          if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
              $this->em->remove($galleryCategory);
              $this->addFlash(FLASH_WARNING, 'Odstranili jste kategorii');
              break;
          }
          $this->addFlash(FLASH_DANGER, NO_RIGHTS);
          $logger
              ->setOperation(NO_RIGHTS)
              ->setType(LOGGER_TYPE_FAILED);
          break;

      default:
          $this->addFlash(FLASH_DANGER, UNEXPECTED_ERROR_FLASH);
          $logger
              ->setOperation(UNEXPECTED_ERROR)
              ->setType(LOGGER_TYPE_FAILED);
          break;
      }

      if(empty($logger->getOperation())) {
          $logger->setOperation($galleryCategory->getName()." [ ".$galleryCategory->getId()." ] ");
      }
      if (empty($logger->getType())) {
          $logger->setType(LOGGER_TYPE_SUCCESS);
      }

      $logger
      ->setModule($this->getModuleName(MODULE_GALLERY_CATEGORIES))
      ->setUser($this->getUser())
      ->setUserName($this->getUser()->getFirstName());

    $this->em->persist($logger);
    $this->em->flush();

    return $this->redirectToRoute('editor_gallery_categories');
  }
}
