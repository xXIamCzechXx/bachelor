<?php

namespace App\Controller;

use App\Entity\Countries;
use App\Entity\Hdm;
use App\Entity\Log;
use App\Entity\Pages;
use App\Entity\Tournaments;
use App\Entity\User;
use App\Service\UploadHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_USER")
 */

class AccountController extends BaseController
{
    /**
     * @Route("/ucet", name="account")
     */
    public function index(Request $request): Response
    {
        if(!$page = $this->em->getRepository(Pages::class)->findOneBy(['name' => 'account'])) {
            throw $this->createNotFoundException();
        }

        $upcomingTourneys = $this->em->getRepository(Tournaments::class)->findAll();
        $scoresaberData = $this->getScoresaberData($this->getUser());

        $countries = $this->em->getRepository(Countries::class)->findAll();
        $hdms = $this->em->getRepository(Hdm::class)->findBy(['view' => 1]);

        return $this->render('default/account/account.html.twig', [
            'countries' => $countries,
            'hdms' => $hdms,
            'upcomingTourneys' => $upcomingTourneys,
            'scoresaberData' => $scoresaberData,
            'page' => $page,
        ]);
    }

    /**
     * @Route("/api/account", name="api_account")
     */
    public function accountApi()
    {
        $user = $this->getUser();

        return $this->json($user, 200, [], [
            'groups' => ['main']
        ]);
    }

    /**
     * @Route("/ucet-tourney-add/{id}/user", name="account_join_tourney")
     */
    public function addToTourney(User $user, Request $request)
    {
        $logger = new Log();
        $logger
            ->setAction("join-tourney")
            ->setModule(MODULE_ACCOUNT)
            ->setOperation("Login to tourney cst s2")
            ->setUserName($user->getNickname())
            ->setUser($user)
        ;
        if ($tournament = $this->em->getRepository(Tournaments::class)->findOneBy(["name" => 'CST S2 - PlayZONE Arena'])) {
            $user->addTournament($tournament);
            $logger->setType(LOGGER_TYPE_SUCCESS);
            $this->addFlash(FLASH_SUCCESS, 'Úspěšné přihlášení do turnaje, budeme se těšit!');

        } else {
            $logger->setType(LOGGER_TYPE_FAILED);
            $this->addFlash(FLASH_DANGER, 'Přihlášení do turnaje se nezdařilo, kontaktujte prosím podporu.');
        }
        $this->em->persist($user, $logger);
        $this->em->flush();
        return $this->redirectToRoute('account');
    }

