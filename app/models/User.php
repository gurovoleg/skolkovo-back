<?php


namespace app\models;


use core\base\Model;


class User extends Model {

    public $table = 'user';

    /*
     *  Набор полей. Используется для идентификации и фильтрации данных с фронта.
     */
    public $fields = [
        'id' => '',
        'email' => '',
        'password' => '',
        'name' => '',
        'surname' => '',
        'patronymic' => '',
        'gender' => '',
        'age' => null,
        'unitId' => '',
        'workshopId' => '',
        'headquarters' => '',
        'rating' => null,
        'roleId' => null,
        'streamId' => '',
        'status' => ''
    ];

    // кастомные запросы
    public $sql = [
        'full' => '
            SELECT user.*, workshop.title as workshop, unit.title as unit, role.title as role, stream.title as stream FROM user
            LEFT JOIN workshop ON (workshop_id = workshop.id)
            LEFT JOIN unit ON (unit_id = unit.id)
            LEFT JOIN stream ON (stream_id = stream.id)
            LEFT JOIN role ON (role_id = role.id)'
    ];

   /*
    *  Настройки валидации согласно запросам
    *  ИСпользуется вместе с Valitron
    */
    public $rules = [
        'registration' => [
            'required' => [
                ['email'], ['password'],['name'], ['surname']
            ],
            'email' => [
                ['email']
            ],
            'lengthMin' => [
                ['password', 5],
            ],
        ],
        'update' => [
            'required' => [
                ['email'], ['name'], ['surname'],['password']
            ],
            'optional' => [
                ['email'], ['name'], ['surname'],['password']
            ],
            'email' => [
                ['email']
            ],
            'lengthMin' => [
                ['password', 5],
            ]
        ],
    ];

    public function login ($data) {
        if ($data['email'] && $data['password']) {
            $user = \R::findOne($this->table, 'email = ?', [$data['email']]);
            if ($user) {
                if (password_verify($data['password'], $user->password)) {
                    return $user;
                }
            }
        }
        return false;
    }

    public function loadProfile ($id) {
        // $user = $this->getRecord($this->table, $id, 'id');
        $query = $this->sql['full'] . ' WHERE user.id = ?';
        $user = \R::camelfy(\R::getRow($query, [$id]));
        if ($user) {
            unset($user['password']);
            return $user;
        }
        return false;
    }

    public function hashPassword (&$user) {
        if (isset($user['password'])) {
            $user['password'] = password_hash($user['password'], PASSWORD_DEFAULT);
        }
    }

    // группируем свойства с общим префиксом для указанных полей
    // public function groupProps ($records) {
    //     $result = [];
    //     forEach ($records as $record) {
    //         $result[] = uniteProps($record, $this->linkedTables);
    //     }
    //     return $result;
    // }
    //
    // // разгруппируем свойства с общим префиксом для указанных полей
    // public function ungroupProps ($records) {
    //     $result = [];
    //     forEach ($records as $record) {
    //         $result[] = disuniteProps($record, $this->linkedTables);
    //     }
    //     return $result;
    // }
}