<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Smart\CoreBundle\Doctrine\RepositoryTrait;

class DemandRepository extends EntityRepository
{
    use RepositoryTrait\FindByQuery;
}