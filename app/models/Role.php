<?php


namespace app\models;


use core\base\Model;

class Role extends Model {
    public $table = 'role';

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