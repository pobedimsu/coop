<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Invite;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Smart\CoreBundle\Doctrine\RepositoryTrait;

class InviteRepository extends EntityRepository
{
    use RepositoryTrait\FindByQuery;

    public function findActiveByUser(User $user): ?Invite
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.user = :user')
            ->andWhere('e.is_used = 0')
            ->andWhere('e.created_at > :date')
            ->orderBy('e.created_at', 'DESC')
            ->setMaxResults(1)
            ->setParameter('date', new \DateTime('-1 day'))
            ->setParameter('user', $user)
        ;

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }
}
