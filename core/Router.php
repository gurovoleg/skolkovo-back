<?php


namespace core;


class Router {

    /*
    *  таблица маршрутов
    *  @var array
    */
    private static $routes = [];

    /*
    *  текущий маршрут
    *  @var array
    */
    private static $route = [];

    /*
    *  возвращает таблицу маршрутов
    *  @var array
    */
    public static function getRoutes() {
        return self::$routes;
        // return static::$routes;
        // return Router::$routes;
    }

    /*
    *  добаляет маршрут в таблицу
    *
    *  @param string $regexp регулярное выражение маршрута
    *  @param array $route маршрут ([controller, action, params])
    */
    public static function addRoute($regexp, $route = []) {
        self::$routes[$regexp] = $route;
    }

    /*
    *  возвращает текущий маршрут
    *  @var array
    */
    public static function getRoute() {
        return self::$route;
    }

    /*
    *  проверка существования маршрута
    *  @param string $url входящий URL
    */
    public static function matchRoute($url): bool {
        foreach (self::$routes as $pattern => $route) {
            // debug($url);
            // debug($pattern);
            // разбираем маршрут
            if (preg_match("#$pattern#i", $url, $matches)) {

                self::$route = $route;

                // ищем в matches строки (controller & action) и добавляем в текущий маршрут
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        self::$route[$key] = $value;
                    }
                }
                self::$route['controller'] = self::upperCamelCase(self::$route['controller']);

                // если action не указан, то задаем по умолчанию
                if (empty(self::$route['action'])) {
                    // if (!isset(self::$route['action'])) {
                    self::$route['action'] = 'index';
                }

                // debug(self::$route);
                // self::$route['searchParams'] = self::$searchParams;

                return true;
            }
        }

        return false;
    }

    /*
    *  перенаправляет URL по корректному маршруту
    *  @param string $url входящий URL
    */
    public static function dispatch($url) {
        // обрезаем GET параметры и кладем их в массив self::$searchParams
        $url = self::removeQueryString($url);

        if (self::matchRoute($url)) {
            // Проверяем на запрос в админку
            if (isset(self::$route['prefix'])) {
                $controller = 'app\controllers\\' . self::$route['prefix'] . '\\' . self::$route['controller'] . 'Controller';
            } else {
                $controller = 'app\controllers\\' . self::$route['controller'] . 'Controller';
            }

            if (class_exists($controller)) {
                // создаем объект контроллера
                $cObj = new $controller(self::$route);
                $action = self::lowerCamelCase(self::$route['action']);
                // добавляем в название метода Action, так как все методы доступные для пользователя мы заканчаваем на Action
                $action = $action . 'Action';
                // проверяем наличие метода у объекта и запускаем его, если есть
                if (method_exists($cObj, $action)) {
                    $cObj->$action(); // запускаем соответствующий метод, в котором можем получать данные для дальнейшего вывода (например запросы в БД)
                } else {
                    throw new \Exception("Контроллер $controller не имеет метода $action!", 404);
                }

            } else {
                throw new \Exception("Контроллер $controller не существует!", 404);
            }

        } else {
            throw new \Exception("Страница не найдена!", 404);
        }
    }

    // приводим к виду post-new -> PostNew
    protected static function upperCamelCase($name) {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $name)));
    }

    // приводим к виду post-new -> postNew
    protected static function lowerCamelCase($name) {
        return lcfirst(self::upperCamelCase($name));
    }

    // убираем параметры запроса (search string)
    protected static function removeQueryString($url) {
        if ($url) {
            $params = explode('?', $url);

            // парсим и сохраняем search params
            // if (!empty($params[1])) {
            //     self::parseQueryString($params[1]);
            // }

            // случай когда http://?page=1, то есть только параметры указаны без маршрута
            if (empty($params[0])) {
                return '';
            } else {
                return rtrim($params[0], '/');
            }
        }
    }

    // protected static function parseQueryString($string) {
    //     $searchData = explode('&', $string);
    //     foreach ($searchData as $key => $value) {
    //         $pair = explode('=', $value);
    //         self::$searchParams[$pair[0]] = $pair[1];
    //     }
    // }

}