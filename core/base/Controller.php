<?php


namespace core\base;

use libs\Token;

// Базовый абстрактный класс для других
abstract class Controller {
    public $model;
    public $route = [];

    public function __construct ($route) {
        $this->route = $route;
    }

    // проверка пользователя
    public function authUser () {
        try {
            // $headers = getallheaders();
            // debug($headers);
            // die();
            $token = getallheaders()['authorization'];
            $tokenData = Token::extractToken($token);

            if (isset($tokenData) && !empty($tokenData->userId)) {
                return $tokenData->userId;
            } else {
                throw new \Error ("Пользователь не авторизован.", 401);
            }
        } catch (\Error $e) {
            echo json_encode($e);
            die();
        }
    }

    // проверяем и получаем данные по запросу
    public function getAndCheckData ($idName, $withAuth = true) {
        // проверка пользователя
        if ($withAuth) $this->authUser();
        // проверка данных согласно полям модели
        $data = $this->model->load();
        // проверка наличия данных в базе согласно указанному полю
        $result = $this->model->isExist($idName, $data[$idName]);

        return [$data, $result];
    }

    // удаление записи (по умолчанию по id из url)
    public function deleteRecord ($idName = 'id', $value = '' ,$message = '') {
        $value = $value ? $value : $this->route['params'];
        $this->authUser();
        $result = $this->model->remove($idName, $value);
        if (!$result) {
            $message = $message ? $message : $this->model->getErrors();
            throw new \Error($message, 400);
        } else {
            http_response_code(204);
        }
    }

}