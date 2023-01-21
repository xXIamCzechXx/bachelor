<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Settings;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function modifySettings(Settings $settings, Request $request)
    {
        $logger = new Log();
        $data = $request->request;
        $action = $data->get('settings-action');
        $logger->setAction($action);

        switch ($action) {
            case 'edit':
                if ($this->isGranted(SUPER_ADMIN)) {
                    $settings
                        ->setInstagramFeedToken($data->get('settings-instagram-feed-token'))
                        ->setRecaptchaSecretKey($data->get('settings-recaptcha-secret-key'))
                        ->setRecaptchaSiteKey($data->get('settings-recaptcha-site-key'))
                    ;
                    $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste změnili konstantu');
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
            $logger->setOperation("Změna nastavení webu");
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

        return $this->redirectToRoute('editor_settings');
    }
}
