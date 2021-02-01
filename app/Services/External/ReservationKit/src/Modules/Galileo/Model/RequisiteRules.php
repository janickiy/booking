<?php

namespace ReservationKit\src\Modules\Galileo\Model;

use ReservationKit\src\Modules\Avia\Model\Abstracts\RequisiteRulesAbstract;
use ReservationKit\src\Modules\Core\Model\Enum\ConditionEnum;

/**
 * Класс описывающий правила поиска и выписки в Galileo
 *
 * Содержит в себе:
 *  - коды PCC, где происходит поиск и выписка билетов,
 *  - коды авиакомпаний, по которым надо искать предложения или наоборот не надо;
 */
class RequisiteRules extends RequisiteRulesAbstract
{
    /**
     * Содержит код PCC для поиска
     *
     * @var string
     */
    private $_searchPCC;

    /**
     * Содержит код валидирующей компании
     *
     * @var string
     */
    private $_validationCompany;

    /**
     * @var array
     */
    private $_searchRules = array();

    /**
     * @var array|null
     */
    private $_conformityOfTicketWABsToCarriers;

    /**
     * Список разрешенных правил с соответсвующим методом для проверки
     *
     * @var array
     */
    private $_checkersFieldList = array(
        'carriers'         => 'checkCarriers',
        'excludeCarriers'  => 'checkExcludeCarriers',
        'countryDeparture' => 'checkCountryDeparture',
        'need3DAgreement'  => 'checkNeed3DAgreements'
    );

    public function __construct()
    {

    }

    public function fillFromJson($jsonData)
    {
        $rules = json_decode($jsonData, true);

        // Правила поиска
        if (isset($rules['search'])) {
            $searchRules = $rules['search'];

            if (isset($searchRules['pcc'])) {
                $this->setSearchPCC($searchRules['pcc']);
            }

            // Парсинг и установка правил
            foreach ($this->_checkersFieldList as $fieldName => $checkerMethodName) {
                if (isset($searchRules[$fieldName])) {
                    $condition = isset($searchRules[$fieldName]['condition']) ? $searchRules[$fieldName]['condition'] : null;
                    $value = isset($searchRules[$fieldName]['value']) ? $searchRules[$fieldName]['value'] : null;

                    $this->addSearchRule($fieldName, $condition, $value);
                }
            }
        }

        // Установка параметров выписки TODO сделать понятнее
        if (isset($rules['ticket']) && is_array($rules['ticket']))
            foreach ($rules['ticket'] as $ticketRules)
                if (is_array($ticketRules['carriers']) && count($ticketRules['carriers']) > 0) $this->addConformityOfTicketWABsToCarriers($ticketRules['pcc'], $ticketRules['carriers']);

        return $this;
    }

    public function toJson()
    {

    }

    /**
     * @return string
     */
    public function getSearchPCC()
    {
        return $this->_searchPCC;
    }

    /**
     * @param string $searchPCC
     */
    public function setSearchPCC($searchPCC)
    {
        $this->_searchPCC = $searchPCC;
    }

    /**
     * @param string $searchPCC
     * @return bool
     */
    public function isSearchPCC($searchPCC)
    {
        return $this->_searchPCC === $searchPCC;
    }

    /**
     * @return string
     */
    public function getValidationCompany()
    {
        return $this->_validationCompany;
    }

    /**
     * @param string $validationCompany
     */
    public function setValidationCompany($validationCompany)
    {
        $this->_validationCompany = $validationCompany;
    }

    /**
     * Возвращает список установленных правил
     *
     * @return array
     */
    public function getSearchRules()
    {
        return $this->_searchRules;
    }

    /**
     * Возвращает правило по названию (ключу)
     *
     * @param $fieldName
     * @return null
     */
    public function getSearchRuleByField($fieldName)
    {
        return $this->isExistSearchRuleField($fieldName) ? $this->_searchRules[$fieldName] : null;
    }

    /**
     * Проверяет существование поля $fieldName
     *
     * @param $fieldName
     * @return bool
     */
    public function isExistSearchRuleField($fieldName)
    {
        return isset($this->_searchRules[$fieldName]);
    }

    /**
     * Проверяет существование значения у поля $fieldName
     *
     * @param $fieldName
     * @return bool
     */
    public function isExistValueForSearchRuleField($fieldName)
    {
        return $this->isExistSearchRuleField($fieldName) && isset($this->_searchRules[$fieldName]['value']);
    }

    /**
     * Проверяет существование условия у поля $fieldName
     *
     * @param $fieldName
     * @return bool
     */
    public function isExistConditionForSearchRuleField($fieldName)
    {
        return $this->isExistSearchRuleField($fieldName) && isset($this->_searchRules[$fieldName]['condition']);
    }

    /**
     * Устанавливает список правил
     *
     * @param array $searchRules
     */
    public function setSearchRules($searchRules)
    {
        $this->_searchRules = $searchRules;
    }

    /**
     * @param $field
     * @param $condition
     * @param $value
     */
    public function addSearchRule($field, $condition, $value)
    {
        $this->_searchRules[$field] = array(
            'condition' => $condition,
            'value'     => $value
        );
    }

    /**
     * @return array|null
     */
    public function getConformityOfTicketWABsToCarriers()
    {
        return $this->_conformityOfTicketWABsToCarriers;
    }

    /**
     * @param array|null $conformityOfTicketWABsToCarriers
     */
    public function setConformityOfTicketWABsToCarriers($conformityOfTicketWABsToCarriers)
    {
        $this->_conformityOfTicketWABsToCarriers = $conformityOfTicketWABsToCarriers;
    }

    /**
     * Добавляет соответствие PCC перевозчикам
     *
     * @param string $PCC
     * @param array $carriers Список перевозчиков
     */
    public function addConformityOfTicketWABsToCarriers($PCC, $carriers)
    {
        if (is_null($this->_conformityOfTicketWABsToCarriers)) {
            $this->_conformityOfTicketWABsToCarriers = array();
        }

        $this->_conformityOfTicketWABsToCarriers[$PCC] = $carriers;
    }
}
