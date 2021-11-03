<?php


namespace app\controllers;

use app\models\Quiz;
use core\base\Controller;
use libs\QueryBuilder;
use libs\Pagination;


class QuizController extends Controller {

    public function __construct($route) {
        parent::__construct($route);
        $this->model = new Quiz();
    }

    // получить вопрос
    public function indexAction () {
        $this->authUser();
        $id = $this->route['params'];

        $result = $this->model->getRecordBy('id', $id);

        if ($result) {
            $result['questionIds'] = json_decode($result['questionIds']) ?: [];
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
            $quizzes = $this->model->execute($query, $qb->where['values']);
            forEach($quizzes as &$quiz) {
                $quiz['questionIds'] = json_decode($quiz['questionIds']) ?: [];
            }
            echo json_encode(['data' => $quizzes, 'pagination' => $pagination]);
        } else {
            echo json_encode([]);
        }
    }

    // создание нового опросника
    public function createAction () {
        list($quiz, $isExist) = $this->getAndCheckData('title');

        if ($isExist) {
            throw new \Error("Вопрос с указанным именем (" . $quiz['title'] . ") уже существует", 400);
        } else {
            $isValid = $this->model->validate($this->model->rules['create'], $quiz);

            if (!$isValid) {
                $message = $this->model->getErrors();
                throw new \Error($message, 400);
            } else {
                $this->model->save($this->model->table, $quiz);
                http_response_code(204);
            }

        }
    }

    // обновить данные
    public function updateAction () {
        list($quiz, $result) = $this->getAndCheckData('id');

        if (!$result) {
            throw new \Error('Вопрос ' . $quiz['id'] . ' не найден', 400);
        } else {
            $isValid = $this->model->validate($this->model->rules['update'], $quiz);
            if (!$isValid) {
                $message = $this->model->getErrors();
                throw new \Error($message, 400);
            } else {
                $quiz['questionIds'] = json_encode($quiz['questionIds']);
                $this->model->save($this->model->table, $quiz);
                http_response_code(204);
            }
        }
    }

    // удаление
    public function deleteAction () {
        $this->deleteRecord();
    }
}