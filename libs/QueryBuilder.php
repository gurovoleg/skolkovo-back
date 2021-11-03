<?php


namespace libs;

/*
 *  Класс для обработки параметров адресной строки (QUERY_STRING) и составления запроса на основе этих параметров
 */

class QueryBuilder {
    private $table = '';

    /*
     * массив полей, который может быть использован для запроса
     * @var array
     */
    private $allowed = [];

    public $select = ''; // часть запроса - SELECT
    public $orderBy = ''; // часть запроса - ORDER BY
    public $limit = ''; // часть запроса - LIMIT
    public $query = ''; // полный запрос

    /*
     * параметры полей и их значений + строка для sql запроса
     * @var array
     */
    public $where = [];

    /*
     * кол-во записей в таблице согласно полученным параметрам
     * @var number
     */
    public $total = null;

    public function __construct($allowed, $table, $select = '', $total = null) {
        $this->allowed = $allowed;
        $this->table = $table;
        $this->select = $this->createSelect($select);
        $this->where = $this->createWhere($allowed);
        $this->orderBy = $this->createOrderBy();
        $this->total = $this->getTotal($total);
    }

    /*
     * получение строки для sql запроса; часть запроса SELECT
     * @var array
     */
    private function createSelect ($select) {
        return $select ? $select : 'SELECT * from ' . $this->table;
    }

    /*
     * получение строки для sql запроса; часть запроса WHERE (согласно переданным разрешенным значениям)
     * + отдельные наборы полей и их значений
     * @var array: Array('fields' => [], 'values' => [], 'sql' => '')
     */
    private function createWhere ($allowed) {
        $result = ['fields' => [], 'values' => [], 'sql' => ''];
        if (count($_GET)) {
            forEach ($_GET as $key => $value) {
                if (array_key_exists($key, $allowed)) {
                    $key = camelToSnake($key);
                    $result['values'][] = $value;
                    $result['fields'][] = "$this->table.$key";
                    $result['sql'] .= $result['sql'] ? " AND $this->table.$key = ?" : "$this->table.$key = ?";
                    // $result['sql'] .= $result['sql'] ? " AND $key = ?" : "$key = ?";
                }
            }
        }
        if ($result['sql']) {
            $result['sql'] = " WHERE {$result['sql']}";
        }
        // echo json_encode($result);
        // die();
        return $result;
    }

    /*
     * получение строки для sql запроса; часть запроса ORDER BY
     * @var string
     */
    private function createOrderBy () {
        $result = '';
        if (isset($_GET['sort']) && !empty($_GET['sort'])) {
            $data = explode('-', $_GET['sort']);
            if (!empty($data[0])) {
                $result .= $data[0];
                $result .= !empty($data[1]) && $data[1] === 'desc' ? " DESC" : " ASC";
            }
            if ($result) {
                $result = ' ORDER BY ' . $result;
            }

        }
        return $result;
    }

    /*
     *  Метод используется с библиотекой RedBean для подсчета общего количества записей согласно
     *  текущим параметрам запроса (where), если параметр не был передан снаружи
     *  @var number
     */
    private function getTotal ($total) {
        if (isset($total) && $total > 0) {
            return $total;
        }
        if (class_exists('R') && $this->table) {
            return \R::count($this->table, $this->where['sql'], $this->where['values']);
        }
    }

    /*
     *  Собираем итоговый запрос без значений. Значения передаются в параметре where для исключения инъекций.
     *  @var string
     */
    public function sql ($pagination = null) {
        $this->query = $this->select . $this->where['sql'] . $this->orderBy;
        if (isset($pagination)) {
            $this->limit = " LIMIT $pagination->startRecord, $pagination->perPage";
            $this->query .= $this->limit;
        }

        return $this->query;
    }
}