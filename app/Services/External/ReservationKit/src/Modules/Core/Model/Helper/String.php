<?php

/**
 * Класс для преобразования строковых переменных
 */
class RK_Core_Helper_String
{
    /**
     * Транслитерация
     *
     * @param $string
     * @return mixed
     */
    public static function translit($string)
    {
        return strtr($string, array('А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'YE', 'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'KH', 'Ц' => 'TS', 'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'SHCH', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA'));
    }

    public static function isJson($string)
    {
        json_decode($string);
        
        return (json_last_error() == JSON_ERROR_NONE);
    }
}