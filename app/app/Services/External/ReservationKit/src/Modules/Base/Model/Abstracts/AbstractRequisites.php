<?php

namespace ReservationKit\src\Modules\Base\Model\Abstracts;

use ReservationKit\src\Modules\Avia\Model\Interfaces\IRequisites;

// TODO подумать надо классом ревизитов
// имеется идентификатор id класс выглядит как сущность DB, но таковой не является
// так же DB возвращает массив и работа происходит с ним. Необходимо переделать массив на объект
// см. пример в файле-вокере search.php
abstract class AbstractRequisites implements IRequisites
{
    const ENV_PROD = 'prod';
    const ENV_TEST = 'test';
    const ENV_PROFILER = 'profiler';

    /** @var int */
    private $_id;

    /** @var string Среда работы системы броинрования */
    private $_environment;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->_id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->_id = $id;
    }

    /**
     * Возвращает окружение
     *
     * @return string
     */
    protected function getEnvironment()
    {
        return $this->_environment;
    }

    /**
     * Устанавливает окружение
     *
     * @param string $environment enum(ENV_PROD|ENV_TEST|ENV_PROFILER)
     */
    public function setEnvironment($environment)
    {
        $this->_environment = $environment;
    }

    /**
     * Проверяет, что установлено prod окружение
     *
     * @return bool
     */
    protected function isProd()
    {
        return $this->getEnvironment() === self::ENV_PROD;
    }
}