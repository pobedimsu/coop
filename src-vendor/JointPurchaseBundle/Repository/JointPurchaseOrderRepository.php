<?php

declare(strict_types=1);

namespace Coop\JointPurchaseBundle\Repository;

use Coop\JointPurchaseBundle\Entity\JointPurchaseOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Smart\CoreBundle\Doctrine\RepositoryTrait;

class JointPurchaseOrderRepository extends ServiceEntityRepository
{
    use RepositoryTrait\FindByQuery;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JointPurchaseOrder::class);
    }
}
