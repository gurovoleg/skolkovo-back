<?php


namespace core;

/**
 * Trait TSingleton
 * @package vendor\core
 * Трейт для создания класса по шаблону Одиночка
 */
trait TSingleton {

    private static $instance;

    public static function getInstance () {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

}