<?php

namespace ReservationKit\src\Bundle\AviaBundle\Service;

use ReservationKit\src\Bundle\AviaBundle\Processor\SearchProcess;
use ReservationKit\src\Bundle\AviaBundle\Processor\AvailabilityProcess;
use ReservationKit\src\Component\Process\Job;
use ReservationKit\src\Modules\Avia\Model\Interfaces\IRequisites;
use ReservationKit\src\Modules\Core\DB\Repository\AviaSearchRepository;
use ReservationKit\src\Modules\Core\DB\Repository\PackageRequisitesRepository;
use ReservationKit\src\Modules\Core\DB\Repository\RequisitesRepository;
use ReservationKit\src\Modules\Core\Model\Enum\ConditionEnum;
use ReservationKit\src\Modules\Galileo\Model\Helper\SearchResponseHelper;
use ReservationKit\src\Modules\Galileo\Model\Requisites;
use ReservationKit\src\Modules\Galileo\Model\Requisites as GalileoRequisites;
use ReservationKit\src\Modules\S7AgentAPI\Model\Factory as S7AgentFactory;    // TODO должен быть интерфейс или абстрактный класс
use ReservationKit\src\Modules\Galileo\Model\Factory as GalileoFactory;       // TODO тоже что и выше
use ReservationKit\src\RK;

/**
 * Kit
 */
class SearchService
{
    /** @var \RK_Avia_Entity_Search_Request */
    private $_request;

    /** @var IRequisites */
    private $_requisites;

    /** @var \RK_Avia_Entity_Booking[] */
    private $_results;

    /**
     * Возвращает экземпляр обьекта для работы поиска с БД
     *
     * @return AviaSearchRepository
     */
    protected static function getAviaRepository()
    {
        return AviaSearchRepository::getInstance();
    }

    /**
     * Извлекает сохраненный ранее запрос поиска
     *
     * @param int $searchId
     * @return \RK_Avia_Entity_Search_Request
     * @throws \Exception
     */
    public function readRequest($searchId)
    {
        $this->_request = self::getAviaRepository()->getRequest($searchId);

        if (!$this->getRequest()) {
            throw new \Exception('Search not found');
        }

        //$this->_results = self::getAviaRepository()->getResults($this->getRequest()->getId());
        //$this->_price($this->_results);

        return $this->getRequest();
    }

    /**
     * TODO 2 запроса к одной таблице. ХУИТА исправлять!
     *
     * Извлекает сохраненный ранее запрос поиска по хешу
     *
     * @param string $hash
     * @return \RK_Avia_Entity_Search_Request
     * @throws \Exception
     */
    public function readRequestByHash($hash)
    {
        $this->_request = self::getAviaRepository()->getRequestByHash($hash);
        $this->_results = self::getAviaRepository()->getSearchResultsByHash($hash);

        if (!$this->getRequest()) {
            throw new \Exception('Search not found');
        }

        return $this->getRequest();
    }

    public function getSearchOfferByHash($hash, $offerId)
    {
        if (!isset($this->_results)) {
            $this->readRequestByHash($hash);
        }

        if (isset($this->_results, $this->_results[$offerId])) {
            return $this->_results[$offerId];
        }

        return null;
    }

    public function search()
    {
        // Получение актуального пакета реквизитов
        $packageRequisites = RequisitesRepository::getInstance()->findByPackageId('1');

        $processor = new SearchProcess();
        // TODO сюда можно добавить preProcess с применением и проверкой правил, а также созданием новых поисков для 36WB
        $processor->runMultiSearch($this->getRequest(), $packageRequisites);
        $processor->runPostProcessing();

        //$processor->runNewPricing($this->getRequest());

        // TODO
        //$searchFilter = new SearchAviaResponseFilter($processor->getResult());

        $processor->setResult(SearchResponseHelper::excludeSimilarOffersByPCC($processor->getResult()));

        // Сохранение результата в БД
        AviaSearchRepository::getInstance()->saveSearchResult($this->getRequest(), $processor->getResult());

        $this->_results = self::getAviaRepository()->getSearchResults($this->getRequest()->getId());

        return $this->_results;
    }

