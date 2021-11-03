<?php

session_start();

// Увеличиваем память (требуется для подсчета 500К записей)
ini_set('memory_limit', '1024M');

require_once $_SERVER['DOCUMENT_ROOT'] . '/config/config.php';
require_once LIBS . '/functions.php';
require_once LIBS . '/ErrorHandler.php';
require_once ROOT . '/vendor/autoload.php'; // автозагрузка классов

if (DEBUG) {
    header('Access-Control-Allow-Origin: http://localhost:8080');

    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE, OPTIONS');
    header('Access-Control-Max-Age: 86400');
    header('Access-Control-Allow-Headers:X-Requested-With, X-HTTP-Method-Override,Content-Type,Accept,Keep-Alive,User-Agent,If-Modified-Since,Cache-Control,Content-Range,Range,Authorization');
    // header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit;
    }
} else {
    // header('Access-Control-Allow-Origin: http://skolkovo.gurovoleg.beget.tech');
}

use core\Router;

// debug($_SERVER);

// убираем api из запроса
$url = trim(str_replace('/api/', '', $_SERVER['REQUEST_URI']), '/');

// user
Router::addRoute('^$', ['controller' => 'Main', 'action' => 'index']); // корневой маршрут
// Router::addRoute('^(?P<controller>[a-z-]+)/?(?P<action>[a-z-]+)?/?(?P<params>[\d]+)?$'); // маска маршрутов: controller - action - params
Router::addRoute('^(?P<controller>[a-z-]+)/?(?P<params>[\d]+)?/?(?P<action>[a-z-]+)?/?(?P<props>[\d]+)?$'); // маска маршрутов: controller - params - action - props


Router::dispatch($url);


$route = Router::getRoute();
