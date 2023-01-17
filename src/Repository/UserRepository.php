<?php

namespace App\Repository;

use App\Entity\User;
use App\Repository\Globals\Queries;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    use Queries;
    const ALIAS = 'u';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return User[] Returns an array of Admin users
     */
    public function findAdmins()
    {
        return $this->getOrCreateQueryBuilder()
            ->orderBy('u.id', 'DESC')
            //->andWhere('JSON_CONTAINS(u.roles, :role) = 1')
            //->setParameter('role', '"ROLE_ADMIN"')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return User[] Returns an array of Users with limit of objects
     */
    public function findUsersWithLimit($limit)
    {
        return $this->getOrCreateQueryBuilder()
            ->orderBy('u.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
    /**
     * @return User[] Returns an array of Users with limit of objects
     */
    public function findAllSortByPercentage($limit)
    {
        return $this->getOrCreateQueryBuilder()
            ->join('u.tournaments', 'u_tournaments')
            ->orderBy('u.avgPercentage', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return User[] Returns an array of Users with limit of objects
     */
    public function findVisibleUsersWithLimit($limit = 1000, $fResult = 0)
    {
        return $this->getOrCreateQueryBuilder()
            ->addOrderBy('u.loggedAt', 'DESC')
            ->addOrderBy('u.id', 'ASC')
            ->andWhere('u.gdpr = 1')
            ->andWhere('u.active = 1')
            ->setMaxResults($limit)
            ->setFirstResult($fResult)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getOrCreateQueryBuilder(QueryBuilder $qb = null): QueryBuilder
    {
        return $qb ?: $this->createQueryBuilder(self::ALIAS);
    }
}
