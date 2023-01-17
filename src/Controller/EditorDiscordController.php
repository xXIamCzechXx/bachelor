<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Webhooks;
use App\Service\DiscordWebhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EditorDiscordController extends BaseEditorController
{
    /**
     * @Route("/editor-discord", name="editor_discord")
     */
    public function index(): Response
    {
        $webhooks = $this->em->getRepository(Webhooks::class)->findAll();
        return $this->render('editor/editor_discord/discord.html.twig', [
            'webhooks' => $webhooks,
        ]);
    }

    /**
     * @Route("/editor-send/send-webhook", name="editor_send_webhook", methods="POST")
     */
    public function sendRequest(Request $request, DiscordWebhook $discordWebhook)
    {
        $logger = new Log();
        $data = $request->request;
        $logger->setAction($data->get('message-action'));
        $url = $data->get('message-url');

        switch ($data->get('message-action')) {
            case 'send':
                if (isset($url) && !empty($url)) {
                    $discordWebhook->setContent($data->get('message-content'));
                    $discordWebhook->setType($data->get('message-type'));
                    $discordWebhook->setTitle($data->get('message-title'));

                    $result = $discordWebhook->sendMessage($discordWebhook->getMessage(), $url);
                    if (empty($result)) {
                        $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste poslali zprávu');
                    } else {
                        $this->addFlash(FLASH_DANGER, 'Zprávu se z neznámých důvodů nepodařilo odeslat error code: '.$result);
                    }
                } else {
                    $this->addFlash(FLASH_DANGER, 'Není vyplněna url webhooku');
                }
                break;

            default:
                $this->addFlash(FLASH_DANGER, UNEXPECTED_ERROR_FLASH);
                $logger
                    ->setOperation(UNEXPECTED_ERROR)
                    ->setType(LOGGER_TYPE_FAILED);
                break;
        }

        if(empty($logger->getOperation())) {
            $logger->setOperation("Poslání zprávy přes webhook [ null ] ");
        }
        $logger
            ->setModule($this->getModuleName(MODULE_DISCORD_WEBHOOK))
            ->setUser($this->getUser())
            ->setUserName($this->getUser()->getFirstName());

        $this->em->persist($logger);
        $this->em->flush();

        return $this->redirectToRoute('editor_discord');
    }


    /**
     * @Route("/editor-edit/{id}/webhook", name="editor_edit_webhook", methods="POST")
     */
    public function modifyWebhook(Webhooks $webhook, Request $request)
    {
        $logger = new Log();
        $data = $request->request;
        $logger->setAction($data->get('tournament-action'));
        $date = new \DateTimeImmutable($data->get('tournament-date'));

        switch ($data->get('tournament-action')) {
            case 'edit':
                if ($this->isGranted(SUPER_ADMIN)) {
                    if (!empty($data->get('tournament-name'))) {

                        $webhook
                                ->setName($data->get('tournament-name'))
                                ->setDescription($data->get('tournament-description'))
                                ->setDate($date)
                        ;
                        $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste změnili turnaje');
                        break;
                    }
                    $this->addFlash(FLASH_DANGER, 'Není vyplněn název turnaje, zkuste to prosím znovu');
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
                    $this->em->remove($webhook);
                    $this->addFlash(FLASH_WARNING, 'Úspěšně jste odstranili turnaj');
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
            $logger->setOperation($webhook->getName()." [ ".$webhook->getId()." ] ");
        }
        if (empty($logger->getType())) {
            $logger->setType(LOGGER_TYPE_SUCCESS);
        }

        $logger
                ->setModule($this->getModuleName(MODULE_DISCORD_WEBHOOK))
                ->setUser($this->getUser())
                ->setUserName($this->getUser()->getFirstName())
        ;

        $this->em->persist($logger);
        $this->em->flush();

        return $this->redirectToRoute('editor_discord');
    }

    /**
     * @Route("/editor-add/webhook", name="editor_add_webhook", methods="POST")
     */
    public function addWebhook(Request $request)
    {
        $logger = new Log();
        $webhook = new Webhooks();
        $data = $request->request;
        $logger->setAction($data->get('tournament-action'));
        $date = new \DateTimeImmutable($data->get('tournament-date'));

        switch ($data->get('tournament-action')) {
            case 'add':
                if ($this->isGranted(SUPER_ADMIN)) {
                    if (!empty($data->get('tournament-name'))) {
                        $webhook
                                ->setName($data->get('tournament-name'))
                                ->setDescription($data->get('tournament-description'))
                                ->setDate($date)
                        ;
                        $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste vytvořili turnaj '.$data->get('tournament-name'));
                        break;
                    }
                    $this->addFlash(FLASH_DANGER, 'Není vyplněn název turnaje, zkuste to prosím znovu');
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
            $logger->setOperation($webhook->getName());
        }

        if (empty($logger->getType())) {
            $logger->setType(LOGGER_TYPE_SUCCESS);
        }

        $logger
            ->setModule($this->getModuleName(MODULE_DISCORD_WEBHOOK))
            ->setUser($this->getUser())
            ->setUserName($this->getUser()->getFirstName());

        $this->em->persist($logger);
        if ($webhook) {
            $this->em->persist($webhook);
        }
        $this->em->flush();

        return $this->redirectToRoute('editor_discord');
    }
}
