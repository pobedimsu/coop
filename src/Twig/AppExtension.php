<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Deal;
use App\Entity\Offer;
use App\Entity\User;
use App\Service\BillService;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    protected $billService;
    protected $tgBotName;
    protected $em;

    /**
     * AppExtension constructor.
     *
     * @param BillService            $billService
     * @param EntityManagerInterface $em
     * @param                        $tgBotName
     */
    public function __construct(BillService $billService, EntityManagerInterface $em, $tgBotName)
    {
        $this->billService = $billService;
        $this->tgBotName   = $tgBotName;
        $this->em          = $em;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'twig.app';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('app_balance',                     [$this, 'getBalance']),
            new TwigFunction('app_offers_balance',              [$this, 'getOffersBallance']),
            new TwigFunction('app_count_deals_for_offer',       [$this, 'getCountAllDealsForOffer']),
            new TwigFunction('app_count_deals_for_user',        [$this, 'getCountAllDealsForUser']),
            new TwigFunction('app_count_offers_for_user',       [$this, 'getCountOffersForUser']),
            new TwigFunction('app_count_offers_available_for_user', [$this, 'getCountOffersAvailableByUser']),
            new TwigFunction('app_count_active_deals_for_offer',    [$this, 'getCountActiveDealsForOffer']),
            new TwigFunction('app_get_hold_sum',        [$this, 'getHoldSum']),
            new TwigFunction('app_transactions_in',     [$this, 'getTransactionsIn']),
            new TwigFunction('app_transactions_out',    [$this, 'getTransactionsOut']),

            new TwigFunction('app_tg_bot_name',    [$this, 'getTgBotName']),
        ];
    }

    /**
     * @param User|null $user
     *
     * @return int
     * @throws \Exception
     */
    public function getBalance(?User $user = null): int
    {
        return $this->billService->getBalance($user);
    }

    /**
     * @param User|null $user
     *
     * @return int
     * @throws \Exception
     */
    public function getOffersBallance(?User $user = null): int
    {
        return $this->billService->getOffersBallance($user);
    }

    /**
     * @param Item $item
     *
     * @return int
     */
    public function getCountAllDealsForOffer(Offer $offer): int
    {
        return $this->billService->getCountAllDealsForOffer($offer);
    }

    /**
     * @param Item $item
     *
     * @return int
     */
    public function getCountActiveDealsForOffer(Offer $offer): int
    {
        return $this->billService->getCountActiveDealsForOffer($offer);
    }

    /**
     * @param User $user
     *
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountAllDealsForUser(User $user): int
    {
        return $this->em->getRepository(Deal::class)->countForUser($user);
    }

    /**
     * @param User $user
     *
     * @return int
     */
    public function getCountOffersForUser(User $user): int
    {
        return $this->billService->getCountOffersByUser($user);
    }

    /**
     * @param User $user
     *
     * @return int
     */
    public function getCountOffersAvailableByUser(User $user): int
    {
        return $this->billService->getCountOffersAvailableByUser($user);
    }

    /**
     * Сумма "холда"
     *
     * @param User $user
     *
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getHoldSum(User $user): int
    {
        return $this->billService->getHoldSum($user);
    }

    /**
     * Входящие транзакции
     *
     * @param User $user
     *
     * @return int
     */
    public function getTransactionsIn(User $user): int
    {
        return $this->billService->getTransactionsIn($user);
    }

    /**
     * Исодящие транзакции
     *
     * @param User $user
     *
     * @return int
     */
    public function getTransactionsOut(User $user): int
    {
        return $this->billService->getTransactionsOut($user);
    }

    /**
     * @return string|null
     */
    public function getTgBotName(): ?string
    {
        return $this->tgBotName;
    }
}
