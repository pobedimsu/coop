<?php

use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    if ($context['APP_DEBUG']) {
        umask(0000);

        if (class_exists(Debug::class)) {
            Debug::enable();
        }
    } else {
        umask(0002);
    }

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
