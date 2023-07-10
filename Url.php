<?php

namespace Kalinger;

class Url {

    public static function translite($to_url) {

        $url = mb_strtolower(trim($to_url)); // убираем крайние пробелы и переводим в нижний регистр
        $url = transliterator_transliterate('Russian-Latin/BGN', $url); // Транслитерация
        $url = preg_replace ('/[^a-zA-ZА-Яа-я0-9\s]/','', $url); // Удаляем спецсимволы
        $url = str_ireplace(' ', '-', $url); // Заменяем пробелы на черточки
        $url = str_ireplace('ʹ', '', $url); // Заменяем мягкий знак на тире

        return $url;

    }

}