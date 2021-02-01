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
 * Менеджер выполнения поиска
 */
class SearchProcess extends MultiProcess
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
    public function runMultiSearch(\RK_Avia_Entity_Search_Request $request, $packageRequisites)
    {
        // Идентификатор группы  параллельных задач
        $searchGroup = createBase64UUID();

        // Создание поисковых задач для параллельного запуска
        $workerUrl = $this->createWorkerUrl('avia.search');

        foreach ($packageRequisites as $requisite) {
            /** @var GalileoRequisiteRules $requisiteRule */
            $requisiteRule = $requisite['requisite_rule'];

            if (isset($requisite['requisite_rule']) && RequisiteChecker::isApplyRule($requisiteRule, $request)) {
                // TODO надо убрать это условие
                if ($requisiteRule->isSearchPCC('36WB')) {
                    if (is_array($request->getTriPartyAgreements())) {
                        // Создание отдельного поискового запроса для каждого 3D договора
                        foreach ($request->getTriPartyAgreements() as $agreement) {
                            // TODO вынести это условие в RequisiteChecker
                            if (!empty($request->getCarriers()) && !in_array($agreement->getCarrier(), $request->getCarriers())) {
                                continue;
                            }

                            $job = new Job();
                            $job->setWorkerUrl($workerUrl);
                            $job->setStarted(\RK_Core_Date::now());
                            $job->setGroupId($searchGroup);

                            $job->addOption('search_id', $request->getId());
                            $job->addOption('requisites_id', $requisite['id']);
                            $job->addOption('carriers', array($agreement->getCarrier()));

                            JobsRepository::getInstance()->saveJob($job);

                            $this->addJob($job);
                        }
                    }

                } else {
                    $job = new Job();
                    $job->setWorkerUrl($workerUrl);
                    $job->setStarted(\RK_Core_Date::now());
                    $job->setGroupId($searchGroup);

                    $job->addOption('search_id', $request->getId());
                    $job->addOption('requisites_id', $requisite['id']);

                    JobsRepository::getInstance()->saveJob($job);

                    $this->addJob($job);
                }
            }

            // Для реквизитов без правил TODO
            if (empty($requisiteRule)) {
                $job = new Job();
                $job->setWorkerUrl($workerUrl);
                $job->setStarted(\RK_Core_Date::now());
                $job->setGroupId($searchGroup);

                $job->addOption('search_id', $request->getId());
                $job->addOption('requisites_id', $requisite['id']);

                JobsRepository::getInstance()->saveJob($job);

                $this->addJob($job);
            }
        }

        // Параллельный запуск поисковых задач
        $this->execute();
    }

    /**
     * Обработка запроса поиска
     */
    public function runPreProcessing()
    {

    }

    /**
     * Обработка результатов поиска
     */
    public function runPostProcessing()
    {
        $jobIds = array();
		$jobs = $this->getJobs();

		if (!$jobs) {
		    return array();
        }

        // Список id задач, которые были запущены параллельно
        foreach ($jobs as $job) {
		    $jobIds[] = $job->getId();
        }

        // Получение результатов выполнения задач из БД
        $jobs = JobsRepository::getInstance()->getJobAllById($jobIds);

        // Сбор и объединение результатов поиска в один
        $multiSearchResults = array();
        foreach ($jobs as $job) {
            if (is_array($job->getResult())) {
                $multiSearchResults = array_merge($multiSearchResults, $job->getResult());
            }
        }

        $this->setResult($multiSearchResults);

        return $multiSearchResults;
    }
}