<?php


namespace app\controllers;


use core\base\Controller;
use app\models\Role;

class RoleController extends Controller {

    public function __construct($route) {
        parent::__construct($route);
        $this->model = new Role();
    }

    public function listAction () {
        $this->authUser();
        $data = $this->model->getRecords();
        echo json_encode($data);
    }
}