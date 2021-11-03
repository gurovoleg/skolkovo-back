<?php


namespace core;

// Класс для работы с ORM RedBean
class Db_RedBean {

    use TSingleton; // подключаем реализацю методов для шаблона Одиночка

    protected function __construct () {
        require LIBS . '/rb-mysql.php';
        // получаем параметры подключения к БД
        $db = require ROOT . '/config/config_db.php';

        \R::setup($db['dsn'], $db['user'], $db['pass']);
        // \R::fancyDebug(); // включаем режим отладки - отображение всех запросов

        $result = \R::testConnection();

        if (!$result) {
            throw new \Exception('Нет подключения к БД', 500);
        }
    }
}