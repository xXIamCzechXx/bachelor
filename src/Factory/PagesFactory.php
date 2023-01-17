<?php

namespace App\Factory;

use App\Entity\Countries;
use App\Entity\Pages;
use App\Entity\User;
use App\Repository\PagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @extends ModelFactory<Pages>
 *
 * @method static Pages|Proxy createOne(array $attributes = [])
 * @method static Pages[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Pages|Proxy find(object|array|mixed $criteria)
 * @method static Pages|Proxy findOrCreate(array $attributes)
 * @method static Pages|Proxy first(string $sortedField = 'id')
 * @method static Pages|Proxy last(string $sortedField = 'id')
 * @method static Pages|Proxy random(array $attributes = [])
 * @method static Pages|Proxy randomOrCreate(array $attributes = [])
 * @method static Pages[]|Proxy[] all()
 * @method static Pages[]|Proxy[] findBy(array $attributes)
 * @method static Pages[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static Pages[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static PagesRepository|RepositoryProxy repository()
 * @method Pages|Proxy create(array|callable $attributes = [])
 */
final class PagesFactory extends ModelFactory
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();

        $this->em = $em;
        // TODO inject services if required (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services)
    }

    protected function getDefaults(): array
    {
        return [
            // TODO add your default values here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories)
            'url' => self::faker()->slug(),
            'heading' => 'Czech Saber - Největší turnaje v Beat Saber a Pavlov VR',
            'instagram_token' => 'IGQVJYd0VPZA3YxcDgwN1Jwdjl1ZA3BjX1pQeDdqY1pHaVVsZA0JqNHlzWTBFZAGRxLXZAROFpwMmUxV18wQXNTaVU5QWw5SUlkaGJTOGdYaFdKUExQT3ZAEZAVNKVC15ZAkZAGNl9vZAU96bFJoZAWxNMmoxY2dhYgZDZD',
            'keywords' => 'Virtuální realita, VR, Beat Saber, Pavlov VR, HTC, PlayZONE, Turnaj, Turnaje',
            'meta_description' => 'Jsme parta kluků a taťka, co se snaží prosadit VR a turnaje ve Virtuální realitě mezi lidmi. Specializujeme se hlavně na Beat Saber a začínáme i s hrou Pavlov VR, tak se přidej i ty!',
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            ->afterInstantiate(function(Pages $pages): void {
                if (null === $this->em->getRepository(Pages::class)->findOneBy(['name' => 'homepage']))
                {
                    $pages
                        ->setName('homepage')
                        ->setTitle('Hlavní strana')
                        ->setUrl('/')
                    ;
                } else if (null === $this->em->getRepository(Pages::class)->findOneBy(['name' => 'gallery']))
                {
                    $pages
                        ->setName('gallery')
                        ->setTitle('Galerie')
                        ->setUrl('/gallerie')
                    ;
                } else if (null === $this->em->getRepository(Pages::class)->findOneBy(['name' => 'account']))
                {
                    $pages
                        ->setName('account')
                        ->setTitle('Účet')
                        ->setUrl('/ucet')
                    ;
                } else if (null === $this->em->getRepository(Pages::class)->findOneBy(['name' => 'app_login']))
                {
                    $pages
                        ->setName('app_login')
                        ->setTitle('Přihlášení')
                        ->setUrl('/prihlaseni')
                    ;
                } else if (null === $this->em->getRepository(Pages::class)->findOneBy(['name' => 'app_register']))
                {
                    $pages
                        ->setName('app_register')
                        ->setTitle('Registrace')
                        ->setUrl('/registrace')
                    ;
                } else if (null === $this->em->getRepository(Pages::class)->findOneBy(['name' => 'news']))
                {
                    $pages
                        ->setName('news')
                        ->setTitle('Články')
                        ->setUrl('/clanky')
                    ;
                } else if (null === $this->em->getRepository(Pages::class)->findOneBy(['name' => 'contact']))
                {
                    $pages
                        ->setName('contact')
                        ->setTitle('Kontakt')
                        ->setUrl('/kontakt')
                    ;
                } else if (null === $this->em->getRepository(Pages::class)->findOneBy(['name' => 'error404']))
                {
                    $pages
                        ->setName('error404')
                        ->setTitle('Error')
                        ->setUrl('/error404')
                    ;
                } else
                {
                    $pages
                        ->setName('modify me')
                    ;
                }
            })
        ;
    }

    protected static function getClass(): string
    {
        return Pages::class;
    }
}
