<?php

namespace App\Controller;

use App\Connector\TournamentAssistantApi;
use App\Entity\News;
use App\Entity\Pages;
use App\Entity\Users;
use App\Form\CookieFormType;
use App\Service\Cookie;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends BaseController
{

    /**
     * @Route("/na", name="na")
     */
    public function index(Request $request): Response
    {
        return $this->render('default/utilities/na.html.twig', [
            'page' => null,
            'newsContentLength' => 164,
            'newsTitleLength' => 54,
        ]);
    }
    /**
     * @Route("/", name="homepage")
     */
    public function homepage(Request $request): Response
    {
        if (!$this->isGranted("ROLE_ADMIN")) {
            $this->redirectToRoute("na");
        }
        if(!$page = $this->em->getRepository(Pages::class)->findOneBy(['name' => 'homepage'])) {
            throw $this->createNotFoundException();
        }

        $topNews = $this->em->getRepository(News::class)->findVisible(1, 0);
        $news = $this->em->getRepository(News::class)->findVisible(2, 1);

        return $this->render('default/index.html.twig', [
            'page' => $page,
            'topNews' => $topNews,
            'newsArray' => $news,
            'newsContentLength' => 164,
            'newsTitleLength' => 54,
        ]);
    }

    /**
     * @Route("/_clear-cache", name="cacheClear")
     */
    public function clearCache(Request $request)
    {
        $response = new Response();
        $cache = new FilesystemAdapter();
        $output = $cache->clear();

        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/plain');
        $response->setContent($output);

        return $response;
    }

    /**
     * @Route("/instagram-token", name="instagramToken")
     */
    public function instagramTokenAction(Request $request)
    {
        $response = new Response();
        // This prevents from accessing route directrly from browser because this token is sensitive
        if ($request->isXmlHttpRequest()) {
            return $response->setContent("IGQVJXQWZAiRHExeTRtZAU5oUDV5T0x3VHBYZAjUxSDB0cEpCNUN5bHc2WVE0NGo2akFFbFhKZA1FnelJ2U2xNQWJOSm5NcmtkdEJWRGRMVDVSQ21XODNLTGUxU2Vyd3JJbXIzcVF4R3R1NHpGeUowQmVvWgZDZD");
        }

        throw $this->createNotFoundException();
    }
}
