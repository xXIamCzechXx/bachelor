<?php

namespace App\Controller;

use App\Entity\Constants;
use App\Entity\Log;
use App\Repository\ConstantsRepository;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EditorConstantsController extends BaseEditorController
{
    /**
     * @Route("/editor-constants", name="editor_constants")
     */
    public function index(ConstantsRepository $constantsRepo): Response
    {
        $constants = $constantsRepo->findAllOrderBy('name', 'ASC');
        return $this->render('editor/editor_constants/constants.html.twig', [
            'title' => EditorController::PAGE_TITLE,
            'constants' => $constants,
        ]);
    }

    /**
     * @Route("/editor-edit/{id}/constant", name="editor_edit_constant", methods="POST")
     */
    public function modifyConstant(Constants $constant, Request $request)
    {
        $logger = new Log();
        $data = $request->request;
        $logger->setAction($data->get('constant-action'));

        switch ($data->get('constant-action')) {
            case 'edit':
                if ($this->isGranted(ADMIN)) {
                    if (!empty($data->get('constant-name'))) {
                        $constantName = Urlizer::urlize($data->get('constant-name'));
                        $constantFormattedName = str_replace("-", "_", $constantName);
                        if ($this->isGranted(SUPER_ADMIN)) {
                            $constant->setName(strtoupper($constantFormattedName));
                        }
                        $constant
                            ->setValue($data->get('constant-value'))
                            ->setDescription($data->get('constant-description'))
                            ->setType($data->get('constant-type'))
                        ;
                        $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste změnili konstantu');
                        break;
                    }
                    $this->addFlash(FLASH_DANGER, 'Není vyplněn název konstanty, zkuste to prosím znovu');
                    $logger->setType(LOGGER_TYPE_FAILED);
                    break;
                }
                $this->addFlash(FLASH_DANGER, NO_RIGHTS);
                $logger
                        ->setOperation(NO_RIGHTS)
                        ->setType(LOGGER_TYPE_FAILED);
                break;

            case 'remove':
                if($this->isGranted(SUPER_ADMIN)) {
                    $this->em->remove($constant);
                    $this->addFlash(FLASH_WARNING, 'Úspěšně jste odstranili konstantu');
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
            $logger->setOperation($constant->getName()." [ ".$constant->getId()." ] ");
        }
        if (empty($logger->getType())) {
            $logger->setType(LOGGER_TYPE_SUCCESS);
        }

        $logger
            ->setModule($this->getModuleName(MODULE_CONSTANTS))
            ->setUser($this->getUser())
            ->setUserName($this->getUser()->getFirstName())
        ;

        $this->em->persist($logger);
        $this->em->flush();

        return $this->redirectToRoute('editor_constants');
    }

    /**
     * @Route("/editor-add/constant", name="editor_add_constant", methods="POST")
     */
    public function addConstant(Request $request)
    {
        $logger = new Log();
        $constant = new Constants();
        $data = $request->request;
        $logger->setAction($data->get('constant-action'));

        switch ($data->get('constant-action')) {
            case 'add':
                if ($this->isGranted(SUPER_ADMIN)) {
                    if (!empty($data->get('constant-name'))) {
                        $constantName = Urlizer::urlize($data->get('constant-name'));
                        $constantFormattedName = str_replace("-", "_", $constantName);
                        $constant
                            ->setName(strtoupper($constantFormattedName))
                            ->setValue($data->get('constant-value'))
                            ->setDescription($data->get('constant-description'))
                            ->setType($data->get('constant-type'))
                        ;
                        $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste vytvořili konstantu '.$data->get('constant-name'));
                        break;
                    }
                    $this->addFlash(FLASH_DANGER, 'Není vyplněn název konstanty, zkuste to prosím znovu');
                    $logger->setType(LOGGER_TYPE_FAILED);
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
            $logger->setOperation($constant->getName());
        }

        if (empty($logger->getType())) {
            $logger->setType(LOGGER_TYPE_SUCCESS);
        }

        $logger
            ->setModule($this->getModuleName(MODULE_CONSTANTS))
            ->setUser($this->getUser())
            ->setUserName($this->getUser()->getFirstName());

        $this->em->persist($logger);
        if ($constant) {
            $this->em->persist($constant);
        }
        $this->em->flush();

        return $this->redirectToRoute('editor_constants');
    }
}
