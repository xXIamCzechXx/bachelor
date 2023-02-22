<?php

namespace App\Controller;

use App\Entity\GalleryCategories;
use App\Entity\Log;
use App\Entity\News;
use App\Entity\NewsCategories;
use App\Entity\User;
use App\Repository\NewsCategoriesRepository;
use App\Repository\NewsRepository;
use App\Repository\UserRepository;
use App\Service\UploadHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\String\b;

class EditorNewsController extends BaseEditorController
{
    use Traits\EditorErrorHandlers;

    /**
     * @Route("/editor-news", name="editor_news")
     */
    public function index()
    {
      $news = $this->em->getRepository(News::class)->findAllDesc();
      $admins = $this->em->getRepository(User::class)->findAdmins();
      $categories = $this->em->getRepository(NewsCategories::class)->findAll();

      return $this->render('editor/editor_news/news.html.twig', [
        'newsArray' => $news,
        'admins' => $admins,
        'categories' => $categories,
      ]);
    }

    /**
     * @Route("/editor-edit/{slug}/news", name="editor_edit_news", methods="POST")
     */
    public function editNews(News $news, Request $request, UploadHelper $uploadHelper)
    {
        if ($this->isGranted(self::SUPER_ADMIN) || $this->isGranted(self::EDITOR)) {
            $data = $request->request;
            $this->processRequest($news, $data, $data->get('news-action'), $request->files, $uploadHelper);
        } else {
            $this->addFlash(FLASH_DANGER, NO_RIGHTS);
        }

        return $this->redirectToRoute('editor_news');
    }

    /**
     * @Route("/editor-add/news", name="editor_add_news", methods="POST")
     */
    public function addNews(Request $request, UploadHelper $uploadHelper)
    {
        if ($this->isGranted(self::SUPER_ADMIN)) {
            $this->processRequest(new News(), $request->request, $request->request->get('news-action'), $request->files, $uploadHelper);
        } else {
            $this->addFlash(FLASH_DANGER, NO_RIGHTS);
        }

        return $this->redirectToRoute('editor_news');
    }

  /**
   * @Route("/editor-news/categories", name="editor_news_categories")
   */
  public function newsCategories(NewsCategoriesRepository $categoryRepository)
  {
    $categories = $categoryRepository->findAll();

    return $this->render('editor/editor_news/news_categories.html.twig', [
      'categories' => $categories,
    ]);
  }

  /**
   * @Route("/editor-add/news-category", name="editor_add_news_category", methods="POST")
   */
  public function addNewsCategory(Request $request)
  {
    if ($this->isGranted(self::SUPER_ADMIN) || $this->isGranted(self::EDITOR)) {
      $this->processRequest(new NewsCategories(), $request->request, $request->request->get('category-action'));
    } else {
      $this->addFlash(FLASH_DANGER, NO_RIGHTS);
    }

    return $this->redirectToRoute('editor_news_categories');
  }

