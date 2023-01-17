<?php

namespace App\Factory;

use App\Entity\AnswerTypes;
use App\Repository\AnswerTypesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @extends ModelFactory<AnswerTypes>
 *
 * @method static AnswerTypes|Proxy createOne(array $attributes = [])
 * @method static AnswerTypes[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static AnswerTypes|Proxy find(object|array|mixed $criteria)
 * @method static AnswerTypes|Proxy findOrCreate(array $attributes)
 * @method static AnswerTypes|Proxy first(string $sortedField = 'id')
 * @method static AnswerTypes|Proxy last(string $sortedField = 'id')
 * @method static AnswerTypes|Proxy random(array $attributes = [])
 * @method static AnswerTypes|Proxy randomOrCreate(array $attributes = [])
 * @method static AnswerTypes[]|Proxy[] all()
 * @method static AnswerTypes[]|Proxy[] findBy(array $attributes)
 * @method static AnswerTypes[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static AnswerTypes[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static AnswerTypesRepository|RepositoryProxy repository()
 * @method AnswerTypes|Proxy create(array|callable $attributes = [])
 */
final class AnswerTypesFactory extends ModelFactory
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->name(),
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            ->afterInstantiate(function(AnswerTypes $answerTypes) {
                if($this->em->getRepository(AnswerTypes::class)->findOneBy(['name' => 'Jiné']) == false) {
                    $answerTypes->setName('Jiné');
                } elseif ($this->em->getRepository(AnswerTypes::class)->findOneBy(['name' => 'Recenze']) == false) {
                    $answerTypes->setName('Recenze');
                } elseif ($this->em->getRepository(AnswerTypes::class)->findOneBy(['name' => 'Reklamace']) == false) {
                    $answerTypes->setName('Reklamace');
                } elseif ($this->em->getRepository(AnswerTypes::class)->findOneBy(['name' => 'Informace o mojí objednávce']) == false) {
                    $answerTypes->setName('Informace o mojí objednávce');
                }
            })
        ;
    }

    protected static function getClass(): string
    {
        return AnswerTypes::class;
    }
}
