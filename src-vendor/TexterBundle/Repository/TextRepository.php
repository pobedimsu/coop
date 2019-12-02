<?php

namespace SmartCore\Bundle\TexterBundle\Repository;

use Doctrine\ORM\EntityRepository;

class TextRepository extends EntityRepository
{
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindAllQuery()
    {
        return $this
            ->createQueryBuilder('t')
            ->orderBy('t.name', 'ASC')
            ->addOrderBy('t.id', 'ASC');
    }
}
