<?php


namespace app\controllers;

use app\models\Question;
use core\base\Controller;
use libs\QueryBuilder;
use libs\Pagination;


class QuestionController extends Controller {

    public function __construct($route) {
        parent::__construct($route);
        $this->model = new Question();
    }

    // получить вопрос
    public function indexAction () {
        $this->authUser();
        $id = $this->route['params'];

        $result = $this->model->getRecordBy('id', $id);

        if ($result) {
            $result['answers'] = json_decode($result['answers']) ?: (object)[];
            echo json_encode($result);
        } else {
            throw new \Error ("Вопрос не найден", 400);
        }
    }

    // список
    public function listAction () {
        // подготовка запроса согласно параметрам адресной  строки
        $qb = new QueryBuilder($this->model->fields, $this->model->table);

        if ($qb->total > 0) {
            $pagination = new Pagination($this->model->pagination['perPage'], $qb->total);
            $query = $qb->sql($pagination);
            $data = $this->model->execute($query, $qb->where['values']);
            forEach($data as &$item) {
                $item['answers'] = json_decode($item['answers']) ?: (object)[];
            }
            echo json_encode(['data' => $data, 'pagination' => $pagination]);
        } else {
            echo json_encode([]);
        }
    }

    // создание нового вопроса
    public function createAction () {
        list($question, $isExist) = $this->getAndCheckData('title');

        if ($isExist) {
            throw new \Error("Вопрос с указанным именем (" . $question['title'] . ") уже существует", 400);
        } else {
            $isValid = $this->model->validate($this->model->rules['create'], $question);

            if (!$isValid) {
                $message = $this->model->getErrors();
                throw new \Error($message, 400);
            } else {
                $question['answers'] = json_encode($question['answers'], JSON_UNESCAPED_UNICODE);
                $this->model->save($this->model->table, $question);
                http_response_code(204);
            }

        }
    }

    // обновить данные
    public function updateAction () {
        list($question, $result) = $this->getAndCheckData('id');

        if (!$result) {
            throw new \Error('Вопрос ' . $question['id'] . ' не найден', 400);
        } else {
            $isValid = $this->model->validate($this->model->rules['update'], $question);
            if (!$isValid) {
                $message = $this->model->getErrors();
                throw new \Error($message, 400);
            } else {
                $question['answers'] = json_encode($question['answers'], JSON_UNESCAPED_UNICODE);
                $this->model->save($this->model->table, $question);
                http_response_code(204);
            }
        }
    }

    // удаление
    public function deleteAction () {
        $this->deleteRecord();
    }
}