    public function availability()
    {
        // Получение актуального пакета реквизитов
        $packageRequisites = RequisitesRepository::getInstance()->findByPackageId('1');

        $processor = new AvailabilityProcess();
        // TODO сюда можно добавить preProcess с применением и проверкой правил, а также созданием новых поисков для 36WB
        $processor->runMultiAvailability($this->getRequest(), $packageRequisites);
        $processor->runPostProcessing();

        //$processor->runNewPricing($this->getRequest());

        // Сохранение результата в БД
        AviaSearchRepository::getInstance()->saveSearchResult($this->getRequest(), $processor->getResult());

        $this->_results = self::getAviaRepository()->getSearchResults($this->getRequest()->getId());

        return $this->_results;
    }

    /**
     * Сохраняет запрос поиска
     *
     * @param \RK_Avia_Entity_Search_Request $request
     * @throws \RK_Core_Exception
     */
    public function saveRequest(\RK_Avia_Entity_Search_Request $request)
    {
        /*
        $request->setServices(RK_Flights_Factory::getInstance()->getSearchTypes());

        //$request->setServices(array('Galileo'));
        $allocator = new RK_Flights_Allocator();
        $allocator->allocateRequest($request);

        $packageFactory = new RK_Flights_RequisitesPackage_Factory;
        $packageFactory->find($request);
        $packageFactory->fillRequest($request);
        $requestPackages = array();
        $packages = $packageFactory->getPackages();
        $packageIds = array();

        foreach ($packages as $service => $spackages) {
            $requestPackages[$service] = array_keys($spackages);
            if (count($requestPackages[$service])) {
                $packageIds = array_merge($packageIds, $requestPackages[$service]);
            }
        }

        $request->setPackages($requestPackages);

        $request->init();
        $allPackages = RK_Main_Source_List_Packages::getInstance()->getPackages($packageIds);

        foreach ($allPackages as $package) {
            $factory = RK_Flights_Factory::getFactory($package);
            $service = $factory->getSearchService();

            if (method_exists($service, 'prepareSearchRequest')) {
                $service->prepareSearchRequest($request);
            }
        }
        */


        self::getAviaRepository()->saveRequest($request);
        $this->_request = $request;

        //return $this->_request = $request;
    }

    /**
     * @return IRequisites
     */
    public function getRequisites()
    {
        return $this->_requisites;
    }

    /**
     * @param IRequisites $requisites
     */
    public function setRequisites($requisites)
    {
        $this->_requisites = $requisites;
    }

    public function applyRequisites(IRequisites $requisites)
    {
        $this->setRequisites($requisites);  // TODO Возможно стоит вынести в конструктор для более понятной семантики

        $request = $this->getRequest();

        if ($request) {
            $request->setRequisiteId($requisites->getId());

            $rules = $requisites->getRules();

            // Остаются только перевозчики, разрешенные для поиска
            if ($rules->isExistValueForSearchRuleField('carriers')) {
                $carriers = $request->getCarriers();
                $ruleCarriers = $rules->getSearchRuleByField('carriers')['value'];

                if (!empty($carriers)) {
                    $filterByCarriers = array_intersect($carriers, $ruleCarriers);
                } else {
                    $filterByCarriers = $ruleCarriers;
                }

                $request->setCarriers($filterByCarriers);
            }

            // Исключаемые перевозчики
            $carriers = $request->getCarriers();
            if ($rules->isExistValueForSearchRuleField('excludeCarriers') && empty($carriers)) {
                $ruleExcludeCarriers = $rules->getSearchRuleByField('excludeCarriers')['value'];

                $request->setProhibitedCarriers($ruleExcludeCarriers);
            }

            // Исключение из поиска перевозчиков из 3Д договора. Для этих перевозчиков будет запущен отдельный поиск
            // Если в правилах реквизитов необходимо наличие 3D договора для поиска, то это отдельный поиск, перевозчик не исключается
            $triPartyAgreements = $request->getTriPartyAgreements();
            if (!empty($triPartyAgreements)) {
                $agreementCarriers = array();

                foreach ($triPartyAgreements as $agreement) {
                    $agreementCarriers[] = $agreement->getCarrier();
                }

                $carriers = array_diff($request->getCarriers(), $agreementCarriers);

                $request->setCarriers($carriers);
            }

            // Удаление 3Д договора из запроса в зависимости от правил
            if (!$rules->isExistSearchRuleField('need3DAgreement')) {
                $request->removeTriPartyAgreements();

            } else {
                if ($rules->isExistConditionForSearchRuleField('need3DAgreement')) {
                    if ($rules->getSearchRuleByField('need3DAgreement')['condition'] !== ConditionEnum::ONLY) {
                        $request->removeTriPartyAgreements();
                    }
                }
            }
        }
    }

