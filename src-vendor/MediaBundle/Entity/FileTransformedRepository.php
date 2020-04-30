<?php

declare(strict_types=1);

namespace SmartCore\Bundle\MediaBundle\Entity;

use Doctrine\ORM\EntityRepository;

class FileTransformedRepository extends EntityRepository
{
    public function countByCollection(string $collection): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.collection = :collection')
            ->setParameter('collection', $collection)
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function summarySize(string $collection): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('SUM(e.size)')
            ->where('e.collection = :collection')
            ->setParameter('collection', $collection)
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
