<?php

namespace App\Repository;

use App\Entity\Hdm;
use App\Repository\Globals\Queries;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Hdm|null find($id, $lockMode = null, $lockVersion = null)
 * @method Hdm|null findOneBy(array $criteria, array $orderBy = null)
 * @method Hdm[]    findAll()
 * @method Hdm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HdmRepository extends ServiceEntityRepository
{
    use Queries;
    const ALIAS = 'h';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hdm::class);
    }

    private function getOrCreateQueryBuilder(QueryBuilder $qb = null): QueryBuilder
    {
        return $qb ?: $this->createQueryBuilder(self::ALIAS);
    }
}
