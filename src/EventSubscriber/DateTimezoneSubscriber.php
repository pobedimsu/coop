<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class DateTimezoneSubscriber implements EventSubscriberInterface
{
    protected $timezone;

    public function __construct(?string $dateDefaultTimezone)
    {
        $this->timezone = $dateDefaultTimezone;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['dateDefaultTimezoneSet', 255],
            ],
            ConsoleEvents::COMMAND => [
                ['dateDefaultTimezoneSet', 255],
            ],
        ];
    }

    public function dateDefaultTimezoneSet()
    {
        if ($this->timezone and $this->timezone !== '~') {
            date_default_timezone_set($this->timezone);
        }
    }
}
