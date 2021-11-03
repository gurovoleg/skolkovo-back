<?php


namespace app\controllers;


use core\base\Controller;
use app\models\Unit;

class UnitController extends Controller {

    public function __construct($route) {
        parent::__construct($route);
        $this->model = new Unit();
    }

    public function listAction () {
        $this->authUser();
        $data = $this->model->getRecords();
        echo json_encode($data);
    }

    public function createAction () {
        list($data, $isExist) = $this->getAndCheckData('title');

        if ($isExist) {
            throw new \Error("Это имя '{$data['title']}' уже используется.", 400);
        } else {
            $result = $this->model->validate($this->model->rules['add'], $data);
            if (!$result) {
                $message = $this->model->getErrors();
                throw new \Error($message, 400);
            } else {
                $this->model->save($this->model->table, $data);
                http_response_code(204);
            }
        }
    }

    public function updateAction () {
        $this->createAction();
    }

    public function deleteAction () {
        $this->deleteRecord();
    }
}