<?php

namespace App\Repository;

use App\Entity\TournamentsScores;
use App\Entity\User;
use App\Repository\Globals\Queries;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TournamentsScores|null find($id, $lockMode = null, $lockVersion = null)
 * @method TournamentsScores|null findOneBy(array $criteria, array $orderBy = null)
 * @method TournamentsScores[]    findAll()
 * @method TournamentsScores[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournamentsScoresRepository extends ServiceEntityRepository
{
    use Queries;
    const ALIAS = 'ts';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TournamentsScores::class);
    }

    private function getOrCreateQueryBuilder(QueryBuilder $qb = null): QueryBuilder
    {
        return $qb ?: $this->createQueryBuilder(self::ALIAS);
    }
}
