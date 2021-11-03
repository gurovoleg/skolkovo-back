<?php


namespace libs;

/**
 * Class Cache
 * Класс для кеширования данных (tmp/cache)
 * @package libs
 */
class Cache {

    /**
     * Сохраняем данные в виде файлов в tmp/cach
     * @param $key
     * @param $data
     * @param int $seconds
     * @return bool
     */
    public function set ($key, $data, $seconds = 3600) {
        $file = CACHE . '/' . md5($key) . '.txt';
        $content['data'] = $data;
        $content['end_time'] = time() + $seconds;

        if (file_put_contents($file, serialize($content))) {
            return true;
        }
        return false;
    }

    public function get ($key) {
        $file = CACHE . '/' . md5($key) . '.txt';
        if (file_exists($file)) {
            $content = unserialize(file_get_contents($file));
            if ($content['end_time'] >= time()) {
                return $content['data'];
            }
            unlink($file);
        }
        return false;
    }

    public function delete ($key) {
        $file = CACHE . '/' . md5($key) . '.txt';
        if (file_exists($file)) {
            unlink($file);
        }
    }

}