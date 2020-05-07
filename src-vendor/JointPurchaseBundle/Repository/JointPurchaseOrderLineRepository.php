<?php

declare(strict_types=1);

namespace Coop\JointPurchaseBundle\Repository;

use App\Entity\User;
use Coop\JointPurchaseBundle\Entity\JointPurchaseOrderLine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class JointPurchaseOrderLineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JointPurchaseOrderLine::class);
    }

    public function findOneByProductAndUser($product, User $user): ?JointPurchaseOrderLine
    {
        return $this->createQueryBuilder('e')
            ->where('e.product = :product')
            ->join('e.order', 'o', 'WITH', 'o.user = :user')
            ->setParameter('product', $product)
            ->setParameter('user', $user)
        ->getQuery()
        ->getOneOrNullResult();
    }
}
