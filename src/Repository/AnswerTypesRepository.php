<?php

namespace App\Repository;

use App\Entity\AnswerTypes;
use App\Repository\Globals\Queries;
use App\Repository\Globals\Queries2;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AnswerTypes|null find($id, $lockMode = null, $lockVersion = null)
 * @method AnswerTypes|null findOneBy(array $criteria, array $orderBy = null)
 * @method AnswerTypes[]    findAll()
 * @method AnswerTypes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnswerTypesRepository extends ServiceEntityRepository
{
    use Queries;
    const ALIAS = 'at';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnswerTypes::class);
    }

    public function getOrCreateQueryBuilder(QueryBuilder $qb = null): QueryBuilder
    {
        return $qb ?: $this->createQueryBuilder(self::ALIAS);
    }
}
