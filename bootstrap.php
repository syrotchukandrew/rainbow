<?php

use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/vendor/autoload.php';

if (file_exists(__DIR__.'/.env')) {
    (new Dotenv())->bootEnv(__DIR__.'/.env');
}
