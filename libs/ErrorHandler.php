<?php


namespace libs;


class ErrorHandler {
    public $type;

    public function __construct () {
        if (DEBUG) {
            // включаем максимальный уровень ошибок (например покажет необъявленную переменную)
            error_reporting(-1);
        } else {
            error_reporting(0);
        }
        // задаем обработчик ошибок для стандартных типов
        set_error_handler([$this, 'errorHandler']);
        // включаем запись в буфер, чтобы убрать вывод фатальных ошибок
        ob_start();
        // подключаем обработчик фатальных ошибок, запускается всегда по завершению работы скрипта
        register_shutdown_function([$this, 'fatalErrorHandler']);
        // подключаем обработчик исключений
        set_exception_handler([$this, 'exceptionHandler']);
    }

    protected function logError ($message = '', $file = '', $line = '') {
        $date = date('Y-m-d H:i:s');
        $message = "[" . $date . "]" .  " Текст ошибки: $message. Файл: $file. Строка: $line. \n";
        error_log($message, 3, ROOT . '/log/' . date('Y-m-d') . '.txt');
    }

    // стандартный обработчки
    public function errorHandler ($errno, $errstr, $errfile, $errline) {
        $this->type = 'errorHandler';
        $this->logError($errstr, $errfile, $errline);
        $this->displayError($errno, $errstr, $errfile, $errline);
        return true;
    }

    // обработчик фатальных ошибок
    public function fatalErrorHandler () {
        $this->type = 'fatalErrorHandler';
        $error = error_get_last(); // получаем последнюю ошибку
        if (!empty($error) && $error['type'] & (E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR)) {
            ob_end_clean(); // чистим буфер, чтобы ничего не выводить и закрываем
            $this->logError($error['message'], $error['file'], $error['line']);
            $this->displayError($error['type'], $error['message'], $error['file'], $error['line'] );
        } else {
            ob_end_flush(); // закрываем и отправляем буфер
        }
    }

    protected function displayError ($errno, $errstr, $errfile, $errline, $response = 500) {
        $response = $response < 400 ? 500 : $response;
        http_response_code($response); // отправляем код ответа
        $metadata = Array('errorType' => $this->type, 'file' => $errfile, 'string' => $errline);
        $error = Array('status' => $response, 'message' => $errstr, 'metadata' => $metadata);
        echo json_encode($error, JSON_UNESCAPED_UNICODE);
        // if ($response === 404 && !DEBUG) {
        //     require APP . '/views/error/404.html';
        // }
        // if (DEBUG) {
        //     require APP . '/views/error/dev.php';
        //     echo '';
        // } else {
        //     require APP . '/views/error/prod.php';
        // }
        die();
    }

    // обработчик исключений
    public function exceptionHandler (\Throwable $e) {
        $this->type = 'exceptionHandler';
        $this->logError($e->getMessage(), $e->getFile(), $e->getLine());
        $this->displayError('Исключение', $e->getMessage(), $e->getFile(), $e->getLine(), $e->getCode());
    }

}

new ErrorHandler();

// Тесты
// test();
// echo $test;
// throw new \Exception('А вот и исключение!');


