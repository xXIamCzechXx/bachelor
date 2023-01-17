<?php

namespace App\Factory;

use App\Entity\News;
use App\Entity\User;
use App\Repository\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @extends ModelFactory<News>
 *
 * @method static News|Proxy createOne(array $attributes = [])
 * @method static News[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static News|Proxy find(object|array|mixed $criteria)
 * @method static News|Proxy findOrCreate(array $attributes)
 * @method static News|Proxy first(string $sortedField = 'id')
 * @method static News|Proxy last(string $sortedField = 'id')
 * @method static News|Proxy random(array $attributes = [])
 * @method static News|Proxy randomOrCreate(array $attributes = [])
 * @method static News[]|Proxy[] all()
 * @method static News[]|Proxy[] findBy(array $attributes)
 * @method static News[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static News[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static NewsRepository|RepositoryProxy repository()
 * @method News|Proxy create(array|callable $attributes = [])
 */
final class NewsFactory extends ModelFactory
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
            'title' => self::faker()->realText(52),
            'content' => self::faker()->text(1200),
            'view' => 1,
            'heading' => 'Czech Saber - Největší turnaje v Beat Saber a Pavlov VR',
            'instagram_token' => 'IGQVJYd0VPZA3YxcDgwN1Jwdjl1ZA3BjX1pQeDdqY1pHaVVsZA0JqNHlzWTBFZAGRxLXZAROFpwMmUxV18wQXNTaVU5QWw5SUlkaGJTOGdYaFdKUExQT3ZAEZAVNKVC15ZAkZAGNl9vZAU96bFJoZAWxNMmoxY2dhYgZDZD',
            'keywords' => 'Virtuální realita, VR, Beat Saber, Pavlov VR, HTC, PlayZONE, Turnaj, Turnaje',
            'meta_description' => 'Jsme parta kluků a taťka, co se snaží prosadit VR a turnaje ve Virtuální realitě mezi lidmi. Specializujeme se hlavně na Beat Saber a začínáme i s hrou Pavlov VR, tak se přidej i ty!',
            //'added_at' => self::faker()->dateTimeBetween('now', 'now'),
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            ->afterInstantiate(function(News $news)
            {
                if($user = $this->em->getRepository(User::class)->findOneBy(['firstName' => 'admin'])) {
                    $news->setAuthor($user);
                }

                //if(!$news->getSlug()) {
                //    $slugger = new AsciiSlugger();
                //    $news->setSlug($slugger->slug($news->getTitle()));
                //}
            })
            ;
    }

    protected static function getClass(): string
    {
        return News::class;
    }
}
