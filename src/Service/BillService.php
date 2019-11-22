<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Bill;
use App\Entity\Deal;
use App\Entity\Offer;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class BillService
{
    protected $em;

    /**
     * BillService constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEm(): EntityManagerInterface
    {
        return $this->em;
    }

    /**
     * Получение полного баланса: предложения и счета.
     *
     * @param User $user
     *
     * @return int
     * @throws \Exception
     */
    public function getBalance(User $user): int
    {
        $deals_sum = $this->em->getRepository(Deal::class)->sumActiveForDeclarantUser($user);

        return $this->getOffersBallance($user) + $this->getBillsBalance($user) - $deals_sum;
    }

    /**
     * Получение баланса только по заявленным предложениям.
     *
     * @param User $user
     *
     * @return int
     * @throws \Exception
     */
    public function getOffersBallance(User $user): int
    {
        $sum = 0;

        $offers = $this->em->getRepository(Offer::class)->findBy([
            'user' => $user,
        ]);

        foreach ($offers as $offer) {
            if ($offer->getStatus() == Offer::STATUS_NOT_AVAILABLE and empty($offer->getQuantityReserved())) {
                continue;
            }

            $sum += $offer->getPriceTotal();
        }

        return $sum;
    }

    /**
     * Получение баланса по счетам.
     *
     * @param User $user
     *
     * @return int
     */
    public function getBillsBalance(User $user): int
    {
        $bill = $this->em->getRepository(Bill::class)->findOneBy(['user' => $user], ['id' => 'DESC']);

        if (empty($bill)) {
            return 0;
        }

        return $bill->getBalance();
    }

    /**
     * @param Item $item
     *
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountActiveDealsForOffer(Offer $offer): int
    {
        return $this->em->getRepository(Deal::class)->countActiveForOffer($offer);
    }

    /**
     * @param Offer $offer
     *
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountAllDealsForOffer(Offer $offer): int
    {
        return $this->em->getRepository(Deal::class)->countForOffer($offer);
    }

    /**
     * @param Bill $bill
     *
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function updateCurrentBalance(Bill $bill)
    {
        $bill->setBalance(
            $this->em->getRepository(Bill::class)->getCurrentBalanceByUser($bill->getUser())
        );

        //$this->updateUserBalance($bill->getUser(), $bill->getBalance());

        $this->em->persist($bill);
        $this->em->flush();
        //$this->persist($bill->getUser(), true);

        return $bill->getBalance();
    }

    /**
     * Генерация блок-чейн хеша для била
     *
     * @param Bill $bill
     *
     * @return null|string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function generateBlockChain(Bill $bill)
    {
        $prev_bill = $this->em->getRepository(Bill::class)->getPreviousBill($bill);

        if (empty($prev_bill)) {
            $prev_bill = null;
        }

        $bill->setHash($this->getHashFromBill($bill, $prev_bill));

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
     */
    public function getHashFromBill(Bill $bill, array $prev_bill = null): ?string
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
     * @param User $user
     *
     * @return int
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
     * @param User $user
     *
     * @return int
     * @throws \Exception
     */
    public function getCountOffersAvailableByUser(User $user): int
    {
        $offers = $this->em->getRepository(Offer::class)->findBy([
            'user' => $user,
            // ['status', 'OR', [1, 2]], @todo
        ]);

        return count($offers);
    }
}
