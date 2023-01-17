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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Config\TwigConfig;
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
        'title' => EditorController::PAGE_TITLE,
        'newsTitleLength' => 44,
        'newsContentLength' => 80,
        'newsArray' => $news,
        'admins' => $admins,
        'categories' => $categories,
      ]);
    }

    protected function getRequest($request = null)
    {

    }
    /**
     * @Route("/editor-edit/{slug}/news", name="editor_edit_news", methods="POST")
     */
    public function editNews(News $news, Request $request, UploadHelper $uploadHelper)
    {
        $logger = new Log();
        $data = $request->request;
        $logger->setAction($data->get('news-action'));

        $slug = $data->get('news-slug');
        $addedAt = new \DateTimeImmutable($data->get('news-addedAt'));

        switch ($data->get('news-action')) {
            case 'edit':
                if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
                    $allCategories = $this->em->getRepository(NewsCategories::class)->findAll();
                    foreach ($allCategories as $categoryRow) {
                        $news->removeNewsCategory($categoryRow);
                    }

                    if ($data->get('category-action')) {
                        foreach ($data->get('category-action') as $category) {
                            if ($category != '0') {
                                $categoryEntity = $this->em->getRepository(NewsCategories::class)->findOneBy(['id' => $category]);
                                $news->addNewsCategory($categoryEntity);
                            }
                        }
                    }
                    /** @var UploadedFile $uploadedFile */
                    $uploadedFile = $request->files->get('news-image');

                    if ($uploadedFile) {
                        $newFileName = $uploadHelper->uploadImage($uploadedFile, 'news', $news->getImgName());
                        $news->setImgName($newFileName);
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
                            $news->setAddedAt($addedAt);
                        }
                        $news
                            ->setAuthor($user)
                            ->setSlug($slug)
                            ->setTitle($data->get('news-title'))
                            ->setAlt($data->get('news-alt'))
                            ->setView($data->get('news-view'))
                            ->setNotation($data->get('news-notation'))
                            ->setContent($data->get('news-content'))
                            ->setKeywords($data->get('news-keywords'))
                            ->setMetaDescription($data->get('news-description'));

                        $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste aktualizovali článek');
                        break;
                    }
                    $this->addFlash(FLASH_DANGER, 'Není vyplněno políčko titulek(nadpis), prosím založte produkt znovu se správnými hodnotami');
                    $logger
                        ->setType(LOGGER_TYPE_FAILED)
                        ->setOperation("Title was empty, please fill title before adding news");
                    break;
                }
                $this->addFlash(FLASH_DANGER, NO_RIGHTS);
                $logger
                    ->setOperation(NO_RIGHTS)
                    ->setType(LOGGER_TYPE_FAILED);
                break;

            case 'remove':
                if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
                    $this->em->remove($news);
                    $this->addFlash(FLASH_WARNING, 'Úspěšně jste odstranili článek');
                    break;
                }
                $this->addFlash(FLASH_DANGER, NO_RIGHTS);
                $logger
                    ->setOperation(NO_RIGHTS)
                    ->setType(LOGGER_TYPE_FAILED);
                break;

            case 'hide':
                if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR) || $this->isGranted(ADMIN)) {
                    $news->hide();
                    $this->addFlash(FLASH_WARNING, 'Skrili jste novinku');
                    break;
                }
                $this->addFlash(FLASH_DANGER, NO_RIGHTS);
                $logger
                    ->setOperation(NO_RIGHTS)
                    ->setType(LOGGER_TYPE_FAILED);
                break;

            case 'show':
                if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR) || $this->isGranted(ADMIN)) {
                    $news->show();
                    $this->addFlash(FLASH_SUCCESS, 'Zviditelnili jste novinku');
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

        if (empty($logger->getOperation())) {
            $logger->setOperation($news->getTitle()." [ ".$news->getId()." ] ");
        }
        if (empty($logger->getType())) {
            $logger->setType(LOGGER_TYPE_SUCCESS);
        }

        $logger
            ->setModule($this->getModuleName(MODULE_NEWS))
            ->setUser($this->getUser())
            ->setUserName($this->getUser()->getFirstName());

        $this->em->persist($logger);
        $this->em->flush();

        return $this->redirectToRoute('editor_news');
    }

    /**
     * @Route("/editor-add/news", name="editor_add_news", methods="POST")
     */
    public function addNews(Request $request, UploadHelper $uploadHelper)
    {
        $logger = new Log();
        $news = new News();
        $data = $request->request;
        $logger->setAction($data->get('news-action'));

        $slug = $data->get('news-slug');
        $addedAt = new \DateTimeImmutable($data->get('news-addedAt'));

        switch ($data->get('news-action')) {
            case 'add':
                if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
                    if ($data->get('category-action')) {
                        foreach ($data->get('category-action') as $category) {
                            if ($category != '0') {
                                $categoryEntity = $this->em->getRepository(NewsCategories::class)->findOneBy(['id' => $category]);
                                $news->addNewsCategory($categoryEntity);
                            }
                        }
                    }
                    /** @var UploadedFile $uploadedFile */
                    $uploadedFile = $request->files->get('news-image');

                    if ($uploadedFile) {
                        $newFileName = $uploadHelper->uploadImage($uploadedFile, 'news', $news->getImgName());
                        $news->setImgName($newFileName);
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
                            $news->setAddedAt($addedAt);
                        }
                        $news
                            ->setAuthor($user)
                            ->setSlug($slug)
                            ->setTitle($data->get('news-title'))
                            ->setAlt($data->get('news-alt'))
                            ->setView($data->get('news-view'))
                            ->setNotation($data->get('news-notation'))
                            ->setContent($data->get('news-content'))
                            ->setKeywords($data->get('news-keywords'))
                            ->setMetaDescription($data->get('news-description'));

                        $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste přidali článek');
                        break;
                    }
                    $news = null;
                    $this->addFlash(FLASH_DANGER, 'Není vyplněno políčko titulek(nadpis), prosím založte produkt znovu se správnými hodnotami');
                    $logger
                        ->setType(LOGGER_TYPE_FAILED)
                        ->setOperation("Title was empty, title gotta be filled before adding news");
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
        if (empty($logger->getOperation())) {
            $logger->setOperation($news->getTitle());
        }
        if (empty($logger->getType())) {
            $logger->setType(LOGGER_TYPE_SUCCESS);
        }

        $logger
            ->setModule($this->getModuleName(MODULE_NEWS))
            ->setUser($this->getUser())
            ->setUserName($this->getUser()->getFirstName());

        $this->em->persist($logger);
        if ($news) {
            $this->em->persist($news);
        }
        $this->em->flush();
        return $this->redirectToRoute('editor_news');
    }

  /**
   * @Route("/editor-news/categories", name="editor_news_categories")
   */
  public function newsCategories(NewsCategoriesRepository $categoryRepository)
  {
    $categories = $categoryRepository->findAll();

    return $this->render('editor/editor_news/news_categories.html.twig', [
      'title' => EditorController::PAGE_TITLE,
      'categories' => $categories,
    ]);
  }

  /**
   * @Route("/editor-add/news-category", name="editor_add_news_category", methods="POST")
   */
  public function addNewsCategory(Request $request)
  {
    $logger = new Log();
    $newsCategory = new NewsCategories();
    $data = $request->request;
    $logger->setAction($data->get('category-action'));

    switch ($data->get('category-action')) {
      case 'add':
          if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
              if (!empty($data->get('category-name'))) {
                  $newsCategory
                      ->setColor($data->get('category-color'))
                      ->setName($data->get('category-name'))
                      ->setDescription($data->get('category-description'));
                  $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste přidali kategorii článku');
                  break;
              }
              $newsCategory = null;
              $logger->setOperation("Name of category was empty, name gotta be filled before adding category");
              $this->addFlash(FLASH_DANGER, 'Není vyplněno políčko Název kategorie, prosím založte kategorii znovu se správnými hodnotami');
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
        $logger->setOperation($newsCategory->getName()." [ ".$newsCategory->getId()." ] ");
    }
    if (empty($logger->getType())) {
        $logger->setType(LOGGER_TYPE_SUCCESS);
    }

    $logger
      ->setModule($this->getModuleName(MODULE_NEWS_CATEGORIES))
      ->setUser($this->getUser())
      ->setUserName($this->getUser()->getFirstName());

    $this->em->persist($logger);
    if ($newsCategory) {
      $this->em->persist($newsCategory);
    }
    $this->em->flush();

    return $this->redirectToRoute('editor_news_categories');
  }

  /**
   * @Route("/editor-edit/{id}/news-category", name="editor_edit_news_category", methods="POST")
   */
  public function editNewsCategory(NewsCategories $newsCategory, Request $request)
  {
    $logger = new Log();
    $data = $request->request;
    $logger->setAction($data->get('category-action'));

    switch ($data->get('category-action')) {
      case 'edit':
        if (!empty($data->get('category-name'))) {
          $newsCategory
            ->setColor($data->get('category-color'))
            ->setName($data->get('category-name'))
            ->setDescription($data->get('category-description'))
          ;
          $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste aktualizovali kategorii galerie');
          break;
        }
        $this->addFlash(FLASH_DANGER, 'Není vyplněno políčko Název kategorie, prosím upravte kategorii znovu se správnými hodnotami');
        $logger
            ->setType(LOGGER_TYPE_FAILED)
            ->setOperation("Name of category was empty, name gotta be filled before editing category");
        break;

        case 'hide':
            if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
                $newsCategory->hide();
                $this->addFlash(FLASH_WARNING, 'Skrili jste kategorii novinek');
                break;
            }
            $this->addFlash(FLASH_DANGER, NO_RIGHTS);
            $logger
                    ->setOperation(NO_RIGHTS)
                    ->setType(LOGGER_TYPE_FAILED);
            break;

        case 'show':
            if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
                $newsCategory->show();
                $this->addFlash(FLASH_SUCCESS, 'Zviditelnili jste kategorii novinek');
                break;
            }
            $this->addFlash(FLASH_DANGER, NO_RIGHTS);
            $logger
                    ->setOperation(NO_RIGHTS)
                    ->setType(LOGGER_TYPE_FAILED);
            break;

      case 'remove':
          if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
              $this->em->remove($newsCategory);
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
        $logger->setOperation($newsCategory->getName());
    }
    if (empty($logger->getType())) {
        $logger->setType(LOGGER_TYPE_SUCCESS);
    }

    $logger
      ->setModule($this->getModuleName(MODULE_NEWS_CATEGORIES))
      ->setUser($this->getUser())
      ->setUserName($this->getUser()->getFirstName());

    $this->em->persist($logger);
    $this->em->flush();

    return $this->redirectToRoute('editor_news_categories');
  }
}
