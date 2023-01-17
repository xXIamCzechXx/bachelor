<?php

namespace App\Repository;

use App\Entity\GalleryCategories;
use App\Repository\Globals\Queries;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GalleryCategories|null find($id, $lockMode = null, $lockVersion = null)
 * @method GalleryCategories|null findOneBy(array $criteria, array $orderBy = null)
 * @method GalleryCategories[]    findAll()
 * @method GalleryCategories[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GalleryCategoriesRepository extends ServiceEntityRepository
{
    use Queries;
    const ALIAS = 'gc';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GalleryCategories::class);
    }

    /**
     * @return GalleryCategories[] Returns an array of Users with limit of objects
     */
    public function findVisible($fResult = 0)
    {
        return $this->addCondition()
            ->addOrderBy('gc.createdAt', 'DESC')
            ->addOrderBy('gc.updatedAt', 'DESC')
            ->addOrderBy('gc.name', 'DESC')
            ->andWhere('gc.view = :num')
            ->setParameter('num', 1)
            ->setFirstResult($fResult)
            ->getQuery()
            ->getResult()
        ;
    }

    private function addCondition(QueryBuilder $qb = null): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder($qb);
    }

    private function getOrCreateQueryBuilder(QueryBuilder $qb = null): QueryBuilder
    {
        return $qb ?: $this->createQueryBuilder(self::ALIAS);
    }
}
