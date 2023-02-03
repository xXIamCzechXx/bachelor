<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Settings;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_SUPER_ADMIN")
 */
class EditorSettingsController extends BaseEditorController
{
    /**
     * @Route("/editor-settings", name="editor_settings")
     */
    public function index(): Response
    {
        return $this->render('editor/editor_settings/settings.html.twig', []);
    }

    /**
     * @Route("/editor-edit/{id}/settings", name="editor_edit_settings", methods="POST")
     */
    public function editSettings(Settings $settings, Request $request)
    {
        if ($this->isGranted(self::ADMIN)) {
            $data = $request->request;
            $this->processRequest($settings, $data, $data->get('settings-action'), $request->files);
        } else {
            $this->addFlash(FLASH_DANGER, NO_RIGHTS);
        }

        return $this->redirectToRoute('editor_settings');
    }

    /**
     * @param $entity
     * @param InputBag $data
     * @param string $action
     */
    protected function processRequest($entity, InputBag $data, String $action = 'undefined', $files = null, $uploadHelper = null)
    {
        $logger = new Log();
        $logger->setAction($action);

        switch ($action) {
            case 'edit':
                $entity
                    ->setInstagramFeedToken($data->get('settings-instagram-feed-token'))
                    ->setRecaptchaSecretKey($data->get('settings-recaptcha-secret-key'))
                    ->setRecaptchaSiteKey($data->get('settings-recaptcha-site-key'));

                $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste změnili konstantu');
                break;

            default:
                $this->addFlash(FLASH_DANGER, UNEXPECTED_ERROR_FLASH);
                $logger
                    ->setOperation(UNEXPECTED_ERROR)
                    ->setType(LOGGER_TYPE_FAILED);
                break;
        }

        $logger = $this->completeLogger($logger, MODULE_PAGES, $entity->getName() ." [ ".$entity->getId()." ] ");

        $this->em->persist($logger);
        $this->em->flush();
    }
}