    public function applyJobOptions(Job $job)
    {
        $request = $this->getRequest();

        $requisites = $this->getRequisites();

        if ($request && $requisites) {
            $rules = $requisites->getRules();

            // Метод applyJobOptions, запущенный после метода applyRequisites будет в объекте с уже примененными правилами и установленными реквизитами
            // Следовательно проверка наличия
            if ($rules->isExistSearchRuleField('need3DAgreement')) {
                if ($rules->isExistConditionForSearchRuleField('need3DAgreement')) {
                    if ($rules->getSearchRuleByField('need3DAgreement')['condition'] === ConditionEnum::ONLY) {
                        $carriers = $job->getOption('carriers');

                        $request->setCarriers($carriers);

                        $agreement = $request->getTriPartyAgreementByCarrierCode($carriers[0]);
                        if ($agreement) {
                            $request->setTriPartyAgreements(array($agreement));
                        }
                    }
                }
            }

            // TODO создать правило для реквизитов с этим условием
            // Если правило применяется для запроса с 3D догвором S7, то заменить PCC 36WB на 35SR, т.к. PCC 36WB c 3В договором S7 не возвращает результаты
            if ($requisites->getRules()->getSearchPCC() === '36WB' && $request->getTriPartyAgreementByCarrierCode('S7') && in_array('S7', $request->getCarriers())) {
                $requisites->getRules()->setSearchPCC('35SR');
            }
        }
    }

    // TODO вынести в общий класс
    /**
     * @return \RK_Avia_Entity_Search_Request
     */
    public function getRequest() { return $this->_request; }

    /**
     * @return \RK_Avia_Entity_Booking[]
     */
    public function getResults() { return $this->_results; }

    /**
     * TODO не для сервиса-поиска
     *
     * @param \RK_Avia_Entity_Search_Request $request
     * @return array
     * @throws \RK_Core_Exception
     */
    public static function pricing($request)
    {
        $priceSolutions = [];

        $requisite = RequisitesRepository::getInstance()->findById($request->getRequisiteId());

        /* @var GalileoFactory|S7AgentFactory $aviaModule */
        $aviaModule = RK::getContainer()->getModule($request->getSystem()); // TODO сделать инициализацию по реквизитам и затем заглянуть в вокер search.php и сделать установку реквизитов

        // Установка реквизитов в запрос
        if ($requisite) {
            $request->setRequisiteId($requisite['id']);

            $requisites = $aviaModule->getRequisites();
            $requisites->setId($requisite['id']);

            if (isset($requisite['requisite_rule'])) {
                // Установка правил для реквизитов

                $requisites->setRules($requisite['requisite_rule']);

                // Установка параметров из реквизитов
                //$searchKit->applyRequisites($requisites);
            }

        } else {
            // TODO запись в логи
            // Ошибка не найдены реквизиты
        }

        //$aviaModule->getRequisites()->init();
        $priceSolutions = $aviaModule->getAviaService()->price($request);

        return $priceSolutions;
    }

    /**
     * TODO не для сервиса-поиска
     *
     * @param \RK_Avia_Entity_Booking $request
     * @return bool
     * @throws \RK_Core_Exception
     */
    public static function booking($request)
    {
        $requisite = RequisitesRepository::getInstance()->findById($request->getRequisiteId());

        /* @var GalileoFactory|S7AgentFactory $aviaModule */
        $aviaModule = RK::getContainer()->getModule($request->getSystem());

        //$aviaModule->getRequisites()->init();
        return $aviaModule->getAviaService()->booking($request);
    }
}