<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Deal;
use App\Entity\Offer;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Smart\CoreBundle\Doctrine\RepositoryTrait;

class DealRepository extends ServiceEntityRepository
{
    use RepositoryTrait\FindByQuery;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deal::class);
    }

    public function getfindActiveByUserQueryBuilder(User $user): QueryBuilder
    {
        $qb = $this->createQueryBuilder('e');
        $qb->where($qb->expr()->orX(
            $qb->expr()->eq('e.seller', ':user'),
            $qb->expr()->eq('e.buyer', ':user')
        ));
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq('e.status', ':status_new'),
            $qb->expr()->eq('e.status', ':status_view'),
            $qb->expr()->eq('e.status', ':status_accepted'),
            $qb->expr()->eq('e.status', ':status_accepted_external')
        ));
        $qb->setParameter('user', $user)
            ->setParameter('status_new', Deal::STATUS_NEW)
            ->setParameter('status_view', Deal::STATUS_VIEW)
            ->setParameter('status_accepted', Deal::STATUS_ACCEPTED)
            ->setParameter('status_accepted_external', Deal::STATUS_ACCEPTED_EXTERNAL)
        ;

        return $qb;
    }

    /**
     * @return Deal[]
     */
    public function findActiveByUser(User $user): array
    {
        $qb = $this->getfindActiveByUserQueryBuilder($user);
        $qb->orderBy('e.updated_at', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function countActiveByUser(User $user): int
    {
        $qb = $this->getfindActiveByUserQueryBuilder($user);
        $qb->select('COUNT(e.id)');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return Deal[]
     */
    public function findNewByUser(User $user): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->where($qb->expr()->orX(
            $qb->expr()->eq('e.seller', ':user'),
            $qb->expr()->eq('e.buyer', ':user')
        ));
        $qb->andWhere('e.viewed_at IS NULL');
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq('e.status', ':status_new'),
            $qb->expr()->eq('e.status', ':status_view'),
            $qb->expr()->eq('e.status', ':status_accepted'),
            $qb->expr()->eq('e.status', ':status_accepted_external')
        ));
        $qb->orderBy('e.updated_at', 'DESC')
            ->setParameter('user', $user)
            ->setParameter('status_new', Deal::STATUS_NEW)
            ->setParameter('status_view', Deal::STATUS_VIEW)
            ->setParameter('status_accepted', Deal::STATUS_ACCEPTED)
            ->setParameter('status_accepted_external', Deal::STATUS_ACCEPTED_EXTERNAL)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Deal[]
     */
    public function countNewByUser(User $user): int
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('COUNT(e.id)');
        $qb->where($qb->expr()->orX(
            $qb->expr()->eq('e.seller', ':user'),
            $qb->expr()->eq('e.buyer', ':user')
        ));
        $qb->andWhere('e.viewed_at IS NULL');
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq('e.status', ':status_new'),
            $qb->expr()->eq('e.status', ':status_view'),
            $qb->expr()->eq('e.status', ':status_accepted'),
            $qb->expr()->eq('e.status', ':status_accepted_external')
        ));
        $qb->setParameter('user', $user)
            ->setParameter('status_new', Deal::STATUS_NEW)
            ->setParameter('status_view', Deal::STATUS_VIEW)
            ->setParameter('status_accepted', Deal::STATUS_ACCEPTED)
            ->setParameter('status_accepted_external', Deal::STATUS_ACCEPTED_EXTERNAL)
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return Deal[]
     */
    public function findAllByUser(User $user): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->where($qb->expr()->orX(
            $qb->expr()->eq('e.seller', ':user'),
            $qb->expr()->eq('e.buyer', ':user')
        ));
        $qb->orderBy('e.updated_at', 'DESC')
            ->setParameter('user', $user)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Deal[]
     */
    public function findCompleteByUser(User $user): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->where($qb->expr()->orX(
            $qb->expr()->eq('e.seller', ':user'),
            $qb->expr()->eq('e.buyer', ':user')
        ));
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq('e.status', ':status_complete'),
            $qb->expr()->eq('e.status', ':status_complete_outside')
        ));
        $qb->orderBy('e.updated_at', 'DESC')
            ->setParameter('user', $user)
            ->setParameter('status_complete', Deal::STATUS_COMPLETE)
            ->setParameter('status_complete_outside', Deal::STATUS_COMPLETE_OUTSIDE)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Deal[]
     */
    public function findCanceledByUser(User $user): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->where($qb->expr()->orX(
            $qb->expr()->eq('e.seller', ':user'),
            $qb->expr()->eq('e.buyer', ':user')
        ));
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq('e.status', ':status_cancel_by_buyer'),
            $qb->expr()->eq('e.status', ':status_cancel_by_seller')
        ));
        $qb->orderBy('e.updated_at', 'DESC')
            ->setParameter('user', $user)
            ->setParameter('status_cancel_by_buyer', Deal::STATUS_CANCEL_BY_BUYER)
            ->setParameter('status_cancel_by_seller', Deal::STATUS_CANCEL_BY_SELLER)
        ;

        return $qb->getQuery()->getResult();
    }

    public function countNewIncomingByUser(User $user): int
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('COUNT(e.id)');
        $qb->where('e.seller = :user');
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq('e.status', ':status_new'),
            $qb->expr()->eq('e.status', ':status_view'),
        ));
        $qb->setParameter('user', $user)
            ->setParameter('status_new', Deal::STATUS_NEW)
            ->setParameter('status_view', Deal::STATUS_VIEW)
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countActiveIncomingByUser(User $user): int
    {
        $qb = $this->getfindActiveIncomingByUserQueryBuilder($user);
        $qb->select('COUNT(e.id)');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return Deal[]
     */
    public function findActiveIncomingByUser(User $user): array
    {
        $qb = $this->getfindActiveIncomingByUserQueryBuilder($user);
        $qb->orderBy('e.updated_at', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function getfindActiveIncomingByUserQueryBuilder(User $user): QueryBuilder
    {
        $qb = $this->createQueryBuilder('e');
        $qb->where('e.seller = :user');
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq('e.status', ':status_new'),
            $qb->expr()->eq('e.status', ':status_view'),
            $qb->expr()->eq('e.status', ':status_accepted'),
            $qb->expr()->eq('e.status', ':status_accepted_external')
        ));
        $qb->setParameter('user', $user)
            ->setParameter('status_new', Deal::STATUS_NEW)
            ->setParameter('status_view', Deal::STATUS_VIEW)
            ->setParameter('status_accepted', Deal::STATUS_ACCEPTED)
            ->setParameter('status_accepted_external', Deal::STATUS_ACCEPTED_EXTERNAL)
        ;

        return $qb;
    }

    /**
     * @return Deal[]
     */
    public function findActiveOutgoingByUser(User $user): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->where('e.buyer = :user');
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq('e.status', ':status_new'),
            $qb->expr()->eq('e.status', ':status_view'),
            $qb->expr()->eq('e.status', ':status_accepted'),
            $qb->expr()->eq('e.status', ':status_accepted_external')
        ));
        $qb->orderBy('e.updated_at', 'DESC')
            ->setParameter('user', $user)
            ->setParameter('status_new', Deal::STATUS_NEW)
            ->setParameter('status_view', Deal::STATUS_VIEW)
            ->setParameter('status_accepted', Deal::STATUS_ACCEPTED)
            ->setParameter('status_accepted_external', Deal::STATUS_ACCEPTED_EXTERNAL)
        ;

        return $qb->getQuery()->getResult();
    }

    public function countOutgoingByUser(User $user): int
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('COUNT(e.id)');
        $qb->where('e.buyer = :user');
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq('e.status', ':status_new'),
            $qb->expr()->eq('e.status', ':status_view'),
            $qb->expr()->eq('e.status', ':status_accepted'),
            $qb->expr()->eq('e.status', ':status_accepted_external')
        ));
        $qb->setParameter('user', $user)
            ->setParameter('user', $user)
            ->setParameter('status_new', Deal::STATUS_NEW)
            ->setParameter('status_view', Deal::STATUS_VIEW)
            ->setParameter('status_accepted', Deal::STATUS_ACCEPTED)
            ->setParameter('status_accepted_external', Deal::STATUS_ACCEPTED_EXTERNAL)
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Offer $offer
     *
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countActiveForOffer(Offer $offer): int
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('count(e.created_at)');
        $qb->where('e.offer = :offer');
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq('e.status', ':status_new'),
            $qb->expr()->eq('e.status', ':status_view'),
            $qb->expr()->eq('e.status', ':status_accepted'),
            $qb->expr()->eq('e.status', ':status_accepted_external')
        ));
        $qb->setParameter('offer', $offer->getId())
            ->setParameter('status_new', Deal::STATUS_NEW)
            ->setParameter('status_view', Deal::STATUS_VIEW)
            ->setParameter('status_accepted', Deal::STATUS_ACCEPTED)
            ->setParameter('status_accepted_external', Deal::STATUS_ACCEPTED_EXTERNAL)
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Сумма "холда"
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function sumActiveForBuyer(User $user): int
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('SUM(e.amount_cost)');
        $qb->where('e.buyer = :user');
        $qb->andWhere('e.type = :type');
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq('e.status', ':status_new'),
            $qb->expr()->eq('e.status', ':status_view'),
            $qb->expr()->eq('e.status', ':status_accepted'),
            $qb->expr()->eq('e.status', ':status_accepted_external')
        ));
        $qb->setParameter('user', $user)
            ->setParameter('status_new', Deal::STATUS_NEW)
            ->setParameter('status_view', Deal::STATUS_VIEW)
            ->setParameter('status_accepted', Deal::STATUS_ACCEPTED)
            ->setParameter('status_accepted_external', Deal::STATUS_ACCEPTED_EXTERNAL)
            ->setParameter('type', Deal::TYPE_INNER)
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Сумма "холда" (алиас)
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getHoldSum(User $user): int
    {
        return $this->sumActiveForBuyer($user);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countForOffer(Offer $offer): int
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('COUNT(e.created_at)');
        $qb->where('e.offer = :offer');
        $qb->setParameter('offer', $offer->getId());

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countForUser(User $user): int
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('count(e.created_at)');
        $qb->where($qb->expr()->orX(
            $qb->expr()->eq('e.seller', ':user'),
            $qb->expr()->eq('e.buyer', ':user')
        ));
        $qb->setParameter('user', $user);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
