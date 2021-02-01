<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 13.08.2018
 * Time: 12:14
 */

namespace App\Services;

use App\Models\Settings as SettingsModel;

class Settings
{
    private static $data = false;

    public function __construct()
    {
        static::load();
    }

    public static function load()
    {
        if (!static::$data) {

            $rawData =   SettingsModel::all()->toArray();

            $data = [];
            foreach ($rawData as $setting){
                $data[$setting['name']] = $setting['value'];
            }

            static::$data = $data;
        }

    }

    public function get(string $key)
    {
        if (!static::$data) {
            static::load();
        }

        if (!isset(static::$data[$key])) {
            return false;
        }

        return static::$data[$key];
    }

    public function all()
    {
        return static::$data;
    }
}