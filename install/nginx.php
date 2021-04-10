#!/usr/bin/env php
<?php

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;

if (!in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
    echo 'Warning: The console should be invoked via the CLI version of PHP, not the '.PHP_SAPI.' SAPI'.PHP_EOL;
}

umask(0000);

require dirname(__DIR__).'/vendor/autoload.php';

if (!isset($argv[1])) {
    echo "You need to set domain name\n\n";
    exit(1);
}

$domain = $argv[1];

if (file_exists('/etc/nginx/sites-enabled/' . $domain . '.conf')) {
    echo "Domain $domain is exists.\n";
    exit(1);
}

$input = new ArgvInput();
if (null !== $env = $input->getParameterOption(['--env', '-e'], null, true)) {
    putenv('APP_ENV='.$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = $env);
}

if ($input->hasParameterOption('--no-debug', true)) {
    putenv('APP_DEBUG='.$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = '0');
}

//Debug::enable();
(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

$dockerConfig = dirname(__DIR__)."/.env.docker.{$_SERVER['APP_ENV']}.local";

if (!file_exists($dockerConfig)) {
    echo "File .env.docker.{$_SERVER['APP_ENV']}.local is NOT exists.\n";
    exit(1);
}

(new Dotenv())->load($dockerConfig);

$conf = file_get_contents(dirname(__DIR__).'/install/configs/nginx.conf');

$conf = str_replace('${DOMAIN}', $domain, $conf);
$conf = str_replace('${WORKING_DIR}', $_SERVER['WORKING_DIR'], $conf);
$conf = str_replace('${PHP_FPM_PROD_PORT}', $_SERVER['PHP_FPM_PROD_PORT'], $conf);

file_put_contents('/etc/nginx/sites-enabled/'.$domain.'.conf', $conf);

system('/etc/init.d/nginx reload');
