<?php

namespace App\Repositories;


use App\Models\Office;
use Cache;

class OfficesRepository extends AbstractRepo
{
    /**
     * @var OfficesRepository|null
     */
    private static $_instance = null;

    const CACHE_KEY_ALL = 'officesAll';
    const CACHE_KEY_TIME = 'officesRedisTime';

    /**
     * @var bool
     */
    private $loaded;

    /**
     * @var array
     */
    private $offices = null;

    /**
     * TypesRepository constructor.
     */
    protected function __construct()
    {
        $this->setModel(new Office());
        $this->setCacheKey('officesRepo');
        $this->setCacheTime(SettingsRepository::get(self::CACHE_KEY_TIME)? : 180);
    }

    /**
     * закрываем клон - синглтон
     */
    protected function __clone()
    {
        //
    }

    /**
     * @return OfficesRepository|null
     */
    static public function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    /**
     * @return boolean
     */
    public function isLoaded()
    {
        return $this->loaded;
    }

    /**
     * @param boolean $loaded
     */
    public function setLoaded($loaded)
    {
        $this->loaded = $loaded;
    }

    /**
     * @return array|mixed
     * @throws \ErrorException
     */
    public function getData()
    {
        if (!$this->isLoaded()) {
            $this->offices = $this->rememberCache(self::CACHE_KEY_ALL, function () {
                $offices = Office::all();
                $result = [];
                $offices->each(function ($item) use (&$result) {
                    $result[$item->id] = $item;
                });
                return $result;
            });

            $this->setLoaded(true);
        }

        return $this->offices;
    }

    /**
     * @return array|mixed
     * @throws \ErrorException
     */
    public static function getAll()
    {
        $instance = self::getInstance();
        return $instance->getData();
    }

    /**
     * @param $typeId
     * @return null |null
     * @throws \ErrorException
     */
    public static function getOfficeById($typeId)
    {
        $office = self::getAll();
        return isset($office[$typeId]) ? $office[$typeId] : null;
    }

    /**
     * @return bool
     */
    public static function cacheClear()
    {
        Cache::tags('officesRepo')->flush();
    }
}
