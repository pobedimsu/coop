<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Tree\Entity\Repository\ClosureTreeRepository;
use Smart\CoreBundle\Doctrine\RepositoryTrait;

class UserRepository extends ClosureTreeRepository implements ServiceEntityRepositoryInterface
{
    use RepositoryTrait\CountBy;
    use RepositoryTrait\FindByQuery;

    public function __construct(ManagerRegistry $registry)
    {
        $manager = $registry->getManagerForClass(User::class);

        parent::__construct($manager, $manager->getClassMetadata(User::class));
    }
}
