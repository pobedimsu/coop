<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Deal;
use App\Entity\Offer;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @todo refactor
 */
class BillService
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Получение полного баланса.
     *
     * @throws \Exception
     */
    public function getBalance(User $user): int
    {
        return $this->getOffersBallance($user) // эмиссия
            + $this->getTransactionsIn($user) // входящие
            - $this->getTransactionsOut($user) // исходящие
            - $this->getHoldSum($user); // холд
    }

    /**
     * Сумма "холда"
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getHoldSum(User $user): int
    {
        return $this->em->getRepository(Deal::class)->getHoldSum($user);
    }

    /**
     * Входящие транзакции
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTransactionsIn(User $user): int
    {
        return $this->em->getRepository(Transaction::class)->getIncomingSum($user);
    }

    /**
     * Входящие транзакции
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTransactionsOut(User $user): int
    {
        return $this->em->getRepository(Transaction::class)->getOutgoingSum($user);
    }

    /**
     * Получение баланса по заявленным предложениям (эмиссия).
     *
     * @throws \Exception
     */
    public function getOffersBallance(User $user): int
    {
        $offers = $this->em->getRepository(Offer::class)->findBy(['user' => $user, 'is_enabled' => true]);

        $sum = 0;
        foreach ($offers as $offer) {
            if ($offer->getStatus() == Offer::STATUS_NOT_AVAILABLE and empty($offer->getQuantityReserved())) {
                continue;
            }

            $sum += $offer->getPriceTotal();
        }

        return $sum;
    }

    /**
     * Кол-во активных сделок по предложению
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountActiveDealsForOffer(Offer $offer): int
    {
        return $this->em->getRepository(Deal::class)->countActiveForOffer($offer);
    }

    /**
     * Кол-во всех сделок по предложению
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountAllDealsForOffer(Offer $offer): int
    {
        return $this->em->getRepository(Deal::class)->countForOffer($offer);
    }

    /**
     * Генерация блок-чейн хеша для била
     *
     * @param Bill $bill
     *
     * @return null|string
     * @throws \Doctrine\DBAL\DBALException
     *
     * @deprecated переделать на транзакции
     */
    public function __generateBlockChain(Bill $bill)
    {
        $prev_bill = $this->em->getRepository(Bill::class)->getPreviousBill($bill);

        if (empty($prev_bill)) {
            $prev_bill = null;
        }

        $bill->setHash($this->__getHashFromBill($bill, $prev_bill));

        $this->em->persist($bill);
        $this->em->flush();

        return $bill->getHash();
    }

    /**
     * @param Bill       $bill
     * @param array|null $prev_bill
     *
     * @return null|string
     * @throws \Doctrine\DBAL\DBALException
     *
     * @deprecated переделать на транзакции
     */
    public function __getHashFromBill(Bill $bill, array $prev_bill = null): ?string
    {
        $deal = [];
        if (!empty($bill->getDeal())) {
            $deal['amount_cost'] = $bill->getDeal()->getAmountCost();
            $deal['item'] = (string) $bill->getDeal()->getOffer()->getId();
        }

        if (!empty($prev_bill)) {
            $prev_bill = $this->em->getRepository(Bill::class)->getPreviousBill($bill);
        }

        if (empty($prev_bill)) {
            $prev_bill = null;
        }

        $hash_data = [
            'previous_bill' => $prev_bill,
            'bill_id' => (string) $bill->getId(),
            'sum' => $bill->getSum(),
            'balance' => $bill->getBalance(),
            'user_id' => (string) $bill->getUser()->getId(),
            'user_name' => $bill->getUser()->getUsername(),
            'created_at' => $bill->getCreatedAt()->format('Y-m-d H:i:s'),
            'deal' => $deal,
        ];

        $sha256 = hash('sha256', serialize($hash_data));

        return $sha256;
    }

    /**
     * @throws \Exception
     */
    public function getCountOffersByUser(User $user): int
    {
        $offers = $this->em->getRepository(Offer::class)->findBy([
            'user' => $user,
        ]);

        return count($offers);
    }

    /**
     * @throws \Exception
     */
    public function getCountOffersAvailableByUser(User $user): int
    {
        return $this->em->getRepository(Offer::class)->countAvailableByUser($user);
    }
}
