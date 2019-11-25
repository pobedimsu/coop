<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Deal;
use App\Entity\Offer;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Smart\CoreBundle\Doctrine\RepositoryTrait;

class DealRepository extends EntityRepository
{
    use RepositoryTrait\FindByQuery;

    public function findActiveByUser($user)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->where($qb->expr()->orX(
            $qb->expr()->eq('e.contractor_user', ':user'),
            $qb->expr()->eq('e.declarant_user', ':user')
        ));
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq('e.status', ':status_new'),
            $qb->expr()->eq('e.status', ':status_view'),
            $qb->expr()->eq('e.status', ':status_accepted'),
            $qb->expr()->eq('e.status', ':status_accepted_outside')
        ));
        $qb->orderBy('e.updated_at', 'DESC')
            ->setParameter('user', $user)
            ->setParameter('status_new', Deal::STATUS_NEW)
            ->setParameter('status_view', Deal::STATUS_VIEW)
            ->setParameter('status_accepted', Deal::STATUS_ACCEPTED)
            ->setParameter('status_accepted_outside', Deal::STATUS_ACCEPTED_OUTSIDE)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findAllByUser($user)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->where($qb->expr()->orX(
            $qb->expr()->eq('e.contractor_user', ':user'),
            $qb->expr()->eq('e.declarant_user', ':user')
        ));
        $qb->orderBy('e.updated_at', 'DESC')
            ->setParameter('user', $user)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findCompleteByUser($user)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->where($qb->expr()->orX(
            $qb->expr()->eq('e.contractor_user', ':user'),
            $qb->expr()->eq('e.declarant_user', ':user')
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

    public function findCanceledByUser($user)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->where($qb->expr()->orX(
            $qb->expr()->eq('e.contractor_user', ':user'),
            $qb->expr()->eq('e.declarant_user', ':user')
        ));
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq('e.status', ':status_cancel_by_declarant'),
            $qb->expr()->eq('e.status', ':status_cancel_by_contractor')
        ));
        $qb->orderBy('e.updated_at', 'DESC')
            ->setParameter('user', $user)
            ->setParameter('status_cancel_by_declarant', Deal::STATUS_CANCEL_BY_DECLARANT)
            ->setParameter('status_cancel_by_contractor', Deal::STATUS_CANCEL_BY_CONTRACTOR)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findActiveIncomingByUser($user)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->where('e.contractor_user = :user');
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq('e.status', ':status_new'),
            $qb->expr()->eq('e.status', ':status_view'),
            $qb->expr()->eq('e.status', ':status_accepted'),
            $qb->expr()->eq('e.status', ':status_accepted_outside')
        ));
        $qb->orderBy('e.updated_at', 'DESC')
            ->setParameter('user', $user)
            ->setParameter('status_new', Deal::STATUS_NEW)
            ->setParameter('status_view', Deal::STATUS_VIEW)
            ->setParameter('status_accepted', Deal::STATUS_ACCEPTED)
            ->setParameter('status_accepted_outside', Deal::STATUS_ACCEPTED_OUTSIDE)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findActiveOutgoingByUser($user)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->where('e.declarant_user = :user');
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq('e.status', ':status_new'),
            $qb->expr()->eq('e.status', ':status_view'),
            $qb->expr()->eq('e.status', ':status_accepted'),
            $qb->expr()->eq('e.status', ':status_accepted_outside')
        ));
        $qb->orderBy('e.updated_at', 'DESC')
            ->setParameter('user', $user)
            ->setParameter('status_new', Deal::STATUS_NEW)
            ->setParameter('status_view', Deal::STATUS_VIEW)
            ->setParameter('status_accepted', Deal::STATUS_ACCEPTED)
            ->setParameter('status_accepted_outside', Deal::STATUS_ACCEPTED_OUTSIDE)
        ;

        return $qb->getQuery()->getResult();
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
            $qb->expr()->eq('e.status', ':status_accepted_outside')
        ));
        $qb->setParameter('offer', $offer->getId())
            ->setParameter('status_new', Deal::STATUS_NEW)
            ->setParameter('status_view', Deal::STATUS_VIEW)
            ->setParameter('status_accepted', Deal::STATUS_ACCEPTED)
            ->setParameter('status_accepted_outside', Deal::STATUS_ACCEPTED_OUTSIDE)
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Сумма "холда"
     *
     * @param User $user
     *
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function sumActiveForDeclarantUser(User $user): int
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('sum(e.amount_cost)');
        $qb->where('e.declarant_user = :user');
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq('e.status', ':status_new'),
            $qb->expr()->eq('e.status', ':status_view'),
            $qb->expr()->eq('e.status', ':status_accepted'),
            $qb->expr()->eq('e.status', ':status_accepted_outside')
        ));
        $qb->setParameter('user', $user)
            ->setParameter('status_new', Deal::STATUS_NEW)
            ->setParameter('status_view', Deal::STATUS_VIEW)
            ->setParameter('status_accepted', Deal::STATUS_ACCEPTED)
            ->setParameter('status_accepted_outside', Deal::STATUS_ACCEPTED_OUTSIDE)
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Сумма "холда" (алиас)
     *
     * @param User $user
     *
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getHoldSum(User $user): int
    {
        return $this->sumActiveForDeclarantUser($user);
    }

    /**
     * @param Offer $offer
     *
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countForOffer(Offer $offer): int
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('count(e.created_at)');
        $qb->where('e.offer = :offer');
        $qb->setParameter('offer', $offer->getId());

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param User $user
     *
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countForUser(User $user): int
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('count(e.created_at)');
        $qb->where($qb->expr()->orX(
            $qb->expr()->eq('e.contractor_user', ':user'),
            $qb->expr()->eq('e.declarant_user', ':user')
        ));
        $qb->setParameter('user', $user);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
