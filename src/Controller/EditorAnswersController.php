<?php

namespace App\Controller;

use App\Entity\FormAnswers;
use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EditorAnswersController extends BaseEditorController
{
    use Traits\EditorErrorHandlers;

    /**
     * @Route("/editor-answers", name="editor_answers")
     */
    public function index(): Response
    {
        $answers = $this->em->getRepository(FormAnswers::class)->findAll();

        return $this->render('editor/editor_answers/answers.html.twig', [
            'title' => EditorController::PAGE_TITLE,
            'answers' => $answers,
        ]);
    }

    /**
     * @Route("/editor-edit/{id}/answer", name="editor_edit_answer")
     */
    public function editAnswer(Request $request, FormAnswers $answer): Response
    {
        $data = $request->request;
        $logger = new Log();
        $logger->setAction($data->get('answer-action'));

        switch ($data->get('answer-action')) {
            case 'remove':
                if ($this->isGranted(SUPER_ADMIN, EDITOR)) {
                    $this->em->remove($answer);

                    $this->addFlash(FLASH_SUCCESS, 'Odpověď z formuláře byla úspěšně smazána');
                    $logger->setType(LOGGER_TYPE_SUCCESS);
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
            $logger->setOperation($answer->getName());
        }

        $logger
            ->setModule($this->getModuleName(MODULE_FORM_ANSWERS))
            ->setUser($this->getUser())
            ->setUserName($this->getUser()->getFirstName());

        $this->em->persist($logger);
        $this->em->flush();

        return $this->redirectToRoute('editor_answers');
    }
}
