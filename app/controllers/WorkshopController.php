<?php


namespace app\controllers;


use core\base\Controller;
use app\models\Workshop;

class WorkshopController extends Controller {

    public function __construct($route) {
        parent::__construct($route);
        $this->model = new Workshop();
    }

    // список
    public function listAction () {
        $this->authUser();
        $data = $this->model->getRecords();

        forEach ($data as &$item) {
            $usersTotal = \R::count('user', 'workshop_id = ?', [$item['id']]);
            $item['usersTotal'] = $usersTotal;
        }

        echo json_encode($data);
    }

    // получить практикум
    public function indexAction () {
        $this->authUser();
        $id = $this->route['params'];

        $result = $this->model->getRecordBy('id', $id);

        if ($result) {
            $usersTotal = \R::count('user', 'workshop_id = ?', [$id]);
            $result['usersTotal'] = $usersTotal;
            echo json_encode($result);
        } else {
            throw new \Error ("Практикум не найден", 400);
        }
    }

    // создать новый
    public function createAction () {
        list($data, $isExist) = $this->getAndCheckData('title');

        if ($isExist) {
            throw new \Error("Имя '{$data['title']}' уже существует.", 400);
        } else {
            $result = $this->model->validate($this->model->rules['add'], $data);
            if (!$result) {
                $message = $this->model->getErrors();
                throw new \Error($message, 400);
            } else {
                $data['created'] = time();
                $data['updated'] = time();
                $this->model->save($this->model->table, $data);
                http_response_code(204);
            }
        }
    }

    // обновить данные
    public function updateAction () {
        list($data, $isExist) = $this->getAndCheckData('id');

        if (!$isExist) {
            throw new \Error("Не найден указанный Практикум {$data['title']}.", 400);
        } else {
            $result = $this->model->validate($this->model->rules['update'], $data);
            if (!$result) {
                $message = $this->model->getErrors();
                throw new \Error($message, 400);
            } else {
                $data['updated'] = time();
                $this->model->save($this->model->table, $data);

                // если статус изменился на активный, то сбрасываем предыдущий активный
                if ($data['status'] === 'active') {
                    $this->resetPreviousActive($data['id']);
                }

                http_response_code(204);
            }
        }
    }

    // сбросить предыдущий активный статус
    private function resetPreviousActive ($id) {
        $result = \R::findOne($this->model->table, "status = ? AND id != ?", ['active', $id]);
        if ($result) {
            $result->status = 'inactive';
            \R::store($result);
        }
    }

    // удаление
    public function deleteAction () {
        $this->deleteRecord();
    }

}