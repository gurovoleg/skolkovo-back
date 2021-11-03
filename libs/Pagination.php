<?php


namespace libs;


class Pagination {
    public $totalRecords; // общее количество записей
    public $currentPage; // текущая страница
    public $perPage; // кол-во записей на странице
    public $totalPages; // кол-во страниц
    public $startRecord; // запись, с которой осуществляем выборку в БД

    public function __construct ($perPage, $total) {
        $this->perPage = $this->getPerPage($perPage);
        $this->totalRecords = $total;
        $this->totalPages = $this->getTotalPages();
        $this->currentPage = $this->getCurrentPage();
        $this->startRecord = $this->getStart();
    }

    public function getPerPage ($perPage) {
        return isset($_GET['perPage']) ? (int)$_GET['perPage'] : $perPage;
    }

    public function getCurrentPage () {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page > $this->totalPages) $page = $this->totalPages;
        return $page;
    }

    public function getTotalPages () {
        $totalPages = ceil($this->totalRecords / $this->perPage);
        return $totalPages ? $totalPages : 1;
    }

    // Расчет стартовой записи для выборки из всез записей (записи индексированы с 0)
    public function getStart () {
        return ($this->currentPage - 1) * $this->perPage;
    }

}