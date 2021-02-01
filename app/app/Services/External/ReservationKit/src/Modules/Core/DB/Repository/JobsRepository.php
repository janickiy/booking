<?php

namespace ReservationKit\src\Modules\Core\DB\Repository;

use ReservationKit\src\Component\Process\Job;
use ReservationKit\src\Modules\Galileo\Model\GalileoException;

class JobsRepository extends AbstractRepository
{
    /**
     * @var JobsRepository
     */
    private static $_instance;

    /**
     * @return JobsRepository
     */
    public static function getInstance()
    {
        return self::$_instance ? self::$_instance : (self::$_instance = new JobsRepository());
    }

    /**
     * Возвращает задачу по id
     *
     * @param int|array $jobId
     * @return null|Job|Job[]
     */
    public function getJobOneById($jobId)
    {
        $job = $this->getDB()->query('select * from ' . $this->getTable() . ' where id = ?', $jobId)->fetchRow();

        if (isset($job)) {
            return $this->unserializeJob($job);
        }

        return null;
    }

    /**
     * Возвращает список задач по массиву из id
     *
     * @param int|array $jobIds
     * @return null|Job|Job[]
     */
    public function getJobAllById($jobIds)
    {
        $placeholders = '';

        if (is_array($jobIds)) {
            $placeholders = str_repeat(',?', (count($jobIds) - 1));
        }

        $jobs = $this->getDB()->query('select * from ' . $this->getTable() . ' where id in (?' . $placeholders . ')', $jobIds)->fetchArray();

        $serializedJobs = array();
        foreach ($jobs as $job) {
            $serializedJobs[] = $this->unserializeJob($job);
        }

        if (count($serializedJobs)) {
            return $serializedJobs;
        }

        return null;
    }

    /**
     * Добавляет задачу в БД
     *
     * @param Job $job
     * @return Job
     */
    public function saveJob(Job $job)
    {
        $serializedJob = $this->serializeJob($job);

        unset($serializedJob['id']);

        $fieldList = array_keys($serializedJob);

        $jobId = $this->getDB()
                      ->insert($this->getTable(), $fieldList, $serializedJob)
                      ->fetchRow();

        if ($jobId['id']) {
            $job->setId($jobId['id']);
        }

        return $job;
    }

    public function updateJob(Job $job)
    {
        $serializedJob = $this->serializeJob($job);

        // TODO сделать по аналогии с методом insert
        $this->getDB()->query('update rk_jobs set status = ?, finished = ?, result = ? where id = ?',
            array(
                $serializedJob['status'],
                $serializedJob['finished'],
                $serializedJob['result'],

                $serializedJob['id'],
            )
        );
    }

    /**
     * Возвращает готовый к сохранению в БД массив данных о задаче
     * 
     * @param Job $job
     * @return array
     */
    private function serializeJob(Job $job)
    {
        return array(
            'id'       => $job->getId(),
            'group_id' => $job->getGroupId(),
            'status'   => $job->getStatus(),
            'worker'   => $job->getWorkerUrl(),
            'started'  => (string) $job->getStarted()->formatTo(\RK_Core_Date::DATE_FORMAT_DB),
            'finished' => $job->getFinished() ? (string) $job->getFinished()->formatTo(\RK_Core_Date::DATE_FORMAT_DB) : null,
            'options'  => json_encode($job->getOptions()),
            'result'   => $job->getResult() ? base64_encode(gzcompress(serialize($job->getResult()), 9)) : null
        );
    }

    /**
     * Разбирает массив данных из БД в обьект задачи
     * 
     * @param array $jobData
     * @return Job
     */
    private function unserializeJob($jobData)
    {
        $job = new Job();
        $job->setId($jobData['id']);
        $job->setGroupId($jobData['group_id']);
        $job->setStatus($jobData['status']);
        $job->setWorkerUrl($jobData['worker']);
        $job->setOptions(json_decode($jobData['options'], true));
        $job->setStarted(new \RK_Core_Date($jobData['started'], \RK_Core_Date::DATE_FORMAT_DB));
        $job->setFinished(new \RK_Core_Date($jobData['finished'], \RK_Core_Date::DATE_FORMAT_DB));
        $job->setResult($jobData['result'] ? unserialize(gzuncompress(base64_decode($jobData['result']))) : null);
        
        return $job;
    }

    public function getDbDomain()
    {
        return 'main';
    }

    public function getTable()
    {
        return 'rk_jobs';
    }
}