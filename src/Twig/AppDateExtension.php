<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppDateExtension extends AbstractExtension
{
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
        $format = 'd MMMM Y г., HH:mm:ss',
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
}
