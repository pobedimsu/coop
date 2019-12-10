<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Smart\CoreBundle\Doctrine\RepositoryTrait;

class OfferRepository extends EntityRepository
{
    use RepositoryTrait\FindByQuery;

    /**
     * @param array $filters
     *
     * @return QueryBuilder
     */
    public function getFindQueryBuilder(array $filters = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');

        if (!empty($filters['category']) and $filters['category'] >= 1) {
            $qb->andWhere('c.category = :category');
            $qb->setParameter('category', $filters['category']);
        }

        if (!empty($filters['search']) and strlen($filters['search']) >= 3) {
            $qb->andWhere('c.title LIKE :search');
            $qb->setParameter('search', '%'.$filters['search'].'%');
        }

        return $qb;
    }
}
