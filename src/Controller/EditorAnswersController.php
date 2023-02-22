<?php

namespace App\Controller;

use App\Entity\FormAnswers;
use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\InputBag;
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
            'answers' => $answers,
        ]);
    }

    /**
     * @Route("/editor-edit/{id}/answer", name="editor_edit_answer")
     */
    public function editAnswer(Request $request, FormAnswers $answer): Response
    {
        if ($this->isGranted(self::SUPER_ADMIN) || $this->isGranted(self::EDITOR)) {
            $data = $request->request;
            $this->processRequest($answer, $data, $data->get('answer-action'));
        } else {
            $this->addFlash(FLASH_DANGER, NO_RIGHTS);
        }

        return $this->redirectToRoute('editor_answers');
    }

    /**
     * @param $entity
     * @param InputBag $data
     * @param string $action
     */
    protected function processRequest($entity, InputBag $data, String $action = 'undefined')
    {
        $logger = new Log();
        $logger->setAction($action);

        switch ($action) {
            case 'remove':
                $logger->setOperation($entity->getName()." [ ".$entity->getId()." ] ");
                $this->em->remove($entity);
                $this->addFlash(FLASH_SUCCESS, 'Odpověď z formuláře byla úspěšně smazána');
                break;

            default:
                $this->addFlash(FLASH_DANGER, UNEXPECTED_ERROR_FLASH);
                $logger
                    ->setOperation(UNEXPECTED_ERROR)
                    ->setType(LOGGER_TYPE_FAILED);
                break;
        }

        $logger = $this->completeLogger($logger, MODULE_FORM_ANSWERS, "ID: [ ".$entity->getId()." ] ");

        $this->em->persist($logger);
        $this->em->flush();
    }
}
