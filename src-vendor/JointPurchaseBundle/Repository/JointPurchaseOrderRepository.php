<?php

declare(strict_types=1);

namespace Coop\JointPurchaseBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Smart\CoreBundle\Doctrine\RepositoryTrait;

class JointPurchaseOrderRepository extends EntityRepository
{
    use RepositoryTrait\FindByQuery;
}
