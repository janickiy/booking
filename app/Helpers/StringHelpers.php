<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 19.04.2018
 * Time: 13:25
 */

namespace App\Helpers;

class StringHelpers
{
    /**
     * @param $string
     * @return string
     */
    public static function remap($string)
    {
        $en = explode(' ', 'q w e r t y u i o p [ ] a s d f g h j k l ; \' z x c v b n m , . Q W E R T Y U I O P { } A S D F G H J K L : " Z X C V B N M < >');
        $ru = explode(' ', 'й ц у к е н г ш щ з х ъ ф ы в а п р о л д ж э я ч с м и т ь б ю Й Ц У К Е Н Г Ш Щ З Х Ъ Ф Ы В А П Р О Л Д Ж Э Я Ч С М И Т Ь Б Ю');
        $ruEn = [];
        $enRu = [];

        foreach ($ru as $i => $l) {
            $ruEn[$l] = $en[$i];
            $enRu[$en[$i]] = $l;
        }

        if (in_array(mb_substr($string, 0, 1), $ru)) {
            return strtr($string, $ruEn);
        } else {
            return strtr($string, $enRu);
        }
    }

    /**
     * @param $string
     * @return string
     */
    public static function fromCamelCase($string)
    {
        $mapLetters = explode(' ', 'Q W E R T Y U I O P A S D F G H J K L Z X C V B N M');
        $map = [];

        foreach ($mapLetters as $letter) {
            $map[$letter] = ' ' . $letter;
        }

        return trim(strtr($string, $map));
    }

    /**
     * @param $data
     * @return array
     */
    public static function ObjectToArray($data)
    {
        if (is_array($data) || is_object($data)) {
            $result = array();
            foreach ($data as $key => $value) {
                $result[$key] = self::ObjectToArray($value);
            }
            return $result;
        }
        return $data;
    }

    /**
     * @param int $max
     * @return null|string
     */
    public static function randomText($max = 6)
    {
        $chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
        $size = strlen($chars) - 1;
        $text = null;

        while ($max--)
            $text .= $chars[rand(0, $size)];

        return $text;
    }

    /**
     * @param $el
     * @param bool $first
     * @return string
     */
    public static function tree($el, $first = true)
    {
        if (is_object($el)) $el = (array)$el;

        if ($el) {

            if ($first) {
                $out = '<ul id="tree-checkbox" class="tree-checkbox treeview">';
            } else {
                $out = '<ul>';
            }

            foreach ($el as $k => $v) {

                if (is_object($v)) $v = (array)$v;

                if ($v) {

                    $out .= "<li><strong> " . $k . " :</strong> ";

                    if (is_array($v)) {
                        $out .= self::tree($v, false);

                    } else {
                        $out .= $v;
                    }

                    $out .= "</li>";
                }
            }

            $out .= "</ul>";

            return $out;
        }
    }

    /**
     * @param $text
     * @return false|mixed|null|string|string[]
     */
    public static function slug($text, $toLower = true)
    {
        $text = trim($text);

        $tr = [
            "А" => "A",
            "Б" => "B",
            "В" => "V",
            "Г" => "G",
            "Д" => "D",
            "Е" => "E",
            "Ё" => "E",
            "Ж" => "J",
            "З" => "Z",
            "И" => "I",
            "Й" => "Y",
            "К" => "K",
            "Л" => "L",
            "М" => "M",
            "Н" => "N",
            "О" => "O",
            "П" => "P",
            "Р" => "R",
            "С" => "S",
            "Т" => "T",
            "У" => "U",
            "Ф" => "F",
            "Х" => "H",
            "Ц" => "TS",
            "Ч" => "CH",
            "Ш" => "SH",
            "Щ" => "SCH",
            "Ъ" => "",
            "Ы" => "YI",
            "Ь" => "",
            "Э" => "E",
            "Ю" => "YU",
            "Я" => "YA",
            "а" => "a",
            "б" => "b",
            "в" => "v",
            "г" => "g",
            "д" => "d",
            "е" => "e",
            "ё" => "e",
            "ж" => "j",
            "з" => "z",
            "и" => "i",
            "й" => "y",
            "к" => "k",
            "л" => "l",
            "м" => "m",
            "н" => "n",
            "о" => "o",
            "п" => "p",
            "р" => "r",
            "с" => "s",
            "т" => "t",
            "у" => "u",
            "ф" => "f",
            "х" => "h",
            "ц" => "ts",
            "ч" => "ch",
            "ш" => "sh",
            "щ" => "sch",
            "ъ" => "y",
            "ы" => "yi",
            "ь" => "",
            "э" => "e",
            "ю" => "yu",
            "я" => "ya",
            "«" => "",
            "»" => "",
            "№" => "",
            "Ӏ" => "",
            "’" => "",
            "ˮ" => "",
            "_" => "-",
            "'" => "",
            "`" => "",
            "^" => "",
            "\." => "",
            "," => "",
            ":" => "",
            ";" => "",
            "<" => "",
            ">" => "",
            "!" => "",
            "\(" => "",
            "\)" => ""
        ];

        foreach ($tr as $ru => $en) {
            $text = mb_eregi_replace($ru, $en, $text);
        }

        if ($toLower) {
            $text = mb_strtolower($text);
        }

        $text = str_replace(' ', '-', $text);

        return $text;
    }

