<?php

namespace ReservationKit\src\Modules\Core\DB\Repository;

use ReservationKit\src\Component\DB\Adapter\AbstractAdapter;
use ReservationKit\src\RK;

/**
 * Абстрактный источник данных
 */
abstract class AbstractRepository
{
    /**
     * Интерфейс подключения к БД
     *
     * @var AbstractAdapter
     */
    protected $_adapter;

    /**
     * Кеш запросов к БД
     *
     * @var array
     */
    protected $_cache;

    public function __construct()
    {
        $this->_adapter = RK::getContainer()->getDbAdapterFor($this->getDbDomain());
        $this->_cache = array();
    }

    /**
     * Возвращает подключенный интрефейс к БД
     *
     * @return AbstractAdapter
     */
    public function getDB()
    {
        return $this->_adapter;
    }

    /**
     * Добавляет значение в кеш.
     * Не рекомендуется сохранять здесь значение false, так
     * как это возвращается методом getQueryCache как признак отсуствия записи
     *
     * @param string $query
     * @param mixed $result
     */
    public function addQueryCache($query, & $result)
    {
        $this->_cache[md5($query)] = $result;
    }

    /**
     * Возвращает значение из кеша или false
     *
     * @param string $query
     * @return mixed|false
     */
    public function getQueryCache($query)
    {
        $query = md5($query);
        
        return isset($this->_cache[$query]) ? $this->_cache[$query] : false;
    }

    /**
     * Запрос ряда данных через кеш
     *
     * @param string $query
     * @param array $params
     * @return array
     */
    public function & cachedFetchRow($query, $params = null)
    {
        return $this->cachedFetch('Row', $query, $params);
    }

    /**
     * Запрос рядов данных через кеш
     *
     * @param string $query
     * @param array $params
     * @return array
     */
    public function & cachedFetchArray($query, $params = null)
    {
        return $this->cachedFetch('Array', $query, $params);
    }

    /**
     * Запрос одной колонки через кеш
     *
     * @param string $query
     * @param array $params
     * @return mixed
     */
    public function & cachedFetchColumn($query, $params = null)
    {
        return $this->cachedFetch('Column', $query, $params);
    }

    /**
     * Запрос структуры данных через кеш
     *
     * @param string $method тип структуры Row/Array/Column
     * @param string $query
     * @param array $params
     * @return array
     */
    protected function & cachedFetch($method, $query, $params)
    {
        $method = 'fetch' . $method;
        $sql = $this->getDB()->prepare($query, $params);
        $cacheKey = md5($sql);
        
        if (isset($this->_cache[$cacheKey])) {
            return $this->_cache[$cacheKey];
        }

        $result = $this->getDB()->query($sql)->$method();
        $this->addQueryCache($sql, $result);

        return $result;
    }

    public function findById($id)
    {
        $row = $this->getDB()->query('SELECT * FROM ' . $this->getTable() . ' WHERE id = ?', array($id))->fetchRow();

        return $row;
    }

    /**
     * Возвращает домен БД установленный в конфиге
     *
     * @return string
     */
    abstract public function getDbDomain();

    /**
     * Возвращает название таблицы
     *
     * @return mixed
     */
    abstract public function getTable();
}
