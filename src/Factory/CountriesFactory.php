<?php

namespace App\Factory;

use App\Entity\Countries;
use App\Repository\CountriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @extends ModelFactory<Countries>
 *
 * @method static Countries|Proxy createOne(array $attributes = [])
 * @method static Countries[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Countries|Proxy find(object|array|mixed $criteria)
 * @method static Countries|Proxy findOrCreate(array $attributes)
 * @method static Countries|Proxy first(string $sortedField = 'id')
 * @method static Countries|Proxy last(string $sortedField = 'id')
 * @method static Countries|Proxy random(array $attributes = [])
 * @method static Countries|Proxy randomOrCreate(array $attributes = [])
 * @method static Countries[]|Proxy[] all()
 * @method static Countries[]|Proxy[] findBy(array $attributes)
 * @method static Countries[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static Countries[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static CountriesRepository|RepositoryProxy repository()
 * @method Countries|Proxy create(array|callable $attributes = [])
 */
final class CountriesFactory extends ModelFactory
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->country(),
        ];
    }

    protected function initialize(): self
    {
        return $this
            ->afterInstantiate(function(Countries $countries)
            {
                if(false == $this->em->getRepository(Countries::class)->findOneBy(['name' => 'Jiné'])) {
                    $countries->setName('Jiné');
                } else if(false == $this->em->getRepository(Countries::class)->findOneBy(['name' => 'Česká Republika'])) {
                    $countries->setName('Česká Republika');
                } else if(false == $this->em->getRepository(Countries::class)->findOneBy(['name' => 'Slovensko'])) {
                    $countries->setName('Slovensko');
                }
            })
        ;
    }

    protected static function getClass(): string
    {
        return Countries::class;
    }
}
