<?php

namespace App\Repository;

use App\Entity\Constants;
use App\Repository\Globals\Queries;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Constants|null find($id, $lockMode = null, $lockVersion = null)
 * @method Constants|null findOneBy(array $criteria, array $orderBy = null)
 * @method Constants[]    findAll()
 * @method Constants[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConstantsRepository extends ServiceEntityRepository
{
    use Queries;
    const ALIAS = 'c';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Constants::class);
    }

    public function getOrCreateQueryBuilder(QueryBuilder $qb = null): QueryBuilder
    {
        return $qb ?: $this->createQueryBuilder(self::ALIAS);
    }
}
