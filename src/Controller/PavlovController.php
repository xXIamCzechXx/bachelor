<?php

namespace App\Controller;

use App\Entity\Pages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class PavlovController extends BaseController
{
    /**
     * @Route("/pavlov", name="pavlov")
     */
    public function index(Request $request): Response
    {
        if(!$page = $this->em->getRepository(Pages::class)->findOneBy(['name' => 'pavlov'])) {
            throw $this->createNotFoundException();
        }

        return $this->render('default/pavlov/pavlov.html.twig', [
            'page' => $page,
        ]);
    }
}
