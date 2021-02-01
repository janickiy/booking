<?php

namespace ReservationKit\src\Component\Process;

use ReservationKit\src\Component\HttpFoundation\URL\URL;

class Job
{
    const JOB_STATUS_NEW     = 'new';
    const JOB_STATUS_DONE    = 'done';
    const JOB_STATUS_ERROR   = 'error';
    const JOB_STATUS_TIMEOUT = 'timeout';

    /**
     * Номер задачи
     *
     * @var int
     */
    private $_id;

    /**
     * Идентификатор группы задач
     * 
     * @var
     */
    private $_groupId;
    
    /**
     * Статус задачи
     *
     * @var string
     */
    private $_status = self::JOB_STATUS_NEW;

    /**
     * Ссылка на исполнителя
     *
     * @var string
     */
    private $_workerUrl;

    /**
     * Опции задачи
     *
     * @var array
     */
    private $_options = array();

    /**
     * Дата начала здачи
     *
     * @var \RK_Core_Date
     */
    private $_started;

    /**
     * Дата выполнеия задачи
     *
     * @var \RK_Core_Date
     */
    private $_finished;

    /**
     * Результат выполнения задачи
     * 
     * @var string
     */
    private $_result;

    /**
     * Возвращает id задачи
     *
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Устанавливает id задачи
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * Возвращает идентификатор группы задача
     * 
     * @return mixed
     */
    public function getGroupId()
    {
        return $this->_groupId;
    }

    /**
     * Устанавливает идентификатор группы задач
     * 
     * @param mixed $groupId
     */
    public function setGroupId($groupId)
    {
        $this->_groupId = $groupId;
    }
    
    /**
     * Возвращает статус задачи
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Устанавливает статус задачи
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->_status = $status;
    }

    /**
     * Возвращает ссылку на исполнителя задачи
     *
     * @return string
     */
    public function getWorkerUrl()
    {
        return $this->_workerUrl;
    }

    /**
     * Устанавливает ссылку на исполнителя задачи
     *
     * @param string $url
     */
    public function setWorkerUrl($url)
    {
        $this->_workerUrl = $url;
    }

    /**
     * Возвращает опции задачи
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Устанавливает опции задачи
     *
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->_options = $options;
    }

    /**
     * Возвращает опцию задачи по названию
     *
     * @return mixed
     */
    public function getOption($option)
    {
        return isset($this->_options[$option]) ? $this->_options[$option] : null;
    }

    /**
     * Добавляет опцию задачи
     *
     * @param string $option
     * @param string $value
     */
    public function addOption($option, $value)
    {
        $this->_options[$option] = $value;
    }

    /**
     * Возвращает дату начала задачи
     *
     * @return \RK_Core_Date
     */
    public function getStarted()
    {
        return $this->_started;
    }

    /**
     * Устанавливает дату начала задачи
     *
     * @param \RK_Core_Date $started
     */
    public function setStarted(\RK_Core_Date $started)
    {
        $this->_started = $started;
    }

    /**
     * Возвращает дату выполнения задачи
     *
     * @return \RK_Core_Date
     */
    public function getFinished()
    {
        return $this->_finished;
    }

    /**
     * Устанавливает дату выполнения задачи
     *
     * @param \RK_Core_Date $finished
     */
    public function setFinished(\RK_Core_Date $finished)
    {
        $this->_finished = $finished;
    }

    /**
     * Возвращает результат выполнения задачи
     * 
     * @return mixed
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * Устанавливает результат выполнения задачи
     * 
     * @param string $result
     */
    public function setResult($result)
    {
        $this->_result = $result;
    }

    /**
     * Создает ссылку на запуск исполнителя
     *
     * @return string
     */
    public function getWorkerCurlUrl()
    {
        $url = new URL();
        $url->parseURL($this->getWorkerUrl());
        $url->addParam('jid', $this->getId());

        $jobParams = $this->getOptions();
        if (isset($jobParams)) {
            $url->addParams($jobParams);
        }

        return $url->getURL();
    }
}