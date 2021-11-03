<?php


namespace app\models;


use core\base\Model;
use Valitron\Validator;

class Quiz extends Model {
    public $table = 'quiz';

    // набор полей
    public $fields = [
        'id' => '',
        'title' => '',
        'comment' => '',
        'questionIds' => []
    ];

    // настройки валидации согласно запросам
    public $rules = [
        'create' => [
            'required' => [
                ['title']
            ],
        ],
        'update' => [
            'required' => [
                ['id'], ['title']
            ],
            // 'checkQuestions' => 'questionsId'
        ],
    ];

    // public function __construct() {
    //     parent::__construct();
    //
    //     // добавляем метод для проверки ответов
    //     Validator::addRule('checkAnswers', function ($field, $value) {
    //         if (isset($value)) {
    //             forEach ($value as $v) {
    //                 if (is_Array($v) && count($v) > 0) return true;
    //                 if ((int)$v > 0) return true;
    //             }
    //         }
    //         return false;
    //     }, 'Не выбрано ни одного варианта ответа.');
    // }

}