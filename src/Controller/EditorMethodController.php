<?php

namespace App\Controller;

use App\Entity\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EditorMethodController extends AbstractController
{
    #[Route('/editor/method', name: 'editor_method')]
    public function index(): Response
    {
        $methods = $this->getDoctrine()->getRepository(Method::class)->findAll();
        return $this->render('editor/editor_method/method.html.twig', [
            'methods' => $methods,
        ]);
    }
}
