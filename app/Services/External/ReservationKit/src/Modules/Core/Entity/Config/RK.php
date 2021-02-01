<?php

/**
 * Класс содержащий настройки приложения Reservation Kit
 */
class RK_Core_Entity_Config_RK
{
    /**
     * Список БД
     *
     * @var array
     */
    private $_dataBases = array();

    /**
     * Добавление данных для подключения к БД
     *
     * @param string $nameDB Название БД
     * @param string $userDB Пользователь БД
     * @param string $passwordDB Пароль БД
     * @param string $hostDB Хост
     */
    public function addDataBase($nameDB, $userDB, $passwordDB, $hostDB = '127.0.0.1')
    {
        $this->_dataBases[] = array(
            'name'     => $nameDB,
            'user'     => $userDB,
            'password' => $passwordDB,
            'host'     => $hostDB
        );
    }
}