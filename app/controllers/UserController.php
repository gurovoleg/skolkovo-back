<?php


namespace app\controllers;


use core\base\Controller;
use app\models\User;
use libs\Token;
use libs\Pagination;
use libs\QueryBuilder;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UserController extends Controller {

    public function __construct($route) {
        parent::__construct($route);
        $this->model = new User();
    }

    // список пользователей
    public function listAction () {
        $this->authUser();
        // подготовка запроса согласно параметрам адресной  строки
        $qb = new QueryBuilder($this->model->fields, $this->model->table, $this->model->sql['full']);

        if ($qb->total > 0) {
            $pagination = new Pagination($this->model->pagination['perPage'], $qb->total);
            $query = $qb->sql($pagination);
            $users = $this->model->execute($query, $qb->where['values']);
            $users = excludeFields($users, ['password']);
            echo json_encode(['data' => $users, 'pagination' => $pagination]);
        } else {
            echo json_encode([]);
        }
    }

    // регистрация пользователя
    public function registerAction ($withAuth = false) {
        list($user, $isExist) = $this->getAndCheckData('email', $withAuth );

        if ($isExist) {
            throw new \Error("Этот email уже занят", 500);
        } else {
            $result = $this->model->validate($this->model->rules['registration'], $user);
            if (!$result) {
                $message = $this->model->getErrors();
                throw new \Error($message, 500);
            } else {
                $this->model->hashPassword($user);
                $user['created'] = time();
                $user['updated'] = time();
                $this->model->save($this->model->table, $user);
                http_response_code(204);
            }
        }
    }

    // добавить пользователя другим пользователем
    public function createAction () {
        $this->registerAction(true);
    }

    // обновить пользователя
    public function updateAction () {
        list($user, $isExist) = $this->getAndCheckData('id');

        if (!$isExist) {
            throw new \Error('Пользователь ' . $user['id'] . ' не найден', 400);
        } else {
            $isValid = $this->model->validate($this->model->rules['update'], $user);

            if (!$isValid) {
                $message = $this->model->getErrors();
                throw new \Error($message, 400);
            } else {
                $this->model->hashPassword($user);
                $user['updated'] = time();
                $this->model->save($this->model->table, $user);
                http_response_code(204);
            }
        }
    }

    // удалить пользователя
    public function deleteAction () {
        $this->deleteRecord();
    }

    // логин
    public function loginAction () {
        $data = $this->model->load();

        $user = $this->model->login($data);
        if ($user) {
            $token = Token::createToken($user->id);
            $role = $user->role;
            echo json_encode(Array('token' => $token, 'role' => $role->title));
            // echo json_encode(['token' => $token, 'role' => $user->role]);
        } else {
            throw new \Error('Неверный email / пароль', 400);
        }
    }

    // загрузить профайл пользователя
    public function profileAction () {
        $userId = $this->authUser();
        // Запрос с id для любого пользователя; без id для владельца
        $id = !empty($this->route['params']) ? $this->route['params'] : $userId;

        $result = $this->model->loadProfile($id);

        if ($result) {
            // echo json_encode($result);
            echo json_encode($result);
        } else {
            throw new \Error ("Пользователь не найден", 401);
        }
    }

    // создать excel со всеми пользователями по указанному практикуму
    public function excelAction () {
        $this->authUser();

        $id = !empty($this->route['props']) ? $this->route['props'] : null;

        if ($id) {
            $users = $this->model->execute("SELECT * from user WHERE workshop_id = ?", [$id]);

            if (count($users) > 0) {

                $columns = array('A' => 'NAME', 'B' => 'SURNAME', 'C' => 'PATRONYMIC', 'D' => 'EMAIL', 'E' => 'GENDER', 'F' => 'AGE', 'G' => 'RATING', 'H' => 'HEADQUARTERS');

                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Колонки
                forEach ($columns as $column => $name) {
                    $sheet->setCellValue($column . '1', $name);
                }

                // Данные
                $i = 2;
                forEach($users as $user) {
                    forEach ($columns as $column => $name) {
                        $sheet->setCellValue($column . $i, $user[mb_strtolower($name)]);
                    }
                    $i++;
                }

                $writer = new Xlsx($spreadsheet);
                $file = CACHE . '/' . 'workshop' . $id . '.xlsx';
                $writer->save($file);

                $file = file_get_contents($file);
                header('Content-type: application/vnd.ms-excel');
                echo $file;

            } else {
                throw new \Error ("Нет данных по указанному запросу", 400);
            }

        } else {
            throw new \Error ("Не указан практикум", 400);
        }

    }

}