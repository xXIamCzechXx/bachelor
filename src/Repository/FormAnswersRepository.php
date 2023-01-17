<?php

namespace App\Repository;

use App\Entity\FormAnswers;
use App\Repository\Globals\Queries;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FormAnswers|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormAnswers|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormAnswers[]    findAll()
 * @method FormAnswers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormAnswersRepository extends ServiceEntityRepository
{
    use Queries;
    const ALIAS = 'fa';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormAnswers::class);
    }

    public function getOrCreateQueryBuilder(QueryBuilder $qb = null): QueryBuilder
    {
        return $qb ?: $this->createQueryBuilder(self::ALIAS);
    }
}
