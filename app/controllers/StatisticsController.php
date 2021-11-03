<?php


namespace app\controllers;

use app\models\Statistics;
use core\base\Controller;
use Mpdf\Mpdf;


class StatisticsController extends Controller {

    public function __construct($route)
    {
        parent::__construct($route);
        $this->model = new Statistics();
    }

    // получаем итоги по всем событиям практикума
    public function indexAction() {
        $this->authUser();

        $workshop = $this->route['params'];
        $data = $this->model->execute("SELECT * FROM statistics WHERE workshop_id = ?", [$workshop]);

        foreach ($data as &$event) {
            $usersData = json_decode($event['result']);
            // получам список вопросов
            $questionIds = array_keys((array)$usersData[0]->questions);
            $questions = \R::getAll("SELECT * from question WHERE id IN (" . \R::genSlots($questionIds) . ")", $questionIds);
            $event['questions'] = $questions;
            $event['result'] = $usersData;
            // Общее количество аттестованных пользователей в событии
            $event['attestedUsers'] = count((array)$usersData);
        }

        echo json_encode($data);
    }

    // подчситываем средний рейтинг в указанном массиве (для пользователя в рамках события)
    private function calculateRating ($value, &$target, $ratingRange = null) {
        if (isset($target['rating']['score'])) {
            $target['rating']['score'] += $value;
            $target['rating']['count'] += 1;
        } else {
            $target['rating']['score'] = (float)$value;
            $target['rating']['count'] = 1;
        }
        $target['rating']['value'] = number_format(round($target['rating']['score'] / $target['rating']['count'], 1), 1, '.', '');
        if ($ratingRange) {
            $target['rating']['range'] = (int)$ratingRange;
        }
    }

    private function calculateSummary ($records) {
        $events = []; // массив событий
        foreach ($records as $record) {
            // создаем ключ (userId) для пользователя в массиве
            $events[ $record['event'] ][ $record['attested'] ]['userId'] = $record['attested'];
            // ссылка на пользователя в массиве events
            $user = &$events[ $record['event'] ][ $record['attested'] ];

            // подсчет общего рейтинга пользовтеля (входят значения всех рейтинговых вопросов из опросника)
            // приходит в отдельном поле record['rating'] (сумма рейтингов всех вопросов)
            if ($record['rating'] > 0) {
                $this->calculateRating($record['rating'], $user);
            }

            // подсчет итогов по каждому вопросу
            $answers = json_decode($record['result']);

            foreach ($answers as $q) {
                // ссылка на вопрос в массиве ответов
                $question = &$user['questions'][ $q->id ];

                // рейтинговый вопрос (свойство ratingRange - диапазон значений рейтинга)
                if (isset($q->ratingRange)) {
                    $this->calculateRating($q->answer, $question, $q->ratingRange);
                } else {
                    $question[$q->answer] = isset($question[$q->answer]) ? $question[$q->answer] + 1 : 1;
                }
            }
        }

        return $events;
    }

    // сохраняем итоги по событию в statistics
    private function saveEventSummary ($workshopId, $event, $data, $sortBy = 'rating') {
        // преобразуем ассоциативный массив в обычный
        $data = array_values($data);

        // сортировка (по общему рейтингу по умолчанию)
        usort($data, function ($a, $b) use ($sortBy) {
            if ($a[$sortBy] == $b[$sortBy]) return 0;
            return ($a[$sortBy] > $b[$sortBy]) ? -1 : 1;
        });

        // расчет позиции в рейтинге
        $this->calculateRatingPosition($data);

        $statistics = \R::findOne($this->model->table, "workshop_id = ? AND event = ?", [$workshopId, $event]);

        if (empty($statistics)) {
            $statistics = \R::dispense('statistics');
        }

        $statistics->workshop_id = $workshopId;
        $statistics->event = $event;
        $statistics->result = json_encode($data, JSON_UNESCAPED_UNICODE);
        $statistics->created = time();

        // Удаляем из кеша последний отчет
        $file = CACHE . '/' . 'workshop' . $workshopId . '-event' . $event . '.pdf';
        if (file_exists($file)) {
            unlink($file);
        }

        return \R::store($statistics);
    }

