<?php


namespace app\models;


use core\base\Model;

class Appraisal extends Model {
    public $table = 'appraisal';

    // набор полей
    public $fields = [
        'certifier' => '',
        'attested' => '',
        'workshopId' => '',
        'module' => '',
        'event' => '',
        'quizId' => '',
        'streamId' => '',
        'result' => '',
    ];

    // настройки валидации согласно запросам
    public $rules = [
        'add' => [
            'required' => [
                ['title'], ['attested'], ['certifier'], ['workshopId'], ['module'], ['event'], ['quizId'], ['streamId'], ['result']
            ]
        ],
    ];
}