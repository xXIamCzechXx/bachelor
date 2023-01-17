<?php

namespace App\Factory;

use App\Entity\AnswerTypes;
use App\Entity\FormAnswers;
use App\Entity\User;
use App\Repository\FormAnswersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @extends ModelFactory<FormAnswers>
 *
 * @method static FormAnswers|Proxy createOne(array $attributes = [])
 * @method static FormAnswers[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static FormAnswers|Proxy find(object|array|mixed $criteria)
 * @method static FormAnswers|Proxy findOrCreate(array $attributes)
 * @method static FormAnswers|Proxy first(string $sortedField = 'id')
 * @method static FormAnswers|Proxy last(string $sortedField = 'id')
 * @method static FormAnswers|Proxy random(array $attributes = [])
 * @method static FormAnswers|Proxy randomOrCreate(array $attributes = [])
 * @method static FormAnswers[]|Proxy[] all()
 * @method static FormAnswers[]|Proxy[] findBy(array $attributes)
 * @method static FormAnswers[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static FormAnswers[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static FormAnswersRepository|RepositoryProxy repository()
 * @method FormAnswers|Proxy create(array|callable $attributes = [])
 */
final class FormAnswersFactory extends ModelFactory
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->firstName(),
            'email' => self::faker()->email(),
            'phone' => self::faker()->phoneNumber(),
            'content' => self::faker()->realText(100),
        ];
    }

    protected function initialize(): self
    {
        return $this
            ->afterInstantiate(function(FormAnswers $formAnswers) {
                if($answerType = $this->em->getRepository(AnswerTypes::class)->findOneBy(['name' => 'Jiné'])) {
                    $formAnswers->setAnswerTypes($answerType);
                }
                if($user = $this->em->getRepository(User::class)->findOneBy(['firstName' => 'admin'])) {
                    $formAnswers->setUser($user);
                }
            })
        ;
    }

    protected static function getClass(): string
    {
        return FormAnswers::class;
    }
}
