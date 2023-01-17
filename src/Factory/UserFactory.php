<?php

namespace App\Factory;

use App\Entity\ApiToken;
use App\Entity\Countries;
use App\Entity\News;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @extends ModelFactory<User>
 *
 * @method static User|Proxy createOne(array $attributes = [])
 * @method static User[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static User|Proxy find(object|array|mixed $criteria)
 * @method static User|Proxy findOrCreate(array $attributes)
 * @method static User|Proxy first(string $sortedField = 'id')
 * @method static User|Proxy last(string $sortedField = 'id')
 * @method static User|Proxy random(array $attributes = [])
 * @method static User|Proxy randomOrCreate(array $attributes = [])
 * @method static User[]|Proxy[] all()
 * @method static User[]|Proxy[] findBy(array $attributes)
 * @method static User[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static User[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static UserRepository|RepositoryProxy repository()
 * @method User|Proxy create(array|callable $attributes = [])
 */
final class UserFactory extends ModelFactory
{
    public function __construct(UserPasswordHasherInterface $passwordEncoder, EntityManagerInterface $em)
    {
        parent::__construct();

        $this->passwordEncoder = $passwordEncoder;
        $this->em = $em;

        // TODO inject services if required (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services)
    }

    protected function getDefaults(): array
    {
        $user = new User();

        return [
            'email' => self::faker()->email(),
            'firstName' => self::faker()->userName(),
            'nickname' => self::faker()->firstName(),
            'user_ip' => self::faker()->ipv4(),
            'gdpr' => 1,
            'password' => $this->passwordEncoder->hashPassword($user, 'admin123'),
        ];
    }

    protected function initialize(): self
    {
        return $this
            ->afterInstantiate(function(User $user) {
                if ($country = $this->em->getRepository(Countries::class)->findOneBy(['name' => 'Česká Republika'])) {
                    $user->setCountry($country);
                }
                if (null === $this->em->getRepository(User::class)->findOneBy(['nickname' => 'xXIamCzechXx']))
                {
                    $user
                        ->setEmail('xXIamCzechXx@gmail.com')
                        ->setNickname('xXIamCzechXx')
                        ->setFirstName('Dominik Mach')
                        ->setGdpr(1)
                        ->setUserIp(self::faker()->ipv4())
                        ->setPassword($this->passwordEncoder->hashPassword($user, 'dominik123'))
                        ->setCountry($this->em->getRepository(Countries::class)->findOneBy(['name' => 'Česká republika']))
                        ->setRoles(['ROLE_SUPER_ADMIN'])
                    ;
                } else if (null === $this->em->getRepository(User::class)->findOneBy(['nickname' => 'admin']))
                {
                    $user
                        ->setEmail('admin@admin.cz')
                        ->setNickname('admin')
                        ->setFirstName('admin')
                        ->setGdpr(1)
                        ->setUserIp(self::faker()->ipv4())
                        ->setPassword($this->passwordEncoder->hashPassword($user, 'admin123'))
                        ->setCountry($this->em->getRepository(Countries::class)->findOneBy(['name' => 'Česká republika']))
                        ->setRoles(['ROLE_ADMIN'])
                    ;
                } else
                {
                    $user
                        ->setRoles(['ROLE_USER'])
                        ->setCountry($this->em->getRepository(Countries::class)->findOneBy(['name' => 'Jiné']))
                    ;
                }
            })
        ;
    }

    protected static function getClass(): string
    {
        return User::class;
    }
}
