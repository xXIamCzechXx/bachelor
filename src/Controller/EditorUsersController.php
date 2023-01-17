<?php

namespace App\Controller;

use App\Entity\Countries;
use App\Entity\FormAnswers;
use App\Entity\Hdm;
use App\Entity\Log;
use App\Entity\News;
use App\Entity\Tournaments;
use App\Entity\User;
use App\Entity\UserBadges;
use App\Service\UploadHelper;
use App\Service\UserNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EditorUsersController extends BaseEditorController
{
    use Traits\EditorErrorHandlers;

    /**
     * @Route("/editor-users", name="editor_users")
     */
    public function index(): Response
    {
        $users = $this->em->getRepository(User::class)->findAll();
        $countries = $this->em->getRepository(Countries::class)->findAll();
        $badges = $this->em->getRepository(UserBadges::class)->findAll();
        $hdms = $this->em->getRepository(Hdm::class)->findBy(['view' => 1]);

        return $this->render('editor/editor_users/users.html.twig', [
            'title' => EditorController::PAGE_TITLE,
            'users' => $users,
            'hdms' => $hdms,
            'countries' => $countries,
            'badges' => $badges,
        ]);
    }

    /**
     * @Route("/editor-users/badges", name="editor_users_badges")
     */
    public function badges(): Response
    {
        $badges = $this->em->getRepository(UserBadges::class)->findAll();

        return $this->render('editor/editor_users/users_badges.html.twig', [
                'title' => EditorController::PAGE_TITLE,
                'badges' => $badges
        ]);
    }

    /**
     * @Route("/editor-users/hdms", name="editor_users_hdms")
     */
    public function hdms(): Response
    {
        $hdms = $this->em->getRepository(Hdm::class)->findAll();

        return $this->render('editor/editor_users/users_hdms.html.twig', [
                'title' => EditorController::PAGE_TITLE,
                'hdms' => $hdms
        ]);
    }

    /**
     * @Route("/editor-edit/{id}/user", name="editor_edit_user", methods="POST")
     */
    public function modifyUser(User $user, Request $request, UserPasswordHasherInterface $passwordHasher, UserNormalizer $userNormalizer, UploadHelper $uploadHelper)
    {
        $logger = new Log(); // Upravit na user log
        $data = $request->request;
        $logger->setAction($data->get('user-action'));
        $birthday = new \DateTimeImmutable($data->get('user-birthday'));

        switch ($data->get('user-action')) {
            case 'edit':
                if ($this->isGranted(SUPER_ADMIN)) {
                    $country = $this->em->getRepository(Countries::class)->findOneBy(['id' => $data->get('user-country')]);

                    if ($data->get('user-name') && strlen($data->get('user-name')) <= 3) {
                        $this->addFlash(FLASH_DANGER, 'Nevyplnili jste jméno uživatele nebo je kratší jak 4 znaků');
                        $logger->setType(LOGGER_TYPE_FAILED);
                        break;
                    }
                    if ($data->get('user-nickname') && strlen($data->get('user-nickname')) <= 3) {
                        $this->addFlash(FLASH_DANGER, 'Nevyplnili jste nickname uživatele nebo je kratší jak 4 znaků');
                        $logger->setType(LOGGER_TYPE_FAILED);
                        break;
                    }
                    if ($user->getNickname() !== $data->get('user-nickname') && null !== $this->em->getRepository(User::class)->findOneBy(['nickname' => $data->get('user-nickname')])) {
                        $this->addFlash(FLASH_DANGER, 'Tento nickname již používá jiný uživatel');
                        $logger->setType(LOGGER_TYPE_FAILED);
                        break;
                    }

                    $allBadges = $this->em->getRepository(UserBadges::class)->findAll();
                    foreach ($allBadges as $badgeRow) {
                        $user->removeBadge($badgeRow);
                    }

                    if ($data->get('badge-action')) {
                        foreach ($data->get('badge-action') as $badge) {
                            if ($badge != '0') {
                                $badgeEntity = $this->em->getRepository(UserBadges::class)->findOneBy(['id' => $badge]);
                                $user->addBadge($badgeEntity);
                            }
                        }
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

                    $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste změnili údaje uživatele');
                    $logger->setType(LOGGER_TYPE_SUCCESS);
                    break;
                }
                $this->addFlash(FLASH_DANGER, NO_RIGHTS);
                $logger
                    ->setOperation(NO_RIGHTS)
                    ->setType(LOGGER_TYPE_FAILED);
                break;

            case 'remove':
                if ($this->isGranted(SUPER_ADMIN)) {
                    $tournaments = $this->em->getRepository(Tournaments::class)->findAll();
                    foreach ($tournaments as $tournament) {
                        $tournament->removePlayer($user);
                    }

                    $newsArrray = $this->em->getRepository(News::class)->findBy(['author' => $user->getId()]);
                    foreach ($newsArrray as $news) {
                        $news->setAuthor(null);
                    }

                    $formAnswers = $this->em->getRepository(FormAnswers::class)->findBy(['User' => $user->getId()]);
                    foreach ($formAnswers as $formAnswer) {
                        $formAnswer->setUser(null);
                    }

                    $logs = $this->em->getRepository(Log::class)->findBy(['user' => $user->getId()]);
                    foreach ($logs as $log) {
                        $user->removeLog($log);
                        $this->em->remove($log);
                    }

                    $this->em->remove($user);
                    $this->addFlash(FLASH_WARNING, 'Úspěšně jste odstranili uživatele');
                    break;
                }
                $this->addFlash(FLASH_DANGER, NO_RIGHTS);
                $logger
                    ->setOperation(NO_RIGHTS)
                    ->setType(LOGGER_TYPE_FAILED);
                break;

            case 'hide':
                if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
                    $user->hide();
                    $this->addFlash(FLASH_WARNING, 'Zneaktivnili jste uživatele');
                    break;
                }
                $this->addFlash(FLASH_DANGER, NO_RIGHTS);
                $logger
                    ->setOperation(NO_RIGHTS)
                    ->setType(LOGGER_TYPE_FAILED);
                break;

            case 'show':
                if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
                    $user->show();
                    $this->addFlash(FLASH_SUCCESS, 'Zaktivnili jste uživatele');
                    break;
                }
                $this->addFlash(FLASH_DANGER, NO_RIGHTS);
                $logger
                    ->setOperation(NO_RIGHTS)
                    ->setType(LOGGER_TYPE_FAILED);
                break;

            case 'regenerate-unique-id':
                if ($this->isGranted(SUPER_ADMIN)) {
                    $user->setUniqueId($userNormalizer->generateUniquePlayerId());
                    $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste uživateli vygenerovali nové ID');
                    break;
                }
                $this->addFlash(FLASH_DANGER, NO_RIGHTS);
                $logger
                        ->setOperation(NO_RIGHTS)
                        ->setType(LOGGER_TYPE_FAILED);
                break;

            case 'recalculate-score':
                if ($this->isGranted(SUPER_ADMIN)) {
                    $user = $userNormalizer->calculateScore($user);
                    $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste přepočítali % uživateli');
                    break;
                }
                $this->addFlash(FLASH_DANGER, NO_RIGHTS);
                $logger
                    ->setOperation(NO_RIGHTS)
                    ->setType(LOGGER_TYPE_FAILED);
                break;

            case 'password':
                if ($this->isGranted(SUPER_ADMIN)) {
                    if ($data->get('user-password') == $data->get('user-passwordRepeat')) {
                        $user->setPassword($passwordHasher->hashPassword($user, $data->get('user-password')));
                        $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste změnili heslo uživatele');
                        $logger->setType(LOGGER_TYPE_SUCCESS);
                        break;
                    }
                    $this->addFlash(FLASH_DANGER, 'Hesla se neshodují, heslo nebylo nezměnilo');
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
            $logger->setOperation($user->getNickname()." [ ".$user->getId()." ] ");
        }
        if (empty($logger->getType())) {
            $logger->setType(LOGGER_TYPE_SUCCESS);
        }

        $logger
            ->setModule($this->getModuleName(MODULE_USERS))
            ->setUser($this->getUser())
            ->setUserName($this->getUser()->getFirstName());

        $this->em->persist($logger);
        $this->em->flush();

        return $this->redirectToRoute('editor_users');
    }

    /**
     * @Route("/editor-add/user-badge", name="editor_add_user_badge", methods="POST")
     */
    public function addUserBadge(Request $request, UploadHelper $uploadHelper)
    {
        $logger = new Log();
        $badge = new UserBadges();
        $data = $request->request;
        $logger->setAction($data->get('badge-action'));

        switch ($data->get('badge-action')) {
            case 'add':
                if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
                    if (!empty($data->get('badge-name'))) {

                        /** @var UploadedFile $uploadedFile */
                        $uploadedFile = $request->files->get('badge-image');

                        if ($uploadedFile) {
                            $newFileName = $uploadHelper->uploadImage($uploadedFile, 'badges');
                            $badge->setImgName($newFileName);
                        }
                        $badge->setName($data->get('badge-name'));
                        $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste přidali badge');
                        break;
                    }
                    $badge = null;
                    $logger->setOperation("Name of badge was empty, name gotta be filled before adding.");
                    $this->addFlash(FLASH_DANGER, 'Není vyplněno políčko Název badge, prosím založte badge znovu se správnými hodnotami');
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
            $logger->setOperation($badge->getName());
        }
        if (empty($logger->getType())) {
            $logger->setType(LOGGER_TYPE_SUCCESS);
        }

        $logger
            ->setModule($this->getModuleName(MODULE_BADGES))
            ->setUser($this->getUser())
            ->setUserName($this->getUser()->getFirstName());

        $this->em->persist($logger);
        if ($badge) {
            $this->em->persist($badge);
        }
        $this->em->flush();

        return $this->redirectToRoute('editor_users_badges');
    }

    /**
     * @Route("/editor-edit/{id}/news-user-badge", name="editor_edit_user_badge", methods="POST")
     */
    public function editUserBadge(UserBadges $badge, Request $request, UploadHelper $uploadHelper)
    {
        $logger = new Log();
        $data = $request->request;
        $logger->setOperation($data->get('badge-action'));

        switch ($data->get('badge-action')) {
            case 'edit':
                if (!empty($data->get('badge-name'))) {
                    /** @var UploadedFile $uploadedFile */
                    $uploadedFile = $request->files->get('badge-image');

                    if ($uploadedFile) {
                        $newFileName = $uploadHelper->uploadImage($uploadedFile, 'badges', $badge->getImgName());
                        $badge->setImgName($newFileName);
                    }

                    $badge->setName($data->get('badge-name'));
                    $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste aktualizovali badge');
                    break;
                }
                $this->addFlash(FLASH_DANGER, 'Není vyplněno políčko Název badge, prosím upravte badge znovu se správnými hodnotami');
                $logger
                    ->setType(LOGGER_TYPE_FAILED)
                    ->setOperation("Name of badge was empty, name gotta be filled before editing");
                break;

            case 'hide':
                if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
                    $badge->hide();
                    $this->addFlash(FLASH_WARNING, 'Skrili jste badge');
                    break;
                }
                $this->addFlash(FLASH_DANGER, NO_RIGHTS);
                $logger
                    ->setOperation(NO_RIGHTS)
                    ->setType(LOGGER_TYPE_FAILED);
                break;

            case 'show':
                if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
                    $badge->show();
                    $this->addFlash(FLASH_SUCCESS, 'Zviditelnili jste badge');
                    break;
                }
                $this->addFlash(FLASH_DANGER, NO_RIGHTS);
                $logger
                        ->setOperation(NO_RIGHTS)
                        ->setType(LOGGER_TYPE_FAILED);
                break;

            case 'remove':
                if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
                    $this->em->remove($badge);
                    $this->addFlash(FLASH_WARNING, 'Odstranili jste badge');
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
            $logger->setOperation($badge->getName()." [ ".$badge->getId()." ] ");
        }
        if (empty($logger->getType())) {
            $logger->setType(LOGGER_TYPE_SUCCESS);
        }

        $logger
            ->setModule($this->getModuleName(MODULE_BADGES))
            ->setUser($this->getUser())
            ->setUserName($this->getUser()->getFirstName());

        $this->em->persist($logger);
        $this->em->flush();

        return $this->redirectToRoute('editor_users_badges');
    }

    /**
     * @Route("/editor-add/user-hdm", name="editor_add_user_hdm", methods="POST")
     */
    public function addUserHdm(Request $request, UploadHelper $uploadHelper)
    {
        $logger = new Log();
        $hdm = new Hdm();
        $data = $request->request;
        $logger->setAction($data->get('hdm-action'));

        switch ($data->get('hdm-action')) {
            case 'add':
                if ($this->isGranted(ADMIN)) {
                    if (!empty($data->get('hdm-name'))) {

                        /** @var UploadedFile $uploadedFile */
                        $uploadedFile = $request->files->get('hdm-image');

                        if ($uploadedFile) {
                            $newFileName = $uploadHelper->uploadImage($uploadedFile, 'hdms');
                            $hdm->setImgName($newFileName);
                        }

                        $hdm->setName($data->get('hdm-name'));
                        $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste přidali hdm');
                        break;
                    }
                    $badge = null;
                    $logger
                        ->setOperation("Name of hdm was empty, name gotta be filled before adding.")
                        ->setType(LOGGER_TYPE_FAILED);
                    $this->addFlash(FLASH_DANGER, 'Není vyplněno políčko Název hdm, prosím založte hdm znovu se správnými hodnotami');
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
            $logger->setOperation($hdm->getName());
        }
        if (empty($logger->getType())) {
            $logger->setType(LOGGER_TYPE_SUCCESS);
        }

        $logger
                ->setModule($this->getModuleName(MODULE_HDMS))
                ->setUser($this->getUser())
                ->setUserName($this->getUser()->getFirstName());

        $this->em->persist($logger);
        if ($hdm) {
            $this->em->persist($hdm);
        }
        $this->em->flush();

        return $this->redirectToRoute('editor_users_hdms');
    }

    /**
     * @Route("/editor-edit/{id}/news-user-hdm", name="editor_edit_user_hdm", methods="POST")
     */
    public function editUserHdm(Hdm $hdm, Request $request, UploadHelper $uploadHelper)
    {
        $logger = new Log();
        $data = $request->request;
        $logger->setAction($data->get('hdm-action'));

        switch ($data->get('hdm-action')) {
            case 'edit':
                if (!empty($data->get('hdm-name'))) {
                    /** @var UploadedFile $uploadedFile */
                    $uploadedFile = $request->files->get('hdm-image');

                    if ($uploadedFile) {
                        $newFileName = $uploadHelper->uploadImage($uploadedFile, 'hdms', $hdm->getImgName());
                        $hdm->setImgName($newFileName);
                    }

                    $hdm->setName($data->get('hdm-name'));
                    $this->addFlash(FLASH_SUCCESS, 'Úspěšně jste aktualizovali hdm');
                    break;
                }
                $this->addFlash(FLASH_DANGER, 'Není vyplněno políčko Název hdm, prosím upravte hdm znovu se správnými hodnotami');
                $logger
                        ->setType(LOGGER_TYPE_FAILED)
                        ->setOperation("Name of hdm was empty, name gotta be filled before editing");
                break;

            case 'hide':
                if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
                    $hdm->hide();
                    $this->addFlash(FLASH_WARNING, 'Skrili jste hdm');
                    break;
                }
                $this->addFlash(FLASH_DANGER, NO_RIGHTS);
                $logger
                        ->setOperation(NO_RIGHTS)
                        ->setType(LOGGER_TYPE_FAILED);
                break;

            case 'show':
                if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
                    $hdm->show();
                    $this->addFlash(FLASH_SUCCESS, 'Zviditelnili jste badge');
                    break;
                }
                $this->addFlash(FLASH_DANGER, NO_RIGHTS);
                $logger
                        ->setOperation(NO_RIGHTS)
                        ->setType(LOGGER_TYPE_FAILED);
                break;

            case 'remove':
                if ($this->isGranted(SUPER_ADMIN) || $this->isGranted(EDITOR)) {
                    $this->em->remove($hdm);
                    $this->addFlash(FLASH_WARNING, 'Odstranili jste hdm');
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
            $logger->setOperation($hdm->getName()." [ ".$hdm->getId()." ] ");
        }
        if (empty($logger->getType())) {
            $logger->setType(LOGGER_TYPE_SUCCESS);
        }

        $logger
                ->setModule($this->getModuleName(MODULE_HDMS))
                ->setUser($this->getUser())
                ->setUserName($this->getUser()->getFirstName());

        $this->em->persist($logger);
        $this->em->flush();

        return $this->redirectToRoute('editor_users_hdms');
    }
}
