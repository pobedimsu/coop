<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Bill;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Smart\CoreBundle\Doctrine\RepositoryTrait;

class BillRepository extends EntityRepository
{
    use RepositoryTrait\FindByQuery;

    /**
     * @param User $user
     *
     * @return float
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCurrentBalanceByUser(User $user): int
    {
        $qb = $this->createQueryBuilder('b')
            ->select('SUM(b.sum)')
            ->where('b.user = :user');
        $qb->setParameter('user', $user);

        $query = $qb->getQuery();

        //return round($query->getSingleScalarResult(), 2); // float
        return (int) $query->getSingleScalarResult();
    }

    /**
     * @param Bill $bill
     *
     * @return array|bool|\Doctrine\DBAL\Driver\Statement
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getPreviousBill(Bill $bill)
    {
        // @todo пересмотреть !!!
        $table = $this->_class->getTableName();

        $sql = "
            SELECT id, hash, created_at
            FROM $table
            WHERE id < '{$bill->getId()}'
            ORDER BY created_at DESC
            LIMIT 1
        ";

        return $this->_em->getConnection()->query($sql)->fetch();
    }

    /**
     * @param $bill
     *
     * @return Bill[]
     */
    public function findFrom($bill): array
    {
        $qb = $this
            ->createQueryBuilder('e')
            ->where('e.id <= :id')
            ->orderBy('e.id', 'DESC')
            ->setParameter('id', $bill)
        ;

        //return $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
        return $qb->getQuery()->getResult();
    }
}
