<?php

namespace App\Repository;

use App\Entity\News;
use App\Repository\Globals\Queries;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method News|null find($id, $lockMode = null, $lockVersion = null)
 * @method News|null findOneBy(array $criteria, array $orderBy = null)
 * @method News[]    findAll()
 * @method News[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsRepository extends ServiceEntityRepository
{
    use Queries;
    const ALIAS = 'n';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, News::class);
    }

    /**
     * @return News[] Returns an array of Users with limit of objects
     */
    public function findVisible($limit = 3000, $fResult = 0)
    {
        return $this->getOrCreateQueryBuilder()
            ->addOrderBy('n.addedAt', 'DESC')
            ->addOrderBy('n.createdAt', 'DESC')
            ->addOrderBy('n.updatedAt', 'DESC')
            ->addOrderBy('n.title', 'DESC')
            ->andWhere('n.view = :num')
            ->setParameter('num', 1)
            ->setMaxResults($limit)
            ->setFirstResult($fResult)
            ->getQuery()
            ->getResult()
        ;
    }
    /**
     * @return News[] Returns an array of Users desc
     */
    public function findAllDesc($limit = 3000, $fResult = 0)
    {
        return $this->getOrCreateQueryBuilder()
            ->addOrderBy('n.addedAt', 'DESC')
            ->addOrderBy('n.createdAt', 'DESC')
            ->addOrderBy('n.updatedAt', 'DESC')
            ->addOrderBy('n.title', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($fResult)
            ->getQuery()
            ->getResult()
        ;
    }

    private function getOrCreateQueryBuilder(QueryBuilder $qb = null): QueryBuilder
    {
        return $qb ?: $this->createQueryBuilder(self::ALIAS);
    }
}
