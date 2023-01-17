<?php

namespace App\Factory;

use App\Entity\NewsCategories;
use App\Repository\NewsCategoriesRepository;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @extends ModelFactory<NewsCategories>
 *
 * @method static NewsCategories|Proxy createOne(array $attributes = [])
 * @method static NewsCategories[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static NewsCategories|Proxy find(object|array|mixed $criteria)
 * @method static NewsCategories|Proxy findOrCreate(array $attributes)
 * @method static NewsCategories|Proxy first(string $sortedField = 'id')
 * @method static NewsCategories|Proxy last(string $sortedField = 'id')
 * @method static NewsCategories|Proxy random(array $attributes = [])
 * @method static NewsCategories|Proxy randomOrCreate(array $attributes = [])
 * @method static NewsCategories[]|Proxy[] all()
 * @method static NewsCategories[]|Proxy[] findBy(array $attributes)
 * @method static NewsCategories[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static NewsCategories[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static NewsCategoriesRepository|RepositoryProxy repository()
 * @method NewsCategories|Proxy create(array|callable $attributes = [])
 */
final class NewsCategoriesFactory extends ModelFactory
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
            'name' => self::faker()->streetName(),
            'description' => self::faker()->realText(154),
            'color' => self::faker()->hexColor,
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(NewsCategories $newsCategories): void {})
        ;
    }

    protected static function getClass(): string
    {
        return NewsCategories::class;
    }
}
