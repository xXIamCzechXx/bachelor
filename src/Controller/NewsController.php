<?php

namespace App\Controller;

use App\Entity\News;
use App\Entity\Pages;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class NewsController extends BaseController
{
    /**
     * @Route("/clanky", name="news")
     */
    public function index(Request $request): Response
    {
        if(!$page = $this->em->getRepository(Pages::class)->findOneBy(['name' => 'news'])) {
            throw $this->createNotFoundException();
        }

        $topNews = $this->em->getRepository(News::class)->findVisible(1, 0);
        $secNews = $this->em->getRepository(News::class)->findVisible(2, 1);
        $news = $this->em->getRepository(News::class)->findVisible(5, 3);

        return $this->render('default/news/news.html.twig', [
            'page' => $page,
            'topNews' => $topNews,
            'secNews' => $secNews,
            'news' => $news,
        ]);
    }

    /**
     * @Route("/clanky/{slug}", name="news_detail")
     */
    public function newsDetail(News $news, Request $request)
    {
        if ($news->getView() != 0) {
            if(!$news = $this->em->getRepository(News::class)->findOneBy(['id' => $news->getId()])) {
                throw $this->createNotFoundException();
            }

            $page = array(
                'title' => $news->getTitle(),
                'name' => $news->getTitle(),
                'metaDescription' => $news->getMetaDescription(),
                'keywords' => $news->getKeywords(),
                'instagramToken' => $news->getInstagramToken(),
                'heading' => $news->getHeading(),
            );
            return $this->render('default/news/news_detail.html.twig', [
                'page' => $page,
                'news' => $news,
            ]);
        }
    }
}
