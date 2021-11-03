<?php


namespace fw\core\base;

// Базовый абстрактный класс для других
class View {
    public $route = []; // текущий маршрут
    public $view; // текущий вид
    public $layout; // текущий шаблон

    public function __construct($route, $layout = '', $view = '') {
        $this->route = $route;
        if ($layout === false) {
            $this->layout = false;
        } else {
            $this->layout = $layout ? $layout : LAYOUT;
        }

        $this->view = $view;
    }

    public function render($vars) {

        // извлекаем данные в виде соответствующих переменных из массива
        if (is_array($vars)) extract($vars);

        // Находим вид
        if (isset($this->route['prefix'])) {
            $file_view = APP . "/views/{$this->route['prefix']}/{$this->route['controller']}/{$this->view}.php";
        } else {
            $file_view = APP . "/views/{$this->route['controller']}/{$this->view}.php";
        }

        // сохраняем вид в буфер, чтобы далее подставить в шаблон (layout)
        ob_start();
        if (file_exists($file_view)) {
            require $file_view;
        } else {
            throw new \Exception("<p>Не найден вид <b>{$file_view}</b></p>", 404);
        }
        $content = ob_get_clean();

        // Если шаблон не отклчючен, то подключаем его
        if ($this->layout !== false) {
            // Находим шаблон
            $file_layout = APP . "/views/layouts/{$this->layout}.php";

            if (file_exists($file_layout)) {
                require $file_layout;
            } else {
                throw new \Exception("<p>Не найден шаблон <b>{$file_layout}</b></p>", 404);
            }
        }
    }

}