  /**
   * @Route("/editor-edit/{id}/news-category", name="editor_edit_news_category", methods="POST")
   */
  public function editNewsCategory(NewsCategories $newsCategory, Request $request)
  {
    if ($this->isGranted(self::SUPER_ADMIN) || $this->isGranted(self::EDITOR)) {
      $data = $request->request;
      $this->processRequest($newsCategory, $data, $data->get('category-action'));
    } else {
      $this->addFlash(FLASH_DANGER, NO_RIGHTS);
    }

    return $this->redirectToRoute('editor_news_categories');
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
                $slug = $data->get('news-slug');
                $addedAt = new \DateTimeImmutable($data->get('news-addedAt'));
                if ($data->get('category-action')) {
                    foreach ($data->get('category-action') as $category) {
                        if ($category != '0') {
                            $categoryEntity = $this->em->getRepository(NewsCategories::class)->findOneBy(['id' => $category]);
                            $entity->addNewsCategory($categoryEntity);
                        }
                    }
                }
                /** @var UploadedFile $uploadedFile */
                $uploadedFile = $files->get('news-image');

                if ($uploadedFile) {
                    $newFileName = $uploadHelper->uploadImage($uploadedFile, 'news', $entity->getImgName());
                    $entity->setImgName($newFileName);
                }

                if (!$user = $this->em->getRepository(User::class)->findOneBy(
                    ['nickname' => $data->get('news-author')]
                )) {
                    $user = $this->getUser();
                }

                if (!empty($data->get('news-title'))) {
                    if (empty($slug)) {
                        $slug = Urlizer::urlize($data->get('news-title'));
                    }
                    if (!empty($addedAt)) {
                        $entity->setAddedAt($addedAt);
                    }
                    $entity
                        ->setAuthor($user)
                        ->setSlug($slug)
                        ->setTitle($data->get('news-title'))
                        ->setAlt($data->get('news-alt'))
                        ->setView($data->get('news-view'))
                        ->setNotation($data->get('news-notation'))
                        ->setContent($data->get('news-content'))
                        ->setKeywords($data->get('news-keywords'))
                        ->setMetaDescription($data->get('news-description'));

                    $this->em->persist($entity);
                    $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste přidali záznam');
                    break;
                }

                $this->addFlash(FLASH_DANGER, 'Není vyplněno políčko titulek(nadpis), prosím založte produkt znovu se správnými hodnotami');
                $logger
                    ->setType(LOGGER_TYPE_FAILED)
                    ->setOperation("Title was empty, title gotta be filled before adding news");
                break;

            case 'edit':
                $slug = $data->get('news-slug');
                $addedAt = new \DateTimeImmutable($data->get('news-addedAt'));
                $allCategories = $this->em->getRepository(NewsCategories::class)->findAll();
                foreach ($allCategories as $categoryRow) {
                    $entity->removeNewsCategory($categoryRow);
                }

                if ($data->get('category-action')) {
                    foreach ($data->get('category-action') as $category) {
                        if ($category != '0') {
                            $categoryEntity = $this->em->getRepository(NewsCategories::class)->findOneBy(['id' => $category]);
                            $entity->addNewsCategory($categoryEntity);
                        }
                    }
                }
                /** @var UploadedFile $uploadedFile */
                $uploadedFile = $files->get('news-image');

                if ($uploadedFile) {
                    $newFileName = $uploadHelper->uploadImage($uploadedFile, 'news', $entity->getImgName());
                    $entity->setImgName($newFileName);
                    //$news->setImgName(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME));
                }

                if (!$user = $this->em->getRepository(User::class)->findOneBy(
                    ['nickname' => $data->get('news-author')]
                )) {
                    $user = $this->getUser();
                }

                if (!empty($data->get('news-title'))) {
                    if (empty($slug)) {
                        $slug = Urlizer::urlize($data->get('news-title'));
                    }
                    if (!empty($addedAt)) {
                        $entity->setAddedAt($addedAt);
                    }
                    $entity
                        ->setAuthor($user)
                        ->setSlug($slug)
                        ->setTitle($data->get('news-title'))
                        ->setAlt($data->get('news-alt'))
                        ->setView($data->get('news-view'))
                        ->setNotation($data->get('news-notation'))
                        ->setContent($data->get('news-content'))
                        ->setKeywords($data->get('news-keywords'))
                        ->setMetaDescription($data->get('news-description'));

                    $this->em->persist($entity);
                    $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste aktualizovali záznam');
                    break;
                }
                $this->addFlash(FLASH_DANGER, 'Není vyplněno políčko titulek(nadpis), prosím založte produkt znovu se správnými hodnotami');
                $logger
                    ->setType(LOGGER_TYPE_FAILED)
                    ->setOperation("Title was empty, please fill title before adding news");
                break;

            case 'remove':
                $this->em->remove($entity);
                $this->addFlash(FLASH_WARNING, 'Úspěšně jste odstranili článek');
                break;

            case 'hide':
                $entity->hide();
                $this->addFlash(FLASH_WARNING, 'Skryli jste záznam');
                break;

            case 'show':
                $entity->show();
                $this->addFlash(FLASH_SUCCESS, 'Zviditelnili jste záznam');
                break;

            case 'add-category':
                if (!empty($data->get('category-name'))) {
                    $entity
                        ->setColor($data->get('category-color'))
                        ->setName($data->get('category-name'))
                        ->setDescription($data->get('category-description'));

                    $this->em->persist($entity);
                    $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste přidali záznam');
                    break;
                }
                $logger->setOperation("Name of category was empty, name gotta be filled before adding category");
                $this->addFlash(FLASH_DANGER, 'Není vyplněno políčko Název kategorie, prosím založte kategorii znovu se správnými hodnotami');
                break;

            case 'edit-category':
                if (!empty($data->get('category-name'))) {
                    $entity
                        ->setColor($data->get('category-color'))
                        ->setName($data->get('category-name'))
                        ->setDescription($data->get('category-description'))
                    ;
                    $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste aktualizovali záznam');
                    break;
                }
                $this->addFlash(FLASH_DANGER, 'Není vyplněno políčko Název kategorie, prosím upravte kategorii znovu se správnými hodnotami');
                $logger
                    ->setType(LOGGER_TYPE_FAILED)
                    ->setOperation("Name of category was empty, name gotta be filled before editing category");
                break;

            case 'hide-category':
                $entity->hide();
                $this->addFlash(FLASH_WARNING, 'Skryli jste záznam');
                break;

            case 'show-category':
                $entity->show();
                $this->addFlash(FLASH_SUCCESS, 'Zviditelnili jste záznam');
                break;

            case 'remove-category':
                if ($this->isGranted(self::SUPER_ADMIN)) { // There is an violation because is deleting
                    $this->em->remove($entity);
                    $this->addFlash(FLASH_WARNING, 'Odstranili jste záznam');
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

        $logger = $this->completeLogger($logger, MODULE_NEWS, "ID: [ ".$entity->getId()." ] ");

        $this->em->persist($logger);
        $this->em->flush();
    }
}
