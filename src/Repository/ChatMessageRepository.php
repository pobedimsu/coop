<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ChatMessage;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Smart\CoreBundle\Doctrine\RepositoryTrait;

class ChatMessageRepository extends ServiceEntityRepository
{
    use RepositoryTrait\FindByQuery;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatMessage::class);
    }

    /**
     * @return ChatMessage[]
     */
    public function findForParticipants(User $user1, User $user2, ?int $limit = 50, ?int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->where($qb->expr()->andX(
            $qb->expr()->eq('e.author', ':user1'),
            $qb->expr()->eq('e.recipient', ':user2')
        ));
        $qb->orWhere($qb->expr()->andX(
            $qb->expr()->eq('e.author', ':user2'),
            $qb->expr()->eq('e.recipient', ':user1')
        ));
        $qb->orderBy('e.created_at', 'DESC')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
        ;

        return $qb->getQuery()->getResult();
    }

    public function markAsReadForRecipient(User $author, User $recipient): int
    {
        $qb = $this->createQueryBuilder('e')
            ->update()
            ->set('e.is_recipient_read', ':is_recipient_read')
            ->where('e.author = :author')
            ->andWhere('e.recipient = :recipient')
            ->setParameter('is_recipient_read', true)
            ->setParameter('author', $author)
            ->setParameter('recipient', $recipient)
        ;

        return $qb->getQuery()->getScalarResult();
    }
}
