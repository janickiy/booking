<?php

namespace ReservationKit\src\Component\Process;

use ReservationKit\src\Modules\Core\Model\Settings;
use ReservationKit\src\RK;
use ReservationKit\src\Component\Process\Job;

class MultiProcess
{
    /**
     * Максимальное время выполнения задачи
     *
     * @var int
     */
    private $_timeout = 30;

    /**
     * Пауза между потоками, мс
     *
     * @var int
     */
    private $_pauseTime = 1;

    /**
     * Максимальное количество одновременно выполняющихся потоков
     *
     * @var int
     */
    private $_maxConnections = 100;

    /**
     * Задачи для выполнения
     *
     * @var Job[]
     */
    private $_jobs;

    /**
     * Результат выполнения
     * 
     * @var
     */
    private $_result;
    
    /**
     * Выполняет запуск задачи
     */
    public function execute()
    {
        $jobs = $this->getJobs();

		if (!$jobs) return;

        $i = 0;
        $chs = array();
        foreach ($jobs as $job) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_URL, $job->getWorkerCurlUrl());
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_NOBODY, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->getTimeout());
            $chs[$i] = $ch;

            $i++;

            //curl_exec($ch);
            //curl_close($ch);
        }

        //$maxThreads = $this->getMaxConnections();
        $countThreads = count($chs);

        $mh = curl_multi_init();
        $stillRunning = null;
        $addedThreads = 0;

        $startT = microtime(true);
        $endtT = 0;
        do {
            // TODO добавить отслеживание времени выполнения цикла
            // TODO добавить контроль максимального количества одновременно выполняющихся задач
            // TODO упростить переменные $toAdd , $countThreads и $addedThreads

            /*if (($endtT - $startT) > 21) {

                echo '$chs: ';
                pr($chs);

                echo '$stillRunning: ';
                pr($stillRunning);

                echo '$toAdd: ';
                pr($toAdd);

                pr('Повис цикл');
                die();
            }*/


            if (!empty($chs)) {
                $toAdd = $countThreads - $addedThreads;

                if ($toAdd) {
                    foreach ($chs as $ch) {
                        curl_multi_add_handle($mh, $ch);
                    }

                    $addedThreads = $toAdd;
                }
            }

            // Запуск мнодественного поиска
            $mrc = curl_multi_exec($mh, $stillRunning);


            //usleep($this->getPauseTime());
            //usleep(1);

            //$endtT = microtime(true);

        } while ($mrc === CURLM_CALL_MULTI_PERFORM || ($stillRunning && $mrc === CURLM_OK));

        $endtT = microtime(true);
        //echo ($endtT - $startT) . ' сек; ';

        foreach ($chs as $ch) {
            $err     = curl_errno($ch); // e.g.: (int) 51
            $errmsg  = curl_error($ch); // e.g.: (string) "SSL: unable to obtain common name from peer certificate"

            // TODO логировать ошибки cURL
            if ($err > 0 || !empty($errmsg)) {
                pr('cURL error: (' . $err . ') ' . $errmsg);
            }

            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }

        curl_multi_close($mh);
    }

    /**
     * Создает ссылку на исполнителя процесса
     *
     * Если в опции app.workers_root не используется плейсхолдер "{WORKER}", то в ссылке будет указан
     * путь к файлу воркера. Если плейсхолдер "{WORKER}" указан, то он будет заменен на имя файла-исполнителя.
     * В таком варианте запустить воркер можно будет методом RK::executeWorker по этому имени.
     *
     * @param string $workerPath Путь к папке исполнителей
     * @return mixed|string
     * @throws \RK_Core_Exception
     */
    public function createWorkerUrl($workerPath)
    {
        $settings = Settings::getInstance('config');

        if ($workersHttpUrl = $settings->get('app.process_worker_url')) {
            if (strpos($workersHttpUrl, '{WORKER}')) {
                $workerPath = str_replace('.php', '', $workerPath);
                $workerPath = str_replace('/', '.', $workerPath);
                $workerPath = str_replace('\\', '.', $workerPath);
                //$workerPath = urlencode($workerPath);
                $workersHttpUrl = str_replace('{WORKER}', $workerPath, $workersHttpUrl);

                return str_replace('127.0.0.1', request()->getHttpHost(),$workersHttpUrl);
            }

            return str_replace('127.0.0.1', request()->getHttpHost(),($workersHttpUrl . '/' . $workerPath));
        }

        throw new \RK_Core_Exception('Invalid workers root');
    }

    /**
     * Возвращает текущую задачу
     *
     * @return Job[]
     */
    public function getJobs()
    {
        return $this->_jobs;
    }

    /**
     * Добавляет задачу
     *
     * @param Job $job
     * @return Job
     */
    public function addJob(Job $job)
    {
        $this->_jobs[] = $job;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->_timeout = $timeout;
    }

    /**
     * @return int
     */
    public function getPauseTime()
    {
        return $this->_pauseTime;
    }

    /**
     * @param int $pauseTime
     */
    public function setPauseTime($pauseTime)
    {
        $this->_pauseTime = $pauseTime;
    }

    /**
     * @return int
     */
    public function getMaxConnections()
    {
        return $this->_maxConnections;
    }

    /**
     * @param int $maxConnections
     */
    public function setMaxConnections($maxConnections)
    {
        $this->_maxConnections = $maxConnections;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->_result = $result;
    }
}