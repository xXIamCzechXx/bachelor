<?php

namespace App\Repository\Globals;


trait Queries
{
    public function findAllOrderBy($orderBy = 'id', $direction = 'DESC', $limit = 3000, $fResult = 0)
    {
        return $this->getOrCreateQueryBuilder()
            ->orderBy(self::ALIAS.'.'.$orderBy, $direction)
            ->setMaxResults($limit)
            ->setFirstResult($fResult)
            ->getQuery()
            ->getResult()
        ;
    }
}