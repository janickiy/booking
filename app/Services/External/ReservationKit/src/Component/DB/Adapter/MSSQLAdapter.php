<?php

namespace ReservationKit\src\Component\DB\Adapter;

use ReservationKit\src\Component\DB\Exception;

/**
 * Реализация адаптера подключения к БД SQL Server с помощью php расширения sqlsrv
 */
class MSSQLAdapter extends AbstractAdapter
{
    protected $_result;

    public function __construct($config)
    {
        $this->init($config);
    }

    protected function connect($config)
    {
        if (!isset($config['dbname'], $config['host'], $config['username'], $config['password'])) {
            throw new Exception('MSSQLAdapter: invalid DB configuration');
        }

        $this->_connection = sqlsrv_connect($config['host'], array(
            'Database' => $config['dbname'],
            'UID' => $config['username'],
            'PWD' => $config['password']
        ));

        if (!$this->_connection) {
            throw new Exception('MSSQLAdapter: сan\'t connect to SQL Server. Message: ' . $this->_sqlsrv_get_last_error());
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
        // TODO: Implement insert() method.
    }

    protected function _query($sql)
    {
        $this->_result = sqlsrv_query($this->_connection, $sql);

        if (!is_resource($this->_result)) {
            throw new Exception('MSSQLAdapter: error in query "' . $sql . "\". Message: " . $this->_sqlsrv_get_last_error());
        }
    }

    public function error()
	{
		return sqlsrv_errors();
	}	

    protected function cleanParam($param)
    {
        return $this->_sqlsrv_escape_string($param);
    }

    public function getAffectedRows()
    {
        return sqlsrv_rows_affected($this->_result);
    }

    public function getLastInsertId()
    {
        $this->query('SELECT @@identity AS id');

        return $this->fetchColumn('id');
    }

    public function fetchRow()
    {
        return sqlsrv_fetch_array($this->_result, SQLSRV_FETCH_ASSOC);
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
            return sqlsrv_free_stmt($this->_result);
        }

        return null;
    }

    /**
     * Удаление кавычек и пробельных символов из строки
     *
     * @param string $data
     * @return mixed|string
     */
    private function _sqlsrv_escape_string($data) {
        if (!isset($data) || empty($data)) {
            return '';
        }

        if (is_numeric($data)) {
            return $data;
        }

        $non_displayables = array(
            '/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
            '/%1[0-9a-f]/',             // url encoded 16-31
            '/[\x00-\x08]/',            // 00-08
            '/\x0b/',                   // 11
            '/\x0c/',                   // 12
            '/[\x0e-\x1f]/'             // 14-31
        );

        foreach ($non_displayables as $regex) {
            $data = preg_replace($regex, '', $data);
        }

        $data = str_replace("'", "''", $data);

        return $data;
    }

    /**
     * Возвращает данные о последней ошибке
     *
     * @return bool
     */
    private function _sqlsrv_get_last_error()
    {
        $errors = sqlsrv_errors();
        if ($errors !== null) {
            $errorMessage = '';
            foreach ($errors as $errorItem) {
                $errorMessage .= $errorItem['message'] . ' ';
            }

            return trim($errorMessage);
        }

        return false;
    }
}
