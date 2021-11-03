<?php


namespace app\models;


use core\base\Model;
use Valitron\Validator;

class Question extends Model {
    public $table = 'question';

    // набор полей
    public $fields = [
        'id' => '',
        'title' => '',
        'text' => '',
        'answers' => []
    ];

    // настройки валидации согласно запросам
    public $rules = [
        'create' => [
            'required' => [
                ['title'], ['text'], ['answers']
            ],
        ],
        'update' => [
            'required' => [
                ['title'], ['text'], ['id'], ['answers']
            ],
            'checkAnswers' => 'answers'
        ],
    ];

    public function __construct() {
        parent::__construct();

        // добавляем метод для проверки ответов (название поля, значение, параметры (передаются как массив [поле, значение параметра]), все данные)
        Validator::addRule('checkAnswers', function ($field, $value, array $params, array $fields) {
            if (isset($value)) {
                forEach ($value as $v) {
                    if (is_Array($v) && count($v) > 0) return true;
                    if ((int)$v > 0) return true;
                }
            }
            return false;
        }, 'Не выбрано ни одного варианта ответа.');
    }

}