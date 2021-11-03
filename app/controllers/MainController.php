<?php


namespace app\controllers;


use core\base\Controller;
use core\base\Model;

class MainController extends Controller {

    public function indexAction () {
        require WWW . '/index.html';
    }

}