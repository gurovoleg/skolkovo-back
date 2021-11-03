<?php


namespace app\controllers;

use app\models\Appraisal;
use core\base\Controller;


class AppraisalController extends Controller {

    public function __construct($route) {
        parent::__construct($route);
        $this->model = new Appraisal();
    }

    // активный практикум + опросник с вопросами + список пользователей, которых еще не аттестовывал данный пользователь
    public function indexAction () {
        $userId = $this->authUser();

        // получить активный практикум
        $workshop = $this->model->getRecordBy('status', 'active', 'workshop');

        if (!$workshop) {
            throw new \Error("Не задан активный Практикум.", 400);
        } else {
            // получить опросник
            $quiz = $this->model->getRecordBy('id', $workshop['quizId'], 'quiz');
            $ids = json_decode($quiz['questionIds']) ?: [];
            $quiz['questionIds'] = $ids;

            // получить вопросы для опросника
            if (!empty($ids)) {
                $sql = 'SELECT * FROM question WHERE id IN ('. \R::genSlots($ids) . ')';
                $questions = $this->model->execute($sql, $ids);
                forEach($questions as &$item) {
                    $item['answers'] = json_decode($item['answers']) ?: (object)[];
                }
            }
            $quiz['questions'] = isset($questions) ? $questions : [];

            // список активных пользователей практикума
            $sql = "SELECT id, name, surname, stream_id FROM user WHERE workshop_id = " . $workshop['id'] . " AND status = 'active'";
            $users = $this->model->execute($sql);

            // список уже аттестованных пользователей данным пользователем
            $sql = "SELECT attested FROM appraisal 
                    WHERE certifier = " . $userId .
                    " AND event = " . $workshop['eventsCurrent'] .
                    " AND module = " . $workshop['modulesCurrent'] .
                    " AND workshop_id = " . $workshop['id'];
            $attestedUsers = array_map(function ($item) {
                return $item['attested'];
            }, $this->model->execute($sql));

            // список пользователей для аттестации (не содердит самого пользователся и уже аттестованным им пользователей)
            $usersTobeAttested = [];
            forEach ($users as $user) {
                if (!in_array($user['id'], $attestedUsers) && $user['id'] !== $userId) {
                    $usersTobeAttested[] = $user;
                }
            }

            echo json_encode(['workshop' => $workshop, 'quiz' => $quiz, 'users' => $usersTobeAttested]);
        }
    }

    public function createAction () {
        $this->authUser();
        $data = $this->model->load();

        if (!empty($data)) {
            $values = [$data['certifier'], $data['attested'], $data['workshopId'], $data['streamId'], $data['module'], $data['event']];
            $exist = \R::count('appraisal', "certifier = ? AND attested = ? AND workshop_id = ? AND stream_id = ? AND module = ? AND event = ?" , $values);

            // проверка повторной аттестации
            if ($exist > 0) {
                throw new \Error("Вы уже аттестовывали данного участника.", 400);
            }
            // проверка аттестации самого себя
            if ($data['certifier'] === $data['attested']) {
                throw new \Error("Вы не можете аттестовывать самого себя.", 400);
            }

            // поиск рейтинговых вопросов (свойство ratingRange) и расчет рейтинга
            $rating = 0;
            forEach ($data['result'] as $result) {
                if (isset($result->ratingRange)) {
                    $result->answer = (int)$result->answer;
                    $rating += $result->answer;
                }
            }
            $data['rating'] = $rating;
            $data['result'] = json_encode($data['result'], JSON_UNESCAPED_UNICODE);

        } else {
            throw new \Error("Нет данных в ответе.", 400);
        }

        $id = $this->model->save('appraisal', $data);
        echo json_encode($id);
        http_response_code(204);
    }

    // public function rating () {
    //     $userId = $this->authUser();
    //
    //     // получить активный практикум
    //     $workshop = $this->model->getRecordBy('status', 'active', 'workshop');
    //
    //     if (!$workshop) {
    //         throw new \Error("Не задан активный Практикум.", 400);
    //     } else {
    //         $sql = "SELECT id, name, surname FROM appraisal WHERE workshop_id = " . $workshop['id'] . " AND status = 'active'";
    //         $users = $this->model->execute($sql);
    //     }
    // }
}