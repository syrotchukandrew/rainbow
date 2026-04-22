<?php
declare(strict_types=1);

date_default_timezone_set('Europe/Kyiv');

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
