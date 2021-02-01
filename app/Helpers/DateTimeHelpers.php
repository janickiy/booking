<?php

namespace App\Helpers;


class DateTimeHelpers
{

    /**
     * @param $format
     * @param bool $timestamp
     * @return mixed
     */
    public static function ruDate($format, $timestamp = false)
    {
        if (!$timestamp) {
            $timestamp = time();
        }

        $monthsRod = [
            1 => 'января',
            'февраля',
            'марта',
            'апреля',
            'мая',
            'июня',
            'июля',
            'августа',
            'сентября',
            'октября',
            'ноября',
            'декабря'
        ];
        $months = [
            1 => 'январь',
            'февраль',
            'март',
            'апрель',
            'май',
            'июнь',
            'июль',
            'август',
            'сентябрь',
            'октябрь',
            'ноябрь',
            'декабрь'
        ];

        $index = date('n', $timestamp);
        if (mb_strlen($format, 'utf-8') > 1) {
            return str_replace('Я', $monthsRod[$index], date($format, $timestamp));
        } else {
            return str_replace('Я', $months[$index], date($format, $timestamp));
        }
    }

    /**
     * @param $date
     * @return mixed
     */
    public static function convertDate($date)
    {
        if ($date) {
            preg_match('/(\d\d\d\d-\d\d-\d\d)/',$date,$out);

            return $out[1];
        }

    }

    /**
     * @param string $datestr
     * @param bool $short
     * @return string
     */
    public static function dateFormat($datestr = '', $lang = 'ru', $short = false)
    {
        if ($datestr == '') return '';

        list($day) = explode(' ', $datestr);

        switch ($day) {
            case date('Y-m-d'):
                $date['ru'] = 'Сегодня';
                $date['en'] = 'today';
                $result = $date[$lang];
                break;

            case date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"))):
                $date['ru'] = 'Вчера';
                $date['en'] = 'yesterday';
                $result = $date[$lang];
                break;

            default:
                {
                    list($y, $m, $d) = explode('-', $day);

                    $month_str['ru'] = $short == true ? ['янв', 'фев', 'март', 'апр', 'мая', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноябр', 'дек'] : ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
                    $month_str['en'] = $short == true ? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Augt', 'Sept', 'Oct', 'Nov', 'Dec'] : ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                    $month_int = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
                    $m = str_replace($month_int, $month_str[$lang], $m);
                    $result = $d . ' ' . $m . ' ' . $y;
                }
        }

        return $result;
    }

    /**
     * @param string $datestr
     * @param bool $short
     * @return string
     */
    public static function dateTimeFormat($datestr = '', $lang = 'ru', $short = false)
    {

        if ($datestr == '') return '';

        $datestr = str_replace('T', ' ', $datestr);

        $dt_elements = explode(' ', $datestr);
        $date_elements = explode('-', $dt_elements[0]);
        $time_elements = explode(':', $dt_elements[1]);

        $result1 = mktime($time_elements[0], $time_elements[1], $time_elements[2], $date_elements[1], $date_elements[2], $date_elements[0]);
        $monthes['ru'] = $short == true ? [' ', 'янв', 'фев', 'март', 'апр', 'мая', 'июн', 'июл', 'авг', 'сент', 'окт', 'ноябр', 'дек'] : [' ', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
        $monthes['en'] = $short == true ? [' ', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'] : [' ', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
       // $days['ru'] = $short == true ? [' ', 'пон', 'вт', 'ср', 'чет', 'пят', 'суб', 'воск'] : [' ', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота', 'воскресенье'];
       // $days['en'] = $short == true ? [' ', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'] : [' ', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        $days['ru'] = [' ', 'пон', 'вт', 'ср', 'чет', 'пят', 'суб', 'воск'];
        $days['en'] = [' ', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];


        $day = date("j", $result1);
        $month = $monthes[$lang][date("n", $result1)];
        $year = date("Y", $result1);
        $hour = date("G", $result1);
        $minute = date("i", $result1);
        $dayofweek = $days[$lang][date("N", $result1)];
        $result = $dayofweek. ', ' . $day . ' ' . $month . ' ' . $year . '  ' . $hour . ':' . $minute;

        return $result;
    }
}