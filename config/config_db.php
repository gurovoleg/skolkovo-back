<?php

if (DEBUG) {
    define('DB_HOST', '127.0.0.1');
    define('DB_NAME', 'skolkovo_test');
    define('DB_USER', 'root');
    define('DB_PASS', 'root');

} else {
    define('DB_HOST', '127.0.0.1');
    define('DB_NAME', 'gurovoleg_sk');
    define('DB_USER', 'gurovoleg_sk');
    define('DB_PASS', 'Detonator_07');
}

$db_connection = [
    'dsn' => 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
    'user' => DB_USER,
    'pass' => DB_PASS
];

return $db_connection;

