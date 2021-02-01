<?php

namespace ReservationKit\src\Component\DB\Adapter;

use ReservationKit\src\Component\DB\Exception;

/**
 * Реализация адаптера подключения к БД с помощью php расширения mysql
 *
 * Внимание! Данное расширение устарело, начиная с версии PHP 5.5.0, и удалено в PHP 7.0.0
 * Необходимо использовать вместо него MySQLi или PDO_MySQL
 */
class MySQLAdapter extends AbstractAdapter
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

        $this->_connection = @mysql_connect($config['host'], $config['username'], $config['password']);

        if (!$this->_connection) {
            throw new Exception('Can\'t connect to mysql: ' . mysql_error());
        }
        
        if (!@mysql_select_db($config['name'], $this->_connection)) {
            throw new Exception('Can\'t select db: ' . mysql_error());
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
        $this->_result = mysql_query($sql, $this->_connection);

        if (mysql_errno()) {
            throw new Exception(mysql_error($this->_connection));
        }
    }

    protected function cleanParam($param)
    {
        return mysql_real_escape_string($param);
    }

    public function getAffectedRows()
    {
        return mysql_affected_rows($this->_connection);
    }

    public function getLastInsertId()
    {
        return mysql_insert_id($this->_connection);
    }

    public function fetchRow()
    {
        return mysql_fetch_assoc($this->_result);
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
            return mysql_free_result($this->_result);
        }

        return null;
    }
}
