<?php

namespace ReservationKit\src\Component\DB\Adapter;

use ReservationKit\src\Component\DB\Exception;
use ReservationKit\src\Component\DB\Profiler;

/**
 * Абстрактный адаптер подключения к БД
 */
abstract class AbstractAdapter
{
    /**
     * Ресурс подключения к БД
     *
     * @var resource
     */
    protected $_connection;
    
    /**
     * Профайлер соединения
     * 
     * @var Profiler
     */
    protected $_profiler;
    
    /**
     * Создает подключение к БД
     * 
     * @param array $config кофигурация подключения
     */
    protected function init($config)
    {
        $this->_profiler = new Profiler();
        $this->_connection = $this->connect($config);
    }

    /**
     * Возвращает профайлер адаптера
     * 
     * @return Profiler
     */
    public function getProfiler()
    {
        return $this->_profiler;
    }

    /**
     * Выполненяет запрос к БД с фиксацией времени выполнения в профайлере и фильтрацией запроса
     *
     * @param $sql
     * @param array $params
     * @return $this
     * @throws Exception
     */
    public function query($sql, $params = array())
    {
        $time = microtime(true);
        $sql = $this->prepare($sql, $params);
        
		try {
			$this->_query($sql);
			$this->getProfiler()->addQuery($sql, microtime(true) - $time);

		} catch (Exception $e) {
			$this->getProfiler()->addQuery($sql, microtime(true) - $time);
		}
        
        return $this;
    }

    /**
     * Возвращает аргумент в зависимости от типа с кавычками или без + фильтрация
     *
     * @param $param
     * @return int|string
     * @throws Exception
     */
    public function bindParam($param)
    {
        if (is_object($param) && method_exists($param, '__toString')) {
            $param = (string) $param;
        }
        
        if (is_string($param) || is_numeric($param) || is_null($param)) {
            $param = $this->cleanParam($param);
            
            if (is_null($param)) {
                return 'NULL';
            }
            
            return (is_numeric($param) && !preg_match('/[a-zA-Z]/', $param)  && !preg_match('/^0[\d]+$/', $param)) ? $param : ('\'' . $param . '\'');
        }
        
        throw new Exception('Invalid DB parameters');
    }

    /**
     * Заменяет в запросе плейсхолдеры <?> соответствующими параметрами и возвращает его
     *
     * @param $sql
     * @param $params
     * @return mixed|string
     * @throws Exception
     */
    public function prepare($sql, $params)
    {
        if (!is_array($params)) {
            $params = array($params);
        }

        $phCounter = 1;
        $replacement = array();

        foreach ($params as $param) {
            if (is_null($param)) {
                $param = 'NULL';

            } else if (is_array($param)) {
                foreach ($param as $subKey => $subParam) {
                    $param[$subKey] = $this->bindParam($subParam);
                }
                $param = implode(',', $param);

            } else {
                $param = $this->bindParam($param);
            }

            $sql = preg_replace('/\?/', '##PH' . $phCounter . '##', $sql, 1);

            $replacement['##PH' . ($phCounter++) . '##'] = $param;
        }

        // Если количество произведенных замен не равно количеству переданных параметров
        /*
        if (($phCounter - 1) != count($replacement)) {
            throw new Exception('Invalid DB parameters count');
        }
        */

        return strtr($sql, $replacement);
    }

    /**
     * Вставка записи в БД
     *
     * @param string $tableName
     * @param array $fields
     * @param array $values
     * @return mixed
     */
    abstract public function insert($tableName, $fields, $values);

    /**
     * Выполняет подключение к БД
     *
     * @param $config
     * @return mixed
     */
    abstract protected function connect($config);

    /**
     * Выполняет запрос к БД
     *
     * @param $sql
     * @return mixed
     */
    abstract protected function _query($sql);

    /**
     * Возвращает безопасное значение аргумента
     *
     * @param $param
     * @return mixed
     */
    abstract protected function cleanParam($param);

    /**
     * Возвращает количество затронутых рядов
     */
    abstract public function getAffectedRows();

    /**
     * Возвращает последний инкремент счетчика
     */
    abstract public function getLastInsertId();

    /**
     * Возвращает результатирующий массив записей
     */
    abstract public function fetchArray();

    /**
     * Возвращает запись
     */
    abstract public function fetchRow();

    /**
     * Возвращает один столбец из результата
     *
     * @param bool $column
     * @return mixed
     */
    abstract public function fetchColumn($column = false);

    /**
     * Освобождает ресурсы результатов запроса
     */
    abstract public function freeResult();
}
