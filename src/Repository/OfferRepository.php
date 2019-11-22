<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Bill;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Smart\CoreBundle\Doctrine\RepositoryTrait;

class OfferRepository extends EntityRepository
{
    use RepositoryTrait\FindByQuery;
}
