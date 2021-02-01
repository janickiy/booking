<?php

namespace ReservationKit\src\Modules\Core\Model\Config\Loader;

class ArrayLoader
{
    /**
     * Выполняет чтение конфигурации и возвращает в виде массива настроек
     * 
     * @param $config
     * @return array
     * @throws \Exception
     */
    public function readConfig($config)
    {
        $conf = array();

        // Параметры приложения
        if (isset($config['app']) && is_array($config['app']) && count($config['app'])) {
            foreach ($config['app'] as $key => $value) {
                $conf['app.' . $key] = $value;
            }
        }

        // Параметры БД
        if (isset($config['db']) && is_array($config['db']) && count($config['db'])) {
            $conf['db.list'] = $this->readDataBases($config['db']);
        }
        
        return $conf;
    }

    /**
     * Читает, проверяет корректность данных и возвращает список баз данных
     *
     * @param $dbConfigs
     * @return array
     * @throws \RK_Core_Exception
     */
    private function readDataBases($dbConfigs)
    {
        $databases = array();
        foreach ($dbConfigs as $dbKey => $database) {
            $dbData = array(
                'type'     => (string) @$database['type'],
                'host'     => (string) @$database['host'],
                'port'     => (string) @$database['port'],
                'dbname'   => (string) @$database['dbname'],
                'username' => (string) @$database['username'],
                'password' => (string) @$database['password'],
            );

            if (!$dbKey || !$dbData['type'] || !$dbData['host'] || !$dbData['username']/* || !$dbData['password']*/) {
                throw new \RK_Core_Exception('Conf error: Invalid database format');
            }

            $databases[$dbKey] = $dbData;
        }

        return $databases;
    }
}