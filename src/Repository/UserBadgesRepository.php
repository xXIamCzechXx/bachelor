<?php

namespace App\Repository;

use App\Entity\UserBadges;
use App\Repository\Globals\Queries;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserBadges|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserBadges|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserBadges[]    findAll()
 * @method UserBadges[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserBadgesRepository extends ServiceEntityRepository
{
    use Queries;
    const ALIAS = 'ub';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBadges::class);
    }

    private function getOrCreateQueryBuilder(QueryBuilder $qb = null): QueryBuilder
    {
        return $qb ?: $this->createQueryBuilder(self::ALIAS);
    }
}
