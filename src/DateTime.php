<?php

namespace Kalinger;

class DateTime {

    public function ru_date($format, $date = false) {

        setlocale(LC_ALL, 'ru_RU.utf8');

        if ($date === false) {

            $date = time();

        }

        if ($format === '') {

            $format = '%e&nbsp;%bg&nbsp;%Y&nbsp;г.';

        }

        $months = explode("|", '|января|февраля|марта|апреля|мая|июня|июля|августа|сентября|октября|ноября|декабря');

        $format = preg_replace("~\%bg~", $months[date('n', $date)], $format);

        return strftime($format, $date);

    }

}