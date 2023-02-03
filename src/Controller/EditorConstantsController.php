<?php

namespace App\Controller;

use App\Entity\Constants;
use App\Entity\Log;
use App\Repository\ConstantsRepository;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\InputBag;
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
            'constants' => $constants,
        ]);
    }

    /**
     * @Route("/editor-edit/{id}/constant", name="editor_edit_constant", methods="POST")
     */
    public function editConstant(Constants $constant, Request $request)
    {
        if ($this->isGranted(self::ADMIN)) {
            $data = $request->request;
            $this->processRequest($constant, $data, $data->get('constant-action'));
        } else {
            $this->addFlash(FLASH_DANGER, NO_RIGHTS);
        }

        return $this->redirectToRoute('editor_constants');
    }

    /**
     * @Route("/editor-add/constant", name="editor_add_constant", methods="POST")
     */
    public function addConstant(Request $request)
    {
        if ($this->isGranted(self::SUPER_ADMIN)) {
            $constant = new Constants();
            $data = $request->request;
            $this->processRequest($constant, $data, $data->get('constant-action'));
        } else {
            $this->addFlash(FLASH_DANGER, NO_RIGHTS);
        }

        return $this->redirectToRoute('editor_constants');
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
            case 'add':
                if (!empty($data->get('constant-name'))) {
                    $constantName = Urlizer::urlize($data->get('constant-name'));
                    $constantFormattedName = str_replace("-", "_", $constantName);
                    $entity
                        ->setName(strtoupper($constantFormattedName))
                        ->setValue($data->get('constant-value'))
                        ->setDescription($data->get('constant-description'))
                        ->setType($data->get('constant-type'));

                    $this->em->persist($entity);
                    $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste vytvořili záznam '.$data->get('constant-name'));
                    break;
                }
                $this->addFlash(FLASH_DANGER, 'Není vyplněn název, zkuste to prosím znovu');
                $logger->setType(LOGGER_TYPE_FAILED);
                break;

            case 'edit':
                if (!empty($data->get('constant-name'))) {
                    $constantName = Urlizer::urlize($data->get('constant-name'));
                    $constantFormattedName = str_replace("-", "_", $constantName);
                    if ($this->isGranted(self::SUPER_ADMIN)) {
                        $entity->setName(strtoupper($constantFormattedName));
                    }
                    $entity
                        ->setValue($data->get('constant-value'))
                        ->setDescription($data->get('constant-description'))
                        ->setType($data->get('constant-type'));

                    $this->em->persist($entity);
                    $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste změnili záznam');
                    break;
                }
                $this->addFlash(FLASH_DANGER, 'Není vyplněn název, zkuste to prosím znovu');
                $logger->setType(LOGGER_TYPE_FAILED);
                break;

            case 'remove':
                if($this->isGranted(self::SUPER_ADMIN)) { // There is an violation because is deleting
                    $logger->setOperation($entity->getName()." [ ".$entity->getId()." ] ");
                    $this->em->remove($entity);
                    $this->addFlash(FLASH_WARNING, 'Úspěšně jste odstranili záznam');
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

        $logger = $this->completeLogger($logger, MODULE_CONSTANTS, $entity->getName() ." [ ".$entity->getId()." ] ");

        $this->em->persist($logger);
        $this->em->flush();
    }
}
