<?php

namespace App\Repository;

use App\Entity\NewsCategories;
use App\Repository\Globals\Queries;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NewsCategories|null find($id, $lockMode = null, $lockVersion = null)
 * @method NewsCategories|null findOneBy(array $criteria, array $orderBy = null)
 * @method NewsCategories[]    findAll()
 * @method NewsCategories[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsCategoriesRepository extends ServiceEntityRepository
{
    use Queries;
    const ALIAS = 'nc';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsCategories::class);
    }

    private function getOrCreateQueryBuilder(QueryBuilder $qb = null): QueryBuilder
    {
        return $qb ?: $this->createQueryBuilder(self::ALIAS);
    }
}
