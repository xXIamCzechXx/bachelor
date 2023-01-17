<?php

namespace App\Factory;

use App\Entity\Log;
use App\Entity\User;
use App\Repository\LogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @extends ModelFactory<Log>
 *
 * @method static Log|Proxy createOne(array $attributes = [])
 * @method static Log[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Log|Proxy find(object|array|mixed $criteria)
 * @method static Log|Proxy findOrCreate(array $attributes)
 * @method static Log|Proxy first(string $sortedField = 'id')
 * @method static Log|Proxy last(string $sortedField = 'id')
 * @method static Log|Proxy random(array $attributes = [])
 * @method static Log|Proxy randomOrCreate(array $attributes = [])
 * @method static Log[]|Proxy[] all()
 * @method static Log[]|Proxy[] findBy(array $attributes)
 * @method static Log[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static Log[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static LogRepository|RepositoryProxy repository()
 * @method Log|Proxy create(array|callable $attributes = [])
 */
final class LogFactory extends ModelFactory
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function getDefaults(): array
    {
        return [
            'operation' => self::faker()->text(120),
            'module' => 'Pages',
        ];
    }

    protected function initialize(): self
    {
        return $this
            ->afterInstantiate(function(Log $log) {
                if($user = $this->em->getRepository(User::class)->findOneBy(['firstName' => 'admin'])) {
                    $log->setUser($user);
                }
            })
        ;
    }

    protected static function getClass(): string
    {
        return Log::class;
    }
}
