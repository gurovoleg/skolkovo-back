<?php

use core\Db_RedBean;

Db_RedBean::getInstance();

function createUsers () {
    // for ($i = 1; $i < 101; $i++) {
    $user = \R::dispense('user');
    $user->name = "User2";
    $user->surname = "Userov2";
    $user->email = "test_2@mail.ru";
    $user->password = password_hash('11111', PASSWORD_DEFAULT);
    $user->roleId = 11;
    $user->created = time();
    $user->created = time();
    $user->status = 'active';
    \R::store($user);
    // }
}
