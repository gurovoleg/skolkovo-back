<?php


namespace app\models;


use core\base\Model;

class Workshop extends Model {
    public $table = 'workshop';

    // набор полей
    public $fields = [
        'id' => '',
        'title' => '',
        'status' => '',
        'modulesTotal' => '',
        'modulesCurrent' => '',
        'eventsTotal' => '',
        'eventsCurrent' => '',
        'quizId' => '',
    ];

    // настройки валидации согласно запросам
    public $rules = [
        'add' => [
            'required' => [
                ['title']
            ]
        ],
        'update' => [
            'required' => [
                ['title'], ['modulesTotal'], ['eventsTotal'], ['status']
            ]
        ],
    ];
}