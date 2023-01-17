<?php

namespace App\Controller;

use App\Entity\Log;
use App\Repository\LogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EditorLogController extends BaseEditorController
{
    /**
     * @Route("/editor-log", name="editor_log")
     */
    public function index(EntityManagerInterface $em, LogRepository $logRepo): Response
    {
        $logs = $logRepo->findAllOrderBy('id','DESC');

        return $this->render('editor/editor_log/log.html.twig', [
            'title' => EditorController::PAGE_TITLE,
            'logs' => $logs,
        ]);
    }
}