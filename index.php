<?php

require __DIR__ . '/vendor/autoload.php';

use App\Router;




session_start();
define('BASE_URI', str_replace('\\', '/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']))));

$uri = str_replace(BASE_URI, '', $_SERVER['REQUEST_URI']);



$router = new Router;
$router->route($uri);
