<?php

// константы
define('DEBUG', 1); // Режим разработки (1 - development, 0 - prod)
define('ROOT', $_SERVER['DOCUMENT_ROOT']);
define('WWW', ROOT . '/public');
define('CORE', ROOT . '/core');
define('LIBS', ROOT . '/libs');
define('CACHE', ROOT . '/tmp/cache'); // кешированные данные
define('APP', ROOT . '/app');
define('LAYOUT', 'default'); // шаблон по умолчанию
define('HOST', '//' . $_SERVER['HTTP_HOST']);