<?php

function debug ($arr) {
    echo '<pre>' . print_r($arr, true) . '</pre>';
}

function print_arr ($array) {
    echo '<pre>';
    print_r ($array);
    echo '</pre>';
}

function object_to_array ($object) {
    $result = [];
    forEach ($object as $key => $user) {
        $result[] = $user;
    }
    return $result;
}

function camelToSnake($input) {
    return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
}
function snakeToCamel($input) {
    return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
}

// исключить поля из записей
function excludeFields ($data, $fields) {
    forEach($data as &$record) {
        forEach($fields as $field) {
            if (array_key_exists($field, $record)) {
                unset($record[$field]);
            }
        }
    }
    return $data;
}

// оставить поля в записи
function filterFields ($record, $fields) {
    forEach($record as $key => $value) {
        if (!in_array($key, $fields)) {
            unset($record[$key]);
        }

    }
    return $record;
}

// объединить свойства заданного формата (parent_prop1, parent_prop2) в один массив (parent = ['prop1' => value1, 'prop2' => value2])
function uniteProps ($item, $indicators = null, $separator = '_') {
    $result = [];

    foreach ($item as $key => $value) {
        $data = explode($separator, $key);
        if (isset($data[1]) && (!isset($indicators) || in_array($data[0], $indicators))) {
            $result[$data[0]][$data[1]] = $value;
        } else {
            $result[$key] = $value;
        }
    }

    return $result;
}

// разбить массив свойств (parent = ['prop1' => value1, 'prop2' => value2]) на отдельные свойства согласно формату (parentProp1, parentProp2)
function disuniteProps ($item, $indicators = null, $separator = '') {
    $result = [];

    foreach ($item as $key => $value) {
        if (!isset($indicators) || in_array($key, $indicators)) {
            forEach ($value as $idx => $v) {
                if ($separator) {
                    $result["{$key}{$separator}{$idx}"] = $v;
                } else {
                    $result[$key . ucfirst($idx)] = $v;
                }
            }
        } else {
            $result[$key] = $value;
        }
    }

    return $result;
}