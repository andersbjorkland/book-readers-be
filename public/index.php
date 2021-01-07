<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

$isProd = false;
$vendors = '';
$envs = '';

if (strpos($_SERVER['SERVER_NAME'], 'localhost') !== false
    || strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false) {
	$vendors =  dirname(__DIR__).'/vendor/autoload.php';
	$envs = dirname(__DIR__).'/.env';
} else {
    $vendors = dirname( __DIR__ ) . '/httpd.private/books/vendor/autoload.php';
    $envs    = dirname( __DIR__ ) . '/httpd.private/books/.env';
}

require $vendors;

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
