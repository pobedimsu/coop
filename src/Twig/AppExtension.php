<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Deal;
use App\Entity\Offer;
use App\Entity\User;
use App\Service\BillService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    protected $billService;
    protected $tgBotName;

    /**
     * AppExtension constructor.
     *
     * @param BillService $billService
     * @param             $tgBotName
     */
    public function __construct(BillService $billService, $tgBotName)
    {
        $this->billService = $billService;
        $this->tgBotName   = $tgBotName;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('app_balance',                     [$this, 'getBalance']),
            new TwigFunction('app_offers_balance',              [$this, 'getOffersBallance']),
            new TwigFunction('app_bills_balance',               [$this, 'getBillsBalance']),
            new TwigFunction('app_count_deals_for_offer',       [$this, 'getCountAllDealsForOffer']),
            new TwigFunction('app_count_deals_for_user',        [$this, 'getCountAllDealsForUser']),
            new TwigFunction('app_count_offers_for_user',       [$this, 'getCountOffersForUser']),
            new TwigFunction('app_count_offers_available_for_user', [$this, 'getCountOffersAvailableByUser']),
            new TwigFunction('app_count_active_deals_for_offer',    [$this, 'getCountActiveDealsForOffer']),

            new TwigFunction('app_tg_bot_name',    [$this, 'getTgBotName']),
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'twig.app';
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
     * @param User|null $user
     *
     * @return int
     * @throws \Exception
     */
    public function getBillsBalance(?User $user = null): int
    {
        return $this->billService->getBillsBalance($user);
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
        $em = $this->billService->getEm();

        return $em->getRepository(Deal::class)->countForUser($user);
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
     * @return string|null
     */
    public function getTgBotName(): ?string
    {
        return $this->tgBotName;
    }
}