    /**
     * @Route("/ucet-edit/{id}/user", name="account_edit_user", methods="POST")
     */
    public function modifyUser(User $user, Request $request, UserPasswordHasherInterface $passwordHasher, UploadHelper $uploadHelper)
    {
        $logger = new Log();
        $data = $request->request;
        $logger->setAction($data->get('user-action'));
        $birthday = new \DateTimeImmutable($data->get('user-birthday'));

        if ($this->getUser() == $user) {
            switch ($data->get('user-action')) {
                case 'edit':
                    if ($data->get('user-name') && (strlen($data->get('user-name')) <= 3 || strlen($data->get('user-name')) >= 21)) {
                        $this->addFlash(FLASH_DANGER, 'Jméno a příjmení musí být v rozmezí 4 až 20 znaků');
                        $logger->setType(LOGGER_TYPE_FAILED);
                        break;
                    }
                    if ($data->get('user-nickname') && (strlen($data->get('user-nickname')) <= 3 || strlen($data->get('user-nickname')) >= 21)) {
                        $this->addFlash(FLASH_DANGER, 'Nickname musí být v rozmezí 4 až 20 znaků');
                        $logger->setType(LOGGER_TYPE_FAILED);
                        break;
                    }
                    if ($this->getUser()->getNickname() !== $data->get('user-nickname') && null !== $this->em->getRepository(User::class)->findOneBy(['nickname' => $data->get('user-nickname')])) {
                        $this->addFlash(FLASH_DANGER, 'Tento nickname již používá jiný uživatel');
                        $logger->setType(LOGGER_TYPE_FAILED);
                        break;
                    }

                    /** @var UploadedFile $uploadedFile */
                    $uploadedFile = $request->files->get('user-image');

                    if ($uploadedFile) {
                        $newFileName = $uploadHelper->uploadImage($uploadedFile, 'users', $user->getImgName());
                        $user->setImgName($newFileName);
                    }

                    if (!$hdm = $this->em->getRepository(Hdm::class)->findOneBy(
                            ['name' => $data->get('user-hdm')]
                    )) {
                        $hdm = $this->em->getRepository(Hdm::class)->findOneBy(['id' => 1]);
                    }

                    if (!empty($user->getDiscordNickname()) && (!$this->hasNumber($data->get('discord-nickname')) || !strpos($data->get('discord-nickname'), '#'))) {
                        $this->addFlash(FLASH_DANGER, 'Neplatný discord nickname (musí obsahovat jméno#xxxx, kde x jsou čísla), pokud nevíte, nechte prázdný.');
                        $logger->setType(LOGGER_TYPE_FAILED);
                        break;
                    }

                    $country = $this->em->getRepository(Countries::class)->findOneBy(['id' => $data->get('user-country')]);

                    if (!empty($birthday)) {
                        $user->setBirthdate($birthday);
                    }

                    $user
                        ->setFirstName($data->get('user-name'))
                        ->setNickname($data->get('user-nickname'))
                        ->setCountry($country)
                        ->setColor($data->get('user-color'))
                        ->setGender($data->get('user-gender'))
                        ->setHdm($hdm)
                        ->setDiscordNickname($data->get('discord-nickname'))
                        ->setScoresaberId((int)$data->get('scoresaber-id'))
                        ->setTwitchNickname($data->get('twitch-nickname'))
                        ->setScoresaberLink(!empty($data->get('scoresaber-id')) ? 'https://scoresaber.com/u/'.$data->get('scoresaber-id') : null)
                        ->setTwitchLink(!empty($data->get('twitch-nickname')) ? 'https://www.twitch.tv/'.$data->get('twitch-nickname') : null)
                        ->setDescription($data->get('user-description'));

                    $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste změnili svoje uživatelské údaje');
                    $logger->setType(LOGGER_TYPE_SUCCESS);
                    break;

                case 'remove':
                    $this->em->remove($user);
                    $this->addFlash(FLASH_WARNING, 'Úspěšně jste odstranili uživatele');
                    break;

                case 'password':
                    if ($data->get('user-password') == $data->get('user-passwordRepeat')) {
                        $user->setPassword($passwordHasher->hashPassword($user, $data->get('user-password')));
                        $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste změnili heslo uživatele');
                        $logger->setType(LOGGER_TYPE_SUCCESS);
                        break;
                    }
                    $this->addFlash(FLASH_DANGER, 'Hesla se neshodují, heslo nebylo nezměnilo');
                    $logger->setType(LOGGER_TYPE_FAILED);
                    break;

                default:
                    $this->addFlash(FLASH_DANGER, UNEXPECTED_ERROR_FLASH);
                    $logger
                        ->setOperation(UNEXPECTED_ERROR)
                        ->setType(LOGGER_TYPE_FAILED);
                    break;
            }
        } else {
            $this->addFlash(FLASH_DANGER, 'Záznam upravuje neoprávněný uživatel');
        }


        if(empty($logger->getOperation())) {
            $logger->setOperation($user->getNickname()." [ ".$user->getId()." ] ");
        }
        if (empty($logger->getType())) {
            $logger->setType(LOGGER_TYPE_SUCCESS);
        }

        $logger
            ->setModule('Account')
            ->setUser($this->getUser())
            ->setUserName($this->getUser()->getFirstName());

        $this->em->persist($logger);
        $this->em->flush();

        return $this->redirectToRoute('account');
    }

    /**
     * @param $users
     * @return array
     */
    public function getScoresaberData($user)
    {
        $player = [];
        $context = stream_context_create(array('https' => array('header'=>'Connection: close\r\n')));

        if (null != $user->getScoresaberId() && $user->getScoresaberId() !== '' && $user->getScoresaberId() !== 0) {
            if ($playerData = json_decode(@file_get_contents('https://new.scoresaber.com/api/player/'.$user->getScoresaberId().'/basic', false, $context))) {
                $player["pp"] = number_format($playerData->playerInfo->pp, 2);
                $player["countryRank"] = $playerData->playerInfo->countryRank;
                $player["rank"] = $playerData->playerInfo->rank;
                $player["country"] = $playerData->playerInfo->country;
            }
        }
        if (empty($player)) {
            $player["pp"] = "Nutný scoresaber";
            $player["countryRank"] = "Nutný scoresaber";
            $player["rank"] = "Nutný scoresaber";
            $player["country"] = "CZ";
        }

        return $player;
    }
}
