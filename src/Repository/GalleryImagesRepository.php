<?php

namespace App\Repository;

use App\Entity\Gallery;
use App\Entity\GalleryImages;
use App\Repository\Globals\Queries;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Gallery|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gallery|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gallery[]    findAll()
 * @method Gallery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GalleryImagesRepository extends ServiceEntityRepository
{
    use Queries;
    const ALIAS = 'ci';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GalleryImages::class);
    }

    /**
     * @return GalleryImages[] Returns an array of Users with limit of objects
     */
    public function findVisible($limit = 1000, $fResult = 0)
    {
        return $this->addCondition()
            ->addOrderBy('ci.createdAt', 'DESC')
            ->addOrderBy('ci.updatedAt', 'DESC')
            ->addOrderBy('ci.name', 'DESC')
            ->andWhere('ci.view = 1')
            ->setMaxResults($limit)
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
