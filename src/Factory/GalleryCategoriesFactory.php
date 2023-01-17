<?php

namespace App\Factory;

use App\Entity\GalleryCategories;
use App\Repository\GalleryCategoriesRepository;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @extends ModelFactory<GalleryCategories>
 *
 * @method static GalleryCategories|Proxy createOne(array $attributes = [])
 * @method static GalleryCategories[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static GalleryCategories|Proxy find(object|array|mixed $criteria)
 * @method static GalleryCategories|Proxy findOrCreate(array $attributes)
 * @method static GalleryCategories|Proxy first(string $sortedField = 'id')
 * @method static GalleryCategories|Proxy last(string $sortedField = 'id')
 * @method static GalleryCategories|Proxy random(array $attributes = [])
 * @method static GalleryCategories|Proxy randomOrCreate(array $attributes = [])
 * @method static GalleryCategories[]|Proxy[] all()
 * @method static GalleryCategories[]|Proxy[] findBy(array $attributes)
 * @method static GalleryCategories[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static GalleryCategories[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static GalleryCategoriesRepository|RepositoryProxy repository()
 * @method GalleryCategories|Proxy create(array|callable $attributes = [])
 */
final class GalleryCategoriesFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();

        // TODO inject services if required (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services)
    }

    protected function getDefaults(): array
    {
        return [
            // TODO add your default values here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories)
            'name' => self::faker()->name,
            'slug' => self::faker()->slug,
            'color' => self::faker()->hexColor(),
            'title' => 'Kategorie',
            'keywords' => 'Virtuální realita, VR, Beat Saber, Pavlov VR, HTC, PlayZONE, Turnaj, Turnaje',
            'meta_description' => 'Jsme parta kluků a taťka, co se snaží prosadit VR a turnaje ve Virtuální realitě mezi lidmi. Specializujeme se hlavně na Beat Saber a začínáme i s hrou Pavlov VR, tak se přidej i ty!',
            'heading' => 'Daná kategorie'
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(GalleryCategories $galleryCategories): void {})
        ;
    }

    protected static function getClass(): string
    {
        return GalleryCategories::class;
    }
}
