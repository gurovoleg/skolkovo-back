<?php


namespace app\models;


use core\base\Model;

class Stream extends Model {
    public $table = 'stream';

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