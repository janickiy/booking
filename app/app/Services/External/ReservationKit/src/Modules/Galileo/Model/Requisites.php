<?php

namespace ReservationKit\src\Modules\Galileo\Model;

use ReservationKit\src\Modules\Avia\Model\Entity\Search\Settings;
use ReservationKit\src\Modules\Base\Model\Abstracts\AbstractRequisites;
use ReservationKit\src\Modules\Galileo\Model\RequisiteRules;

/**
 * test WABs:
 * uAPI4094670315-04e34b7f 	mS?5-d2Xe4 	1G	39NE	P7059935    +
 * uAPI4094670315-04e34b7f 	mS?5-d2Xe4 	1G	33VU	P7059936    +
 * uAPI4094670315-04e34b7f 	mS?5-d2Xe4 	1G	36WB	P7059937    -
 * uAPI4094670315-04e34b7f 	mS?5-d2Xe4 	1G	32Q6	P7059939    +
 * uAPI4094670315-04e34b7f 	mS?5-d2Xe4 	1G	6UQ2	P7059931    -    немцы
 *
 * prod WABs:
 * uAPI4316867519-de36d93f  2q%Ti_A7Kt  1G  39NE    P2889631
 * uAPI4316867519-de36d93f  2q%Ti_A7Kt  1G  33VU    P2889628
 * uAPI4316867519-de36d93f  2q%Ti_A7Kt  1G  36WB    P2889630
 * uAPI4316867519-de36d93f  2q%Ti_A7Kt  1G  35SR    P2889629
 * uAPI4316867519-de36d93f  2q%Ti_A7Kt  1G  32Q6    P2889627
 * uAPI4316867519-de36d93f  2q%Ti_A7Kt  1G  6UQ2    P2889626
 * uAPI4316867519-de36d93f  2q%Ti_A7Kt  1G  80UE    P3350099
 * uAPI4316867519-de36d93f  2q%Ti_A7Kt  1G  64WW    P3391319
 *
 * uAPI1338361128-8fc71187  y=7F+Wo94s  1G  L8W     P3204814
 */
class Requisites extends AbstractRequisites
{
    const WSDL_AIR_PATH = '/wsdl/galileo/uAPI_WSDLschema_Release-V16.2.0.59/Release-V16.2.0.59-V16.2/air_v37_0/Air.wsdl';
    const PROVIDER_CODE = '1G'; // Provider name

    // PT-BR (Portuguese Brazil), FR-CA (French Canadian), ZH-HANT (Chinese Traditional), ZH-HANS (Chinese Simplified)
    const LANGUAGE_CODE_RU = 'RURU';
    const LANGUAGE_CODE_GB = 'ENGB';
    const LANGUAGE_CODE_US = 'ENUS';

    const CURRENCY_RUB = 'RUB';
    const CURRENCY_EUR = 'EUR';
    const CURRENCY_USD = 'USD';

    private $_credentials = array(
        self::ENV_PROD => array(
            'default' => ['userId' => 'uAPI4316867519-de36d93f', 'password' => '2q%Ti_A7Kt'],
            'L8W'     => ['userId' => 'uAPI1338361128-8fc71187', 'password' => 'y=7F+Wo94s'],
        ),
        self::ENV_TEST => array(
            'default' => ['userId' => 'uAPI4094670315-04e34b7f', 'password' => 'mS?5-d2Xe4'],
        )
    );

    private $_uapiURL = array(
        self::ENV_PROD => 'https://emea.universal-api.travelport.com/B2BGateway/connect/uAPI',
        self::ENV_TEST => 'https://emea.universal-api.pp.travelport.com/B2BGateway/connect/uAPI',
        //self::ENV_PROFILER => 'https://twsprofiler.travelport.com/Service/Default.ashx'
    );

    private $_brancheCode = array(
        self::ENV_PROD => array(
            '39NE' => 'P2889631',
            '33VU' => 'P2889628',
            '36WB' => 'P2889630',
            '35SR' => 'P2889629',
            '32Q6' => 'P2889627',
            '6UQ2' => 'P2889626',
            'L8W'  => 'P3204814',
            '80UE' => 'P3350099',
            '64WW' => 'P3391319',
        ),
        self::ENV_TEST => array(
            '39NE' => 'P7059935',
            '33VU' => 'P7059936',
            '36WB' => 'P7059937',
            '32Q6' => 'P7059939',
            '6UQ2' => 'P7059931'
        )
    );

    /** @var Requisites Экземпляр объекта */
    private static $_instance;

    /** @var RequisiteRules */
    private $_rules;

    /**
     * Соответствие WAB поиска и выписки
     * Формат array(<WAB_поиска> => <WAB_выписки>[, ...])
     * TODO пересмотреть правила выписки
     *
     * @var array
     */
    private $_matchTicketWABs = array(
        '35SR'    => '35SR',
        '36WB'    => '36WB',
        '33VU'    => '36WB',
        '33VU:UT' => '39NE',
        '33VU:S7' => '35SR',
        '80UE'    => '36WB',
        '80UE:UT' => '39NE',
        '80UE:S7' => '35SR',
        //'6UQ2'    => '6UQ2',
        '39NE'    => '39NE',
        'L8W'     => '6UQ2',
        '64WW'    => '64WW',
    );

