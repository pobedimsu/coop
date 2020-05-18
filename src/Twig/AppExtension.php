<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Deal;
use App\Entity\Offer;
use App\Entity\User;
use App\Service\BillService;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    protected $billService;
    protected $tgBotName;
    protected $em;

    public function __construct(BillService $billService, EntityManagerInterface $em, $tgBotName)
    {
        $this->billService = $billService;
        $this->tgBotName   = $tgBotName;
        $this->em          = $em;
    }

    public function getName(): string
    {
        return 'twig.app';
    }

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

    public function getFilters(): array
    {
        return [
            new TwigFilter('app_date', [$this, 'getDate'], ['needs_environment' => true]),
        ];
    }

    /**
     * http://userguide.icu-project.org/formatparse/datetime
     */
    public function getDate(
        Environment $env,
        $date,
        $format = 'd MMMM Y г., HH:mm',
        $dateFormat = 'medium',
        $timeFormat = 'medium',
        $locale = null,
        $timezone = null,
        $calendar = 'gregorian'
    ): string {
        $date = twig_date_converter($env, $date, $timezone);

        $formatValues = [
            'none' => \IntlDateFormatter::NONE,
            'short' => \IntlDateFormatter::SHORT,
            'medium' => \IntlDateFormatter::MEDIUM,
            'long' => \IntlDateFormatter::LONG,
            'full' => \IntlDateFormatter::FULL,
        ];

        if (PHP_VERSION_ID < 50500 || !class_exists('IntlTimeZone')) {
            $formatter = \IntlDateFormatter::create(
                $locale,
                $formatValues[$dateFormat],
                $formatValues[$timeFormat],
                $date->getTimezone()->getName(),
                'gregorian' === $calendar ? \IntlDateFormatter::GREGORIAN : \IntlDateFormatter::TRADITIONAL,
                $format
            );

            return $formatter->format($date->getTimestamp());
        }

        $formatter = \IntlDateFormatter::create(
            $locale,
            $formatValues[$dateFormat],
            $formatValues[$timeFormat],
            \IntlTimeZone::createTimeZone($date->getTimezone()->getName()),
            'gregorian' === $calendar ? \IntlDateFormatter::GREGORIAN : \IntlDateFormatter::TRADITIONAL,
            $format
        );

        return $formatter->format($date->getTimestamp());
    }

    /**
     * @throws \Exception
     */
    public function getBalance(?User $user = null): int
    {
        return $this->billService->getBalance($user);
    }

    /**
     * @throws \Exception
     */
    public function getOffersBallance(?User $user = null): int
    {
        return $this->billService->getOffersBallance($user);
    }

    public function getCountAllDealsForOffer(Offer $offer): int
    {
        return $this->billService->getCountAllDealsForOffer($offer);
    }

    public function getCountActiveDealsForOffer(Offer $offer): int
    {
        return $this->billService->getCountActiveDealsForOffer($offer);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountAllDealsForUser(User $user): int
    {
        return $this->em->getRepository(Deal::class)->countForUser($user);
    }

    public function getCountOffersForUser(User $user): int
    {
        return $this->billService->getCountOffersByUser($user);
    }

    public function getCountOffersAvailableByUser(User $user): int
    {
        return $this->billService->getCountOffersAvailableByUser($user);
    }

    /**
     * Сумма "холда"
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getHoldSum(User $user): int
    {
        return $this->billService->getHoldSum($user);
    }

    /**
     * Входящие транзакции
     */
    public function getTransactionsIn(User $user): int
    {
        return $this->billService->getTransactionsIn($user);
    }

    /**
     * Исодящие транзакции
     */
    public function getTransactionsOut(User $user): int
    {
        return $this->billService->getTransactionsOut($user);
    }

    public function getTgBotName(): ?string
    {
        return $this->tgBotName;
    }
}
