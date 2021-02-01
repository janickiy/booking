<?php

namespace App\Helpers;

use App\Models\TManager;

class LangHelper
{

    /**
     * @param $key
     * @return mixed|null
     */

    public static function trans($key, $replace=[])
    {
        if (!empty($key)) {
            $arr = explode(".", $key);
            if (isset($arr[0])) $key = str_replace($arr[0] . '.', '', $key);
            $group = $arr[0];

            $trans = TManager::where('group', $group)->where('key', $key)->where('locale', app()->getLocale());

            if ($trans->count() == 0) {
                $list = self::getTranslations($group);

                $lastKey = end($arr);

                if ($group != $lastKey) {
                    $list = isset($list[$lastKey]) ? $list[$lastKey] : null;
                }
            } else  {
                $list = $trans->first()->value;
            }
        }

        if (isset($list)) {
            $replacePairs = [];
            foreach ($replace as $key => $value){
                $replacePairs["{{$key}}"] = self::trans($value);
            }
            return is_string($list) ? strtr($list, $replacePairs) : $list;
        }else {
            return $key;
        }
    }

    /**
     * @param null $group
     * @return mixed
     */
    public static function getTranslations($group = null)
    {
        $tree = self::makeTree( TManager::ofTranslatedGroup( $group )
            ->orderByGroupKeys( array_get(config('translation-manager'), 'sort_keys', false ) )
            ->get() );

        if(count($tree)<1){
            return $group;
        }

        return  $group ? $tree[app()->getLocale()][$group] : $tree[app()->getLocale()];
    }

    /**
     * @param $translations
     * @param bool $json
     * @return array
     */
    protected static function makeTree( $translations, $json = false )
    {
        $array = [];
        foreach ( $translations as $translation ) {
            if ( $json ) {
               self::jsonSet( $array[ $translation->locale ][ $translation->group ], $translation->key,
                    $translation->value );
            } else {
                array_set( $array[ $translation->locale ][ $translation->group ], $translation->key,
                    $translation->value );
            }
        }

        return $array;
    }

    /**
     * @param $array
     * @param $key
     * @param $value
     * @return mixed
     */
    public static function jsonSet( &$array, $key, $value )
    {
        if ( is_null( $key ) ) {
            return $array = $value;
        }
        $array[ $key ] = $value;

        return $array;
    }
}