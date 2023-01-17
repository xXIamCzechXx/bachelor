<?php

namespace App\Repository;

use App\Entity\TournamentsMaps;
use App\Repository\Globals\Queries;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TournamentsMaps|null find($id, $lockMode = null, $lockVersion = null)
 * @method TournamentsMaps|null findOneBy(array $criteria, array $orderBy = null)
 * @method TournamentsMaps[]    findAll()
 * @method TournamentsMaps[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournamentsMapsRepository extends ServiceEntityRepository
{
    use Queries;
    const ALIAS = 'tm';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TournamentsMaps::class);
    }

    private function getOrCreateQueryBuilder(QueryBuilder $qb = null): QueryBuilder
    {
        return $qb ?: $this->createQueryBuilder(self::ALIAS);
    }
}
