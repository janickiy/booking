<?php

namespace ReservationKit\src\Component\DB\Adapter;

use ReservationKit\src\Component\DB\Exception;

/**
 * Реализация адаптера подключения к БД с помощью php расширения mysql
 *
 * Внимание! Данное расширение устарело, начиная с версии PHP 5.5.0, и удалено в PHP 7.0.0
 * Необходимо использовать вместо него MySQLi или PDO_MySQL
 */
class MySQLiAdapter extends AbstractAdapter
{
    protected $_result;

    public function __construct($config)
    {
        $this->init($config);
    }

    protected function connect($config)
    {
        if (!isset($config['name'], $config['host'], $config['username'], $config['password'])) {
            throw new Exception('Invalid DB configuration');
        }

        $this->_connection = @mysqli_connect($config['host'], $config['username'], $config['password']);

        if (!$this->_connection) {
            throw new Exception('Can\'t connect to mysqli: ' . mysqli_error($this->_connection));
        }
        
        if (!@mysqli_select_db($this->_connection, $config['name'])) {
            throw new Exception('Can\'t select db: ' . mysqli_error($this->_connection));
        }

        $this->query('SET character_set_client=?', 'UTF8');
        $this->query('SET character_set_results=?', 'UTF8');
        //$this->query('SET collation_connection=?', 'UTF8');
        $this->query('SET NAMES ?', 'UTF8');

        return $this->_connection;
    }

    public function insert($tableName, $fields, $values)
    {
        // TODO: Implement insert() method.
    }

    protected function _query($sql)
    {
        $this->_result = mysqli_query($this->_connection, $sql);
		
        if (mysqli_errno($this->_connection)) {
            throw new Exception(mysqli_error($this->_connection));
        }
    }

    protected function cleanParam($param)
    {
        return mysqli_real_escape_string($this->_connection, $param);
    }

    public function getAffectedRows()
    {
        return mysqli_affected_rows($this->_connection);
    }

    public function getLastInsertId()
    {
        return mysqli_insert_id($this->_connection);
    }

    public function fetchRow()
    {
        return mysqli_fetch_assoc($this->_result);
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

    public function freeResult()
    {
        if (is_resource($this->_result)) {
            return mysqli_free_result($this->_result);
        }

        return null;
    }
}
