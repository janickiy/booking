<?php

namespace ReservationKit\src\Bundle\AviaBundle\Processor;

use ReservationKit\src\Modules\Galileo\Model\RequisiteChecker;
use ReservationKit\src\RK;
use ReservationKit\src\Component\Process\Job;
use ReservationKit\src\Component\Process\MultiProcess;
use ReservationKit\src\Modules\Core\DB\Repository\AviaSearchRepository;
use ReservationKit\src\Modules\Core\DB\Repository\AviaSearchSettingsRepository;
use ReservationKit\src\Modules\Core\DB\Repository\JobsRepository;
use ReservationKit\src\Modules\Avia\Model\ServiceFactory;

use ReservationKit\src\Modules\Galileo\Model\RequisiteRules as GalileoRequisiteRules;
use ReservationKit\src\Modules\Galileo\Model\Requisites as GalileoRequisites;

/**
 * TODO объеденить все возможное в этом классе с поисковым классом
 */

/**
 * Менеджер выполнения поиска
 */
class AvailabilityProcess extends MultiProcess
{
    /**
     * Инициализирует и запускает новый поиск
     *
     * Добавляет список задач в БД и запускает их параллельное выполнение
     *
     * @param \RK_Avia_Entity_Search_Request $request
     * @param array $packageRequisites
     * @return bool
     */
    public function runMultiAvailability(\RK_Avia_Entity_Search_Request $request, $packageRequisites)
    {
        // Идентификатор группы  параллельных задач
        $searchGroup = createBase64UUID();

        // Создание поисковых задач для параллельного запуска
        foreach ($packageRequisites as $requisite) {
            /** @var GalileoRequisiteRules $requisiteRule */
            $requisiteRule = $requisite['requisite_rule'];

            if (RequisiteChecker::isApplyRule($requisiteRule, $request)) {
                /** @var GalileoRequisiteRules $requisiteRules */
                $requisiteRules = $requisite['requisite_rule'];
                // TODO надо убрать это условие
                if ($requisiteRules->isSearchPCC('36WB')) {
                    foreach ($request->getTriPartyAgreements() as $agreement) {
                        // TODO вынести это условие в RequisiteChecker
                        if (!empty($request->getCarriers()) && !in_array($agreement->getCarrier(), $request->getCarriers())) {
                            continue;
                        }

                        $job = new Job();
                        $job->setWorkerUrl($this->createWorkerUrl('avia.availability'));
                        $job->setStarted(\RK_Core_Date::now());
                        $job->setGroupId($searchGroup);

                        $job->addOption('search_id', $request->getId());
                        $job->addOption('requisites_id', $requisite['id']);
                        $job->addOption('carriers', array($agreement->getCarrier()));

                        JobsRepository::getInstance()->saveJob($job);

                        $this->addJob($job);
                    }

                } else {
                    $job = new Job();
                    $job->setWorkerUrl($this->createWorkerUrl('avia.availability'));
                    $job->setStarted(\RK_Core_Date::now());
                    $job->setGroupId($searchGroup);

                    $job->addOption('search_id', $request->getId());
                    $job->addOption('requisites_id', $requisite['id']);

                    JobsRepository::getInstance()->saveJob($job);

                    $this->addJob($job);
                }
            }
        }

        // Параллельный запуск поисковых задач
        $this->execute();
    }

    /**
     * Обработка результатов поиска
     */
    public function runPostProcessing()
    {
        $jobIds = array();
        foreach ($this->getJobs() as $job) {
            $jobIds[] = $job->getId();
        }

        // Результаты выполнения задач
        $jobs = JobsRepository::getInstance()->getJobAllById($jobIds);

        // Сбор и объединение результатов поиска в один
        $multiSearchResults = array();
        foreach ($jobs as $job) {
            if (is_array($job->getResult())) {
                foreach ($job->getResult() as $PCC => $availResults) {

                    if (isset($multiSearchResults[$PCC]) && is_array($multiSearchResults[$PCC])) {
                        foreach ($availResults as $wayNum => $PCCResults) {
                            $multiSearchResults[$PCC][$wayNum] = array_merge($multiSearchResults[$PCC][$wayNum], $PCCResults);
                        }

                    } else {
                        $multiSearchResults[$PCC] = $availResults;
                    }

                }
            }
/*
            $searchLogs = $job->getSearchLogs();

            // В расписании запрос состоит из последовательных запросов
            if (isset($searchLogs['isAvail'])) {
                foreach ($searchLogs as $key => $searchLogsItem) {
                    if ($key === 'isAvail') {
                        continue;
                    }

                    $this->addLogs($searchLogsItem['request_id'], $searchLogsItem['request_hash'], $searchLogsItem['time']);
                }
            } else {
                $this->addLogs($searchLogs['request_id'], $searchLogs['request_hash'], $searchLogs['time']);
            }*/
        }

        $this->setResult($multiSearchResults);

        return $multiSearchResults;
    }

    public function addLogs($id, $requestHash, $time)
    {
        // TODO ну тут без пояснений
        global $SCRIPT;

        //$time = (int) number_format(microtime(true), 12, '.', '');

        $SCRIPT['added_requests'][] = $id;
        $SCRIPT['used_requests'][] = $time . '_' . $requestHash;
    }
}