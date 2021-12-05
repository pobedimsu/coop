<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ChatDialog;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Smart\CoreBundle\Doctrine\RepositoryTrait;

class ChatDialogRepository extends ServiceEntityRepository
{
    use RepositoryTrait\FindByQuery;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatDialog::class);
    }

    public function countUnreadOwner(User $user): int
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('COUNT(e.id)');
        $qb->where('e.owner = :user');
        $qb->andWhere('e.unread_owner_count > 0');
        $qb->setParameter('user', $user);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
