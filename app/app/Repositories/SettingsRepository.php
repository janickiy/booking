<?php namespace App\Repositories;

use App\Models\Settings;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;

/**
 * The repository of the settings.
 */
class SettingsRepository
{
    const CACHE_KEY = 'settings';

    const ALLOWED_BOOL_VALUES = ['0', '1', 'true', 'false'];

    private $settings;

    private static $instance = null;

    /**
     * @var array
     */
    private static $allowedKeys = [];

    /*
     * The singleton implementation.
     */
    private function __construct() {}
    private function __clone() {}
    private function __sleep() {}
    private function __wakeup() {}

    /**
     * @return self
     */
    public static function getInstance(): SettingsRepository
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
            self::$instance->loadSettings();
        }

        return self::$instance;
    }

    /**
     * @param mixed $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }


    /**
     * @param null $instance
     */
    public static function setInstance($instance)
    {
        self::$instance = $instance;
    }

    /**
     * @return array
     */
    public static function getAllowedKeys()
    {
        return self::$allowedKeys;
    }

    /**
     * @param array $allowedKeys
     */
    public static function setAllowedKeys($allowedKeys)
    {
        self::$allowedKeys = $allowedKeys;
    }

    private static function loadSettingsFromCache()
    {
        return Cache::remember(self::CACHE_KEY, 180, function () {
            try {
                $settings = Settings::all();
            } catch (QueryException $e) {
                return [];
            }
            if ($settings === null) {
                return [];
            }
            $result = $settings->pluck('value', 'name');
            return $result;
        });
    }

    private function loadSettings()
    {
        $this->settings = self::loadSettingsFromCache();

        return $this->settings;
    }

    /**
     * The alias for the {@link getValueForKey()}.
     *
     * @param mixed $key
     * @param bool  $default
     *
     * @return mixed
     */
    public static function get($key, $default = false)
    {
        return self::getValueForKey($key, $default);
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return bool
     * @throws Exception
     */
    public static function set($key, $value): bool
    {
        // TODO: надо сделать валидацию
//        if (!self::validateSettings([$key => $value])) {
//            return false;
//        }

        $setting = Settings::firstOrNew(['name' => $key]);
        $setting->conf_value = $value;

        return $setting->save();
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public static function has($key): bool
    {
        return isset(SettingsRepository::getInstance()->settings[$key]);
    }

    /**
     * @return array|null
     */
    public static function getSettings()
    {
        return SettingsRepository::getInstance()->settings;
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function getValueForKey($name, $default = false)
    {
        $settings = SettingsRepository::getInstance()->settings;

        return $settings[$name] ?? $default;
    }

    public static function cacheClear($reload = false)
    {
        $result = Cache::forget(self::CACHE_KEY);
        if ($result && $reload) {
            SettingsRepository::loadSettingsFromCache();
        }

        return $result;
    }
}
