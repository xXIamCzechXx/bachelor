<?php

namespace App\Security;

use App\Controller\SecurityController;
use App\Entity\User;
use App\Service\UserNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';
    public const HOMEPAGE_ROUTE = 'homepage';
    public const EDITOR_ROUTE = 'editor';
    public const ACCOUNT_ROUTE = 'account';

    /** @var User */
    private $user;
    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->user = new User();
    }

    /**
     * {@inheritdoc}
     *
     * Override to change the request conditions that have to be
     * matched in order to handle the login form submit.
     *
     * This default implementation handles all POST requests to the
     * login path (@see getLoginUrl()).
     */
    public function supports(Request $request): bool
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function supportsClass($class)
    {
        return $class === UserInterface::class;
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'login' => $request->request->get('_login'),
            'password' => $request->request->get('_password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['login']
        );

        return $credentials;
    }

    public function getValidUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        if ($users = $this->entityManager->getRepository(User::class)->findAll()) {
            foreach ($users as $user) {
                if ($user->getEmail() == $credentials['login'] || $user->getNickname() == $credentials['login']) {
                    return $user;
                }
            }
        }

        return false;
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    public function authenticate(Request $request): PassportInterface
    {
        $email = $request->request->get('_login');
        $password = $request->request->get('_password');

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email, function($userIdentifier) {
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userIdentifier]);

                if (!$user) {
                    throw new UserNotFoundException();
                }
                $this->user = $user;

                return $user;
            }),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge(
                    'authenticate',
                    $request->request->get('_csrf_token')
                ),
                (new RememberMeBadge())->enable(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $this->user->setLaggedAt(new \DateTime());
        if (empty($this->user->getUserIdentifier())) {
            $normalizer = new UserNormalizer($this->entityManager);
            $this->user->setUniqueId($normalizer->generateUniquePlayerId());
        }
        $this->entityManager->persist($this->user);
        $this->entityManager->flush();

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate(self::ACCOUNT_ROUTE));
    }

    /**
     * Override to change what happens after a bad username/password is submitted.
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        }

        $url = $this->getLoginUrl($request);

        return new RedirectResponse($url);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }
}
