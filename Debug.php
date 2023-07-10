<?php
/**
 * Created by PhpStorm.
 * User: web-progr
 * Date: 02.09.2020
 * Time: 17:20
 */

namespace Kalinger;

class Debug {

    public static function log($str = NULL) {

        echo '<pre>';
        var_dump($str);
        echo '</pre>';

        exit;

    }

    public static function writeLogToFile($log) {

        if (is_array($log)) {

            $log = print_r($log, true);

        }

        file_put_contents(WWW . '/log.txt', '----------------------------------' . PHP_EOL, FILE_APPEND);
        file_put_contents(WWW . '/log.txt', date('Y-m-d H:i:s')  . PHP_EOL, FILE_APPEND);
        file_put_contents(WWW . '/log.txt', $log . PHP_EOL, FILE_APPEND);

    }

}