<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Webhooks;
use App\Service\DiscordWebhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function PHPUnit\Framework\isInstanceOf;

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
     * @Route("/editor-edit/{id}/webhook", name="editor_edit_webhook", methods="POST")
     */
    public function editWebhook(Webhooks $webhook, Request $request)
    {
        if ($this->isGranted(self::SUPER_ADMIN)) {
            $data = $request->request;
            $this->processRequest($webhook, $data, $data->get('webhook-action'));
        } else {
            $this->addFlash(FLASH_DANGER, NO_RIGHTS);
        }

        return $this->redirectToRoute('editor_discord');
    }

    /**
     * @Route("/editor-add/webhook", name="editor_add_webhook", methods="POST")
     */
    public function addWebhook(Request $request)
    {
        if ($this->isGranted(self::SUPER_ADMIN)) {
            $this->processRequest(new Webhooks(), $request->request, $request->request->get('webhook-action'));
        } else {
            $this->addFlash(FLASH_DANGER, NO_RIGHTS);
        }

        return $this->redirectToRoute('editor_discord');
    }

    /**
     * @Route("/editor-send/send-webhook", name="editor_send_webhook", methods="POST")
     */
    public function sendRequest(Request $request, DiscordWebhook $discordWebhook)
    {
        $data = $request->request;
        $this->processRequest($discordWebhook, $data, $data->get('webhook-action'));

        return $this->redirectToRoute('editor_discord');
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
                if (!empty($data->get('webhook-name'))) {
                    $entity
                        ->setName($data->get('webhook-name'))
                        ->setPlatform($data->get('webhook-platform'))
                        ->setSlug($data->get('webhook-url'));

                    $this->em->persist($entity);
                    $this->addFlash(FLASH_SUCCESS, 'Z??znam byl ??sp????n?? vytvo??en');
                    break;
                }
                $this->addFlash(FLASH_DANGER, 'Nen?? vypln??n n??zev, zkuste to pros??m znovu');
                $logger->setType(LOGGER_TYPE_FAILED);
                break;

            case 'edit':
                if (!empty($data->get('webhook-name'))) {

                    $entity
                        ->setName($data->get('webhook-name'))
                        ->setPlatform($data->get('webhook-platform'))
                        ->setSlug($data->get('webhook-url'));

                    $this->em->persist($entity);
                    $this->addFlash(FLASH_SUCCESS, 'Z??znam byl ??sp????n?? upraven');
                    break;
                }
                $this->addFlash(FLASH_DANGER, 'Nen?? vypln??n n??zev, zkuste to pros??m znovu');
                $logger->setType(LOGGER_TYPE_FAILED);
                break;

            case 'send-webhook':
                if (!empty($data->get('message-url'))) {
                    $entity->setContent($data->get('message-content'));
                    $entity->setType($data->get('message-type'));
                    $entity->setTitle($data->get('message-title'));

                    $result = $entity->sendMessage($entity->getMessage(), $data->get('message-url'));
                    if (empty($result)) {
                        $this->addFlash(FLASH_SUCCESS, '??sp????n?? jste poslali zpr??vu');
                    } else {
                        $this->addFlash(FLASH_DANGER, 'Zpr??vu se z nezn??m??ch d??vod?? nepoda??ilo odeslat error code: '.$result);
                    }
                } else {
                    $this->addFlash(FLASH_DANGER, 'Nen?? vypln??na url webhooku');
                    $logger->setType(LOGGER_TYPE_FAILED);
                }
                $logger->setOperation("Posl??n?? zpr??vy p??es webhook");
                break;

            case 'remove':
                if($this->isGranted(self::SUPER_ADMIN)) { // Removal always as SUPER_ADMIN
                    $logger->setOperation($entity->getName()." [ ".$entity->getId()." ] ");
                    $this->em->remove($entity);
                    $this->addFlash(FLASH_WARNING, '??sp????n?? jste odstranili z??znam');
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

        $logger = $this->completeLogger($logger, MODULE_DISCORD_WEBHOOK, $entity instanceof Webhooks ? $entity->getName() ." [ ".$entity->getId()." ] " : 'Webhook message');

        $this->em->persist($logger);
        $this->em->flush();
    }
}
