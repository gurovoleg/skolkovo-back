<?php


namespace app\controllers;

use app\models\Report;
use core\base\Controller;


class ReportController extends Controller {

    public function __construct($route) {
        parent::__construct($route);
        $this->model = new Report();
    }

    public function indexAction() {
        $this->authUser();

        $workshop = $this->route['params'];
        $data = $this->model->execute("SELECT * FROM statistics WHERE workshop_id = ?", [$workshop]);

        foreach ($data as &$event) {
            $usersData = json_decode($event['result']);
            $event['result'] = $usersData;
            $event['attestedUsers'] = count($usersData);
        }

        echo json_encode($data);
    }

}