<?php

namespace App\Repository;

use App\Entity\Tournaments;
use App\Repository\Globals\Queries;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tournaments|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tournaments|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tournaments[]    findAll()
 * @method Tournaments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournamentsRepository extends ServiceEntityRepository
{
    use Queries;
    const ALIAS = 't';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tournaments::class);
    }

    public function getOrCreateQueryBuilder(QueryBuilder $qb = null): QueryBuilder
    {
        return $qb ?: $this->createQueryBuilder(self::ALIAS);
    }
}
