<?php

use ReservationKit\src\RK;
use ReservationKit\src\Bundle\AviaBundle\Service\SearchService;
use ReservationKit\src\Component\Process\Job;
use ReservationKit\src\Modules\Core\DB\Repository\JobsRepository;
use ReservationKit\src\Modules\Core\DB\Repository\RequisitesRepository;

if (isset($_GET['jid'], $_GET['wn']))
{
    if ($job = JobsRepository::getInstance()->getJobOneById((int) $_GET['jid'])) {
        $job->setStarted(\RK_Core_Date::now());

        /* @var SearchService $searchKit */
        $searchKit = RK::getContainer()->getBundle('Avia')->getSearchService();
        if ($searchKit->readRequest($job->getOption('search_id'))) {
            // Получение правил для реквизитов
            $requisite = RequisitesRepository::getInstance()->findById($job->getOption('requisites_id'));

            // Получение модуля бронирования
            $module = RK::getContainer()->getModule($requisite['system']);

            // Установка реквизитов в запрос
            if ($requisite) {
                $requisites = $module->getRequisites();
                $requisites->setId($requisite['id']);

                if (isset($requisite['requisite_rule'])) {
                    // Установка правил для реквизитов

                    $requisites->setRules($requisite['requisite_rule']);

                    // Установка параметров из реквизитов
                    $searchKit->applyRequisites($requisites);
                }

            } else {
                // TODO запись в логи
                // Ошибка не найдены реквизиты
            }

            // Установка дополнительных параметров
            $searchKit->applyJobOptions($job);

            // Поиск
            $searchService = $module->getSearchService();
            $searchService->search($searchKit->getRequest());

            // Фиксация результатов выполнения задачи
            $job->setStatus(Job::JOB_STATUS_DONE);
            $job->setFinished(\RK_Core_Date::now());
            $job->setResult($searchService->getResult());

            // Сохранение результатов выполнения задачи
            JobsRepository::getInstance()->updateJob($job);
        }
    }
}