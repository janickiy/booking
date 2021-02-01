<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 16.02.2018
 * Time: 13:28
 */

namespace App\Helpers;


class XMLHelpers
{

    public static function array2XML($array, $cn=false, $prevTag = 'items')
    {
        $data = '';
        if(!$cn){
            $data = '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL;
        }
        foreach ($array as $key => $val){
            if(is_array($val) || @get_class($val)=='stdClass'){
                $kKey = $key;
                if(is_int($key)){
                    $kKey = 'items';
                }
                $val = self::array2XML($val, true, $kKey);
            }
            if(is_int($key)){
                $key = substr($prevTag,0,strlen($prevTag)-1);
            }
            if($val!=''){
                $data.="<{$key}>".$val."</{$key}>".PHP_EOL;
            }else{
                $data.="<{$key} />".PHP_EOL;
            }
        }
        return $data;
    }

}