<?php


namespace app\models;


use core\base\Model;

class Unit extends Model {
    public $table = 'unit';

    // набор полей
    public $fields = [
        'id' => '',
        'title' => '',
    ];

    // настройки валидации согласно запросам
    public $rules = [
        'add' => [
            'required' => [
                ['title']
            ]
        ],
    ];
}