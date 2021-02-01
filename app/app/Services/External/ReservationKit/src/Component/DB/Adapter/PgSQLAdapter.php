<?php

namespace ReservationKit\src\Component\DB\Adapter;

use ReservationKit\src\Component\DB\Exception;

/**
 * Реализация адаптера подключения к БД SQL Server с помощью php расширения sqlsrv
 */
class PgSQLAdapter extends AbstractAdapter
{
    protected $_result;

    public function __construct($config)
    {
        $this->init($config);
    }

    protected function connect($config)
    {
        if (!isset($config['host'], $config['port'], $config['dbname'], $config['username'], $config['password'])) {
            throw new Exception('PgSQLAdapter: invalid DB configuration');
        }

        $connection = [
            'host='     . $config['host'],
            'port='     . $config['port'],
            'dbname='   . $config['dbname'],
            'user='     . $config['username'],
            'password=' . $config['password']
        ];

        $this->_connection = pg_connect(implode(' ', $connection));

        if (!$this->_connection) {
            throw new Exception('PgSQLAdapter: сan\'t connect to PgSQL Server. Message: ' . pg_last_error($this->_connection));
        }

        /*
        $this->query('SET character_set_client=?', 'UTF8');
        $this->query('SET character_set_results=?', 'UTF8');
        $this->query('SET collation_connection=?', 'UTF8');
        $this->query('SET NAMES ?', 'UTF8');
        */

        return $this->_connection;
    }

    public function insert($tableName, $fields, $values)
    {
        $fieldsList       = implode(',', $fields);
        $placeholdersList = trim( str_repeat('?,', count($fields)) , ',' );

        $sql = 'INSERT INTO ' . $tableName . ' (' . $fieldsList . ') values (' . $placeholdersList . ') RETURNING id';

        $this->query($sql, $values);

        return $this;
    }

    protected function _query($sql)
    {
        // TODO возможно _result стоит перенести в родительский класс
        $this->_result = pg_query($this->_connection, $sql);

        if (!is_resource($this->_result)) {
            throw new Exception('PgSQLAdapter: error in query "' . $sql . "\". Message: " . pg_result_error($this->_result));
        }
    }

    public function error()
    {
        return pg_last_error();
    }

    protected function cleanParam($param)
    {
        return pg_escape_string($this->_connection, $param);
    }

    public function getAffectedRows()
    {
        return pg_affected_rows($this->_result);
    }

    /**
     * Возвращает Id последней добавленной в БД записи
     *
     * @return string
     */
    public function getLastInsertId()
    {
        return $this->_result ? $this->_result->fetchRow()['id'] : false;
    }

    public function fetchRow()
    {
        return pg_fetch_array($this->_result, null, PGSQL_ASSOC);
    }

    public function fetchArray()
    {
        $result = array();
        while ($row = $this->fetchRow()) {
            $result[] = $row;
        }

        $this->freeResult();

        return $result;
    }

    public function fetchColumn($column = false)
    {
        $row = $this->fetchRow();

        return $row ? ($column ? $row[$column] : reset($row)) : null;
    }

    /**
     * Очистка результата запроса и освобождение памяти
     *
     * @return bool|null
     */
    public function freeResult()
    {
        if (is_resource($this->_result)) {
            return pg_free_result($this->_result);
        }

        return null;
    }
}
