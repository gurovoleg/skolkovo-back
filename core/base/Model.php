<?php


namespace core\base;

use core\Db_RedBean;
use Valitron\Validator;

// В Модели создаем собственные методы для работы с БД
abstract class Model {
    protected $table;
    public $fields = [];
    public $rules = []; // параметры валидации
    public $errors = [];
    public $pagination = ['perPage' => 10];

    public function __construct () {
        Db_RedBean::getInstance();
    }

    // передаем данные из запроса и фильтруем согласно полям модели
    public function load () {
        $data = json_decode(file_get_contents('php://input'));
        $result = [];
        $data = is_object($data) ? $data : [$data];

        foreach ($data as $name => $value) {
            if (array_key_exists($name, $this->fields)) {
                $result[$name] = $value;
            }
        }
        return $result;
    }

    // сохранение данных в БД
    public function save ($table, $data) {
        $tbl = \R::dispense($table);

        foreach ($data as $name => $value) {
            $tbl->$name = $value;
        }

        return \R::store($tbl);
    }

    // удаление записи из БД
    public function remove ($idName, $id, $table = '') {
        $table = $table ? $table : $this->table;
        $result = \R::findOne($table, "$idName = ?", [$id]);
        if ($result) {
            \R::trash($result);
            return true;
        } else {
            $this->errors[] = ["Не найдена запись с '{$idName} = {$id}' в таблице {$table}"];
            return false;
        }
    }

    // валидация данных по заданным правилам (Valitron)
    public function validate ($rules, $data) {
        Validator::lang('ru');
        $v = new Validator($data);
        $v->rules($rules);
        if ($v->validate()) {
            return true;
        }
        $this->errors = $v->errors();
        return false;
    }

    // Проверка наличия данных в таблице по заданному ключу
    public function isExist ($idName, $value, $table = '') {
        $table = $table ? $table : $this->table;
        $result = \R::count($table, "$idName = ?", [$value]);
        if ($result > 0) {
            return true;
        }
        return false;
    }

    // Генерим разметку для ошибок и добавляем в сессию
    public function getErrors () {
        $errors = '';
        foreach ($this->errors as $error) {
            foreach ($error as $item) {
                $errors .= "$item<br>";
            }
        }
        return $errors;
    }

    // запрос записи + перевод в camelCase
    public function getRecordBy ($idName, $value, $table = '') {
        $table = $table ? $table : $this->table;
        $result = \R::findOne($table, "$idName = ?", [$value]);
        if (!empty($result)) {
            $result = \R::camelfy($result->export());
        }
        return $result;
    }

    // запрос всех записей + перевод в camelCase
    public function getRecords ($table = '') {
        $table = $table ? $table : $this->table;
        $sql = "SELECT * from $table";

        return \R::camelfy(\R::getAll($sql));
    }

    // запрос с sql + перевод в camelCase
    public function execute ($sql, $values = []) {
        if (!empty($values)) {
            return \R::camelfy(\R::getAll($sql, $values));
        } else {
            return \R::camelfy(\R::getAll($sql));
        }
    }

}