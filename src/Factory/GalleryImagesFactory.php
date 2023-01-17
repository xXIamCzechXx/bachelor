<?php

namespace App\Factory;

use App\Entity\GalleryImages;
use App\Repository\GalleryImagesRepository;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @extends ModelFactory<GalleryImages>
 *
 * @method static GalleryImages|Proxy createOne(array $attributes = [])
 * @method static GalleryImages[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static GalleryImages|Proxy find(object|array|mixed $criteria)
 * @method static GalleryImages|Proxy findOrCreate(array $attributes)
 * @method static GalleryImages|Proxy first(string $sortedField = 'id')
 * @method static GalleryImages|Proxy last(string $sortedField = 'id')
 * @method static GalleryImages|Proxy random(array $attributes = [])
 * @method static GalleryImages|Proxy randomOrCreate(array $attributes = [])
 * @method static GalleryImages[]|Proxy[] all()
 * @method static GalleryImages[]|Proxy[] findBy(array $attributes)
 * @method static GalleryImages[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static GalleryImages[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static GalleryImagesRepository|RepositoryProxy repository()
 * @method GalleryImages|Proxy create(array|callable $attributes = [])
 */
final class GalleryImagesFactory extends ModelFactory
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
            'img_name' => '',
            'alt' => 'Beat Saber, Pavlov VR a VR E-sport',
            'name' => '',
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(GalleryImages $galleryImages): void {})
        ;
    }

    protected static function getClass(): string
    {
        return GalleryImages::class;
    }
}
