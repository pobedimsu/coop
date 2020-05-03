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

    public function getFindQueryBuilder(array $filters = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('e');

        if (!empty($filters['category']) and $filters['category'] >= 1) {
            $qb->andWhere('e.category = :category');
            $qb->setParameter('category', $filters['category']);
        }

        if (!empty($filters['search']) and strlen($filters['search']) >= 3) {
            $qb->andWhere('e.title LIKE :search');
            $qb->setParameter('search', '%'.$filters['search'].'%');
        }

        if (isset($filters['is_enabled']) and is_bool($filters['is_enabled'])) {
            $qb->andWhere('e.is_enabled = :is_enabled');
            $qb->setParameter('is_enabled', $filters['is_enabled']);
        }

        $qb->orderBy('e.created_at', 'DESC');

        return $qb;
    }
}