    /**
     * Соответствие WAB поиска валюте, которая за ним закреплена
     * TODO Сделать дефолтное значение, которое будет возвращаться, если нет соответсвия
     *
     * @var array
     */
    private $_matchCurrencyWABs = array(
        '36WB' => self::CURRENCY_RUB,
        '33VU' => self::CURRENCY_RUB,
        '80UE' => self::CURRENCY_RUB,
        '35SR' => self::CURRENCY_RUB,
        '39NE' => self::CURRENCY_RUB,
        '6UQ2' => self::CURRENCY_EUR,
        'L8W'  => self::CURRENCY_EUR,
        '64WW' => self::CURRENCY_RUB,
    );

    private function __construct() {  }  // Защищаем от создания через new Singleton
    private function __clone()     {  }  // Защищаем от создания через клонирование
    //private function __wakeup()    {  }  // Защищаем от создания через unserialize

    /**
     * Возвращает единственный экземпляр класса
     *
     * @param RequisiteRules|null $rules
     * @return Requisites
     */
    public static function getInstance(RequisiteRules $rules = null)
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
            self::$_instance->setEnvironment(self::ENV_PROD);
        }

        if ($rules instanceof RequisiteRules) {
            self::$_instance->setRules($rules);
        }

        return self::$_instance;
    }

    /**
     * Возвращает идентификатор пользователя
     *
     * @return string
     */
    public function getUserId()
    {
        // Ищется доступ для поискового PCC и, если найден, возвращается соответствующий 'userId'
        foreach ($this->_credentials[$this->getEnvironment()] as $PCC => $credential) {
            if ($PCC === $this->getRules()->getSearchPCC()) {
                return $credential['userId'];
            }
        }

        // Ищется 'default' доступ для поискового PCC и, если найден, возвращается соответствующий 'userId'
        foreach ($this->_credentials[$this->getEnvironment()] as $PCC => $credential) {
            if ($PCC === 'default') {
                return $credential['userId'];
            }
        }

        return false;
    }

    /**
     * Возвращает пароль окружения
     *
     * @return string
     */
    public function getPassword()
    {
        // Ищется доступ для поискового PCC и, если найден, возвращается соответствующий 'userId'
        foreach ($this->_credentials[$this->getEnvironment()] as $PCC => $credential) {
            if ($PCC === $this->getRules()->getSearchPCC()) {
                return $credential['password'];
            }
        }

        // Ищется 'default' доступ для поискового PCC и, если найден, возвращается соответствующий 'userId'
        foreach ($this->_credentials[$this->getEnvironment()] as $PCC => $credential) {
            if ($PCC === 'default') {
                return $credential['password'];
            }
        }

        return false;
    }

    /**
     * Возвращает url отправки запросов
     *
     * @return string
     */
    public function getRequestURI()
    {
        return $this->_uapiURL[$this->getEnvironment()];
    }

    /**
     * Возвращает Branch Code для указанного PCC
     *
     * @param $PCC
     * @return string
     * @throws \RK_Core_Exception
     */
    public function getBranchCode($PCC)
    {
        if (isset( $this->_brancheCode[$this->getEnvironment()][$PCC] )) {
            return $this->_brancheCode[$this->getEnvironment()][$PCC];
        }
        
        throw new \RK_Core_Exception('Branch code for PCC "' . $PCC . '" not exist');
    }

    public function getCurrencyWAB()
    {
        if (isset( $this->_matchCurrencyWABs[$this->getRules()->getSearchPCC()] )) {
            return $this->_matchCurrencyWABs[$this->getRules()->getSearchPCC()];
        }

        throw new \RK_Core_Exception('Currency code for PCC "' . $this->getRules()->getSearchPCC() . '" not exist');
    }

    /**
     * Устанавливает правила поиска и выписки для системы броинрования
     *
     * @return RequisiteRules
     */
    public function getRules()
    {
        return $this->_rules;
    }

    /**
     * Устанавливает правила поиска и выписки для системы броинрования
     *
     * @param RequisiteRules $rules
     */
    public function setRules(RequisiteRules $rules)
    {
        $this->_rules = $rules;
    }

    /**
     * Возвращает название системы бронирования
     *
     * @return string
     */
    public function getSystemName()
    {
        return 'galileo';
    }

    // TODO переделать. Сделать уникальный идентификатор для правил
    public function getTicketPCC()
    {
        if ($this->getRules()) {
            $searchPCC = $this->getRules()->getSearchPCC();
            $validationCompany = $this->getRules()->getValidationCompany();

            if (!empty($validationCompany)) {
                $validationCompany = ':' . $validationCompany;
            }

            if (isset($this->_matchTicketWABs[$searchPCC . $validationCompany])) {
                return $this->_matchTicketWABs[$searchPCC . $validationCompany];

            }

            if (isset($this->_matchTicketWABs[$searchPCC])) {
                return $this->_matchTicketWABs[$searchPCC];
            }

            throw new \Exception('Not found TicketPCC by "' . $searchPCC . '" in matchTicketWABs');
        }

        return null;
    }
}