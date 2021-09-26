<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Offer;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Smart\CoreBundle\Doctrine\RepositoryTrait;

class OfferRepository extends ServiceEntityRepository
{
    use RepositoryTrait\FindByQuery;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offer::class);
    }

    public function getFindQueryBuilder(array $filters = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('e');

        if (!empty($filters['category']) and $filters['category'] >= 1) {
            $qb->andWhere('e.category = :category');
            $qb->setParameter('category', $filters['category']);
        }

        if (!empty($filters['city']) and $filters['city'] >= 1) {
            $qb->andWhere('e.city = :city');
            $qb->setParameter('city', $filters['city']);
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

    public function countAvailableByUser(User $user): int
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('COUNT(e.id)')
            ->where('e.user = :user')
            ->andWhere('e.is_enabled = true')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('e.status', ':status_available'),
                $qb->expr()->eq('e.status', ':status_on_demand'),
            ))
            ->setParameter('user', $user)
            ->setParameter('status_available', Offer::STATUS_AVAILABLE)
            ->setParameter('status_on_demand', Offer::STATUS_ON_DEMAND)
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