    /**
     * @param $str
     * @param int $chars
     * @return string
     */
    public static function shortText($str, $chars = 500)
    {
        $pos = strpos(substr($str, $chars), " ");
        $srttmpend = strlen($str) > $chars ? '...' : '';

        return substr($str, 0, $chars + $pos) . (isset($srttmpend) ? $srttmpend : '');
    }

    /**
     * @param $array
     * @param $keySearch
     * @return bool
     */
    public static function multiKeyExists(array $arr, $key)
    {

        if (in_array($key, $arr)) {
            return true;
        }

        foreach ($arr as $element) {
            if (is_array($element)) {
                if (self::multiKeyExists($element, $key)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $array
     * @return array
     */
    public static function convertToCamelCase($array)
    {
        $finalArray = [];

        foreach ($array as $key => $value) {
            $key = lcfirst($key);
            if (!is_array($value))
                $finalArray[$key] = $value;
            else
                $finalArray[$key] = self::convertToCamelCase($value);
        }

        return $finalArray;
    }

    /**
     * @param $array
     * @return array
     */
    public static function convertKeysToCamelCase($array)
    {
        $result = [];

        array_walk_recursive($array, function ($value, &$key) use (&$result) {
            $newKey = preg_replace_callback('/_([az])/', function ($matches) {
                return strtoupper($matches[1]);
            }, $key);
            $result[$newKey] = $value;
        });

        return $result;
    }

    /**
     * @param $str
     * @param int $start
     * @param int $end
     * @return mixed
     */
    public static function hidePartText($str, $start = 3, $end = 4)
    {
        for ($i = $start; $i < strlen($str) - $end; $i++)
            $str[$i] = '*';

        return $str;
    }

    /**
     * Generate a six digits code
     *
     * @param int $codeLength
     * @return string
     */
    public static function generateCode($codeLength = 4)
    {
        $min = pow(10, $codeLength);
        $max = $min * 10 - 1;
        $code = mt_rand($min, $max);

        return $code;
    }

    /**
     * @param $num
     * @return string
     */
    public static function getPaymentSystem($num)
    {
        if (substr($num, 0, 1) == 2) {
            return 'Мир';
        } else if (substr($num, 0, 1) == 3) {
            switch (substr($num, 0, 2)) {
                case 30:
                case 36:
                case 38:
                    return 'Diners Club';
                    break;
                case 31:
                case 35:
                    return 'JCB International';
                    break;
                case 34:
                case 37:
                    return 'American Express';
                    break;
            }
        } else if (substr($num, 0, 1) == 4) {
            return 'VISA';
        } else if (substr($num, 0, 1) == 5) {
            switch (substr($num, 0, 2)) {
                case 50:
                case 56:
                case 57:
                case 58:
                    return 'Maestro';
                    break;
                case 51:
                case 52:
                case 53:
                case 54:
                case 55:
                    return 'MasterCard';
                    break;
            }
        } else if (substr($num, 0, 1) == 6) {
            switch (substr($num, 0, 2)) {
                case 63:
                case 67:
                    return 'Maestro';
                    break;
                case 62:
                    return 'China UnionPay';
                case 60:
                    return 'Discover';
            }

        } else if (substr($num, 0, 1) == 7) {
            return 'УЭК';
        }

        return 'unknown';

    }

    /**
     * @param $id
     * @return string
     */
    public static function getCitizenship($id)
    {
        $citizenship = \App\Models\Admin\Hotel\HotelsCitizenship::find($id);

        return $citizenship ? $citizenship->name_ru : '';
    }

    /**
     * @param $id
     * @return mixed
     */
    public static function getUserById($id)
    {
        $user = \App\Models\User::find($id);

        return $user->email ?? $user;
    }
}