    // расчет позиции в рейтинге; пользователи с одинаковым рейтингом имеют одинаковую позицию, следующая позиция расчитывается
    // с учетом количества пользователей на предыдущей позиции (то есть если имеется три первых места, то следующая позиция в рейтинги - четвертая)
    private function calculateRatingPosition (&$users) {
        $position = 1;
        $count = 0;

        for ($i = 0; $i < count($users); $i++) {
            if ($i === 0 || $users[$i]['rating']['value'] === $users[$i - 1]['rating']['value']) {
                $count++;
            } else {
                $position += $count;
                $count = 1;
            }

            $users[$i]['rating']['position'] = $position;
        }
    }

    // генерация PDF документа по результатам события
    public function eventPDFAction() {
        $this->authUser();
        $workshopId = $this->route['params'];
        $event = $this->route['props'];

        $fileName = 'workshop' . $workshopId . '-event' . $event . '.pdf';
        if (!file_exists(CACHE . '/' . $fileName)) {

            // Данные по практикуму
            $workshop = \R::findOne('workshop',  "id = ?", [$workshopId]);
            $workshopTitle = $workshop->title;

            // Данные по событию
            $data = \R::findOne($this->model->table,  "workshop_id = ? AND event = ?", [$workshopId, $event]);
            $result = json_decode($data->result);
            $total = count((array)$result);

            // Пользователи
            $users = \R::getAll("SELECT id, name, surname FROM user WHERE workshop_id = ?", [$workshopId]);
            $ids = array_column($users, 'id');

            ob_start();
            include APP . '/views/Statistics/eventPDFTemplate.php';
            $page = ob_get_clean();

            $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
            $mpdf->WriteHTML($page);
            $mpdf->Output(CACHE . '/' . $fileName, \Mpdf\Output\Destination::FILE);
        }

        $pdf = file_get_contents(CACHE . '/' . $fileName);
        header('Content-Description: File Transfer');
        header('Content-Transfer-Encoding: binary');
        header('Content-type: application/pdf');
        echo $pdf;

    }

    // подводим итог по одному событию практикума и сохраняем в таблицу statistics
    public function eventAction() {
        $this->authUser();
        $workshop = $this->route['params'];
        $event = $this->route['props'];

        if (!empty($event) && !empty($workshop)) {
            $result = \R::getAll("SELECT * FROM appraisal WHERE workshop_id = ? AND event = ?", [$workshop, $event]);

            if (count($result) > 0) {
                $events = $this->calculateSummary($result);

                if (!empty($events) && count($events) === 1) {
                    $this->saveEventSummary($workshop, $event, reset($events));
                    http_response_code(204);
                }

            } else {
                throw new \Error ('Не найдено аттестаций по данному событию.', 400);
            }

        } else {
            throw new \Error ('Не указан практикум или событие.', 400);
        }

    }

    // подводим итоги по всем событим практикума и сохраняем в таблицу statistics
    public function eventsAction() {
        $this->authUser();
        $workshop = $this->route['params'];

        if (!empty($workshop)) {
            $result = \R::getAll("SELECT * FROM appraisal WHERE workshop_id = ?", [$workshop]);

            if (count($result) > 0) {
                $events = $this->calculateSummary($result);

                if (!empty($events) && count($events) > 0) {
                    forEach ($events as $event => $data) {
                        $this->saveEventSummary($workshop, $event, $data);
                    }
                    http_response_code(204);
                }

            } else {
                throw new \Error ('Не найдено аттестаций в указанном практикуме.', 400);
            }

        } else {
            throw new \Error ('Не указан практикум.', 400);
        }

    }

}