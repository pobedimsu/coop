<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\JointPurchaseOrder;
use Doctrine\ORM\EntityRepository;
use Smart\CoreBundle\Doctrine\RepositoryTrait;

class JointPurchaseOrderRepository extends EntityRepository
{
    use RepositoryTrait\FindByQuery;
}
