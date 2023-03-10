<?php

namespace App\Controller;

use App\Entity\Countries;
use App\Entity\Hdm;
use App\Entity\Pages;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use App\Service\UploadHelper;
use App\Service\UserNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends BaseController
{
    /**
     * @Route("/connexion", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, LoginFormAuthenticator $authenticator, Request $request): Response
    {
        if ($this->getUser()) { return $this->redirectToRoute('editor'); }

        if(!$page = $this->em->getRepository(Pages::class)->findOneBy(['name' => 'app_login'])) {
            throw $this->createNotFoundException();
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'page' => $page,
        ]);

    }

    /**
     * @Route("/registrace", name="app_register")
     */
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, GuardAuthenticatorHandler $guard, LoginFormAuthenticator $authenticator, UserNormalizer $userNormalizer): Response
    {
        if (null != $this->getUser()) {
            return new RedirectResponse($this->generateUrl('account'));
        }

        if(!$page = $this->em->getRepository(Pages::class)->findOneBy(['name' => 'app_register'])) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);
        $data = $request->request;

        if($form->isSubmitted() && $form->isValid()) {

            /** @var User $user */
            $user = $form->getData();
            $Return = $this->getCaptcha($data->get("g-recaptcha-response"));

            if($Return->success == true && $Return->score > 0.5) {

                if ($user->getPassword()==$user->getPasswordRepeat()) {

                    if($this->em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()])) {
                        $this->addFlash(FLASH_DANGER, 'U??ivatel s t??mto emailem ji?? existuje');
                    } elseif ($this->em->getRepository(User::class)->findOneBy(['nickname' => $user->getNickname()])) {
                        $this->addFlash(FLASH_DANGER, 'U??ivatel s t??mto nickname ji?? existuje');
                    } else {

                        if (strlen($user->getNickname()) <= 5 || strlen($user->getNickname()) >= 21) {
                            $this->addFlash(FLASH_DANGER, 'Nickname mus?? b??t v rozmez?? 5 a?? 20 znak??');
                        } elseif (strlen($user->getFirstName()) <= 5 || strlen($user->getFirstName()) >= 21) {
                            $this->addFlash(FLASH_DANGER, 'Jm??no a p????jmen?? mus?? b??t v rozmez?? 5 a?? 20 znak??');
                        } elseif (!$this->hasUpperCase($user->getPassword()) || !$this->hasLowerCase($user->getPassword()) || !$this->hasNumber($user->getPassword()) || strlen($user->getPassword()) < 8) {
                            $this->addFlash(FLASH_DANGER, 'Heslo mus?? obsahovat minim??ln?? 8 znak??, jedno velk?? a mal?? pismeno a ????slo');
                        } elseif (!empty($user->getDiscordNickname()) && (!$this->hasNumber($data->get('discord-nickname')) || !strpos($data->get('discord-nickname'), '#'))) {
                            $this->addFlash(FLASH_DANGER, 'Neplatn?? discord nickname (mus?? obsahovat jm??no#xxxx, kde x jsou ????sla), pokud nev??te, nechte pr??zdn??.');
                        } else {

                            $user
                                ->setPassword($passwordHasher->hashPassword($user, $user->getPassword()))
                                ->setPasswordRepeat('OK')
                                ->setUserIp($this->container->get('request_stack')->getMasterRequest()->getClientIp())
                                ->setUniqueId($userNormalizer->generateUniquePlayerId())
                                ->setScoresaberLink(!empty($user->getScoresaberId()) ? 'https://scoresaber.com/u/'.$user->getScoresaberId() : null)
                                ->setTwitchLink(!empty($user->getTwitchNickname()) ? 'https://www.twitch.tv/'.$user->getTwitchNickname() : null);

                            $this->em->persist($user);
                            $this->em->flush();

                            $this->addFlash(FLASH_SUCCESS, 'Registrace prob??hla ??sp????n??');
                            return $this->redirectToRoute('app_login');
                        }
                    }

                } else {
                    $this->addFlash(FLASH_DANGER, 'Hesla se mus?? shodovat');
                }
            } else {
                $this->addFlash(FLASH_DANGER, 'Va??e registrace byla vyhodnocena jako spam');
            }
        }
        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
            'page' => $page,
        ]);
    }

    protected function getCaptcha($SecretKey)
    {
        $Response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$this->translator->getValue('SECRET_KEY')."&response={$SecretKey}");
        $Return = json_decode($Response);
        return $Return;
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        //throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

}
