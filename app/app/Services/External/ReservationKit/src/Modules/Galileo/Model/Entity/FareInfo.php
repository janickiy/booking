<?php

namespace ReservationKit\src\Modules\Galileo\Model\Entity;

use ReservationKit\src\Modules\Avia\Model\Entity\FareInfo as AviaFareInfo;

/**
 * Класс содержит иформацию специфическую (необходимую) для Galileo
 *
 * Часть параметров необязательны (т.е. без них можно создать бронь, выписать и т.д.), но они рекомендуемы специалистами
 * Galileo и без них сертифкацию для продакшен-версии не получить.
 */
class FareInfo extends AviaFareInfo
{
    /**
     * Уникальный ключ-идентификатор в формате Base64UUID
     *
     * @var string
     */
    private $_key;

    /**
     * Ключ правила
     *
     * @var string
     */
    private $_ruleKey;

    /**
     * Аэропорт отправления
     *
     * @var string
     */
    private $_departureAirportCode;

    /**
     * Аэропорт прибытия
     *
     * @var string
     */
    private $_arrivalAirportCode;

    /**
     * Дата получения тарифа
     *
     * @var \RK_Core_Date
     */
    private $_effectiveDate;

    /**
     * Код тарифа
     *
     * @var string
     */
    private $_fareBasis;

    /**
     * Тип пассажира
     *
     * @var string
     */
    private $_passengerTypeCode;

    /**
     * Тур-код
     *
     * @var string
     */
    private $_tourCode;

    /**
     * Рекомендуемые параметры для сертификации
     */
    private $_brand;
    private $_departureDate;
    private $_amount;
    private $_notValidBefore;
    private $_notValidAfter;
    private $_taxAmount;

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->_key = $key;
    }

    /**
     * @return string
     */
    public function getDepartureAirportCode()
    {
        return $this->_departureAirportCode;
    }

    /**
     * @param string $departureAirportCode
     */
    public function setDepartureAirportCode($departureAirportCode)
    {
        $this->_departureAirportCode = $departureAirportCode;
    }

    /**
     * @return string
     */
    public function getArrivalAirportCode()
    {
        return $this->_arrivalAirportCode;
    }

    /**
     * @param string $arrivalAirportCode
     */
    public function setArrivalAirportCode($arrivalAirportCode)
    {
        $this->_arrivalAirportCode = $arrivalAirportCode;
    }

    /**
     * @return \RK_Core_Date
     */
    public function getEffectiveDate()
    {
        return $this->_effectiveDate;
    }

    /**
     * @param \RK_Core_Date $effectiveDate
     */
    public function setEffectiveDate($effectiveDate)
    {
        $this->_effectiveDate = $effectiveDate;
    }

    /**
     * @return string
     */
    public function getFareBasis()
    {
        return $this->_fareBasis;
    }

    /**
     * @param string $fareBasis
     */
    public function setFareBasis($fareBasis)
    {
        $this->_fareBasis = $fareBasis;
    }

    /**
     * @return string
     */
    public function getPassengerTypeCode()
    {
        return $this->_passengerTypeCode;
    }

    /**
     * @param string $passengerTypeCode
     */
    public function setPassengerTypeCode($passengerTypeCode)
    {
        $this->_passengerTypeCode = $passengerTypeCode;
    }

    /**
     * Возвращает ключ правила
     *
     * @return string
     */
    public function getRuleKey()
    {
        return $this->_ruleKey;
    }

    /**
     * Устанавливает ключ правила
     *
     * @param string $ruleKey
     */
    public function setRuleKey($ruleKey)
    {
        $this->_ruleKey = $ruleKey;
    }

    /**
     * @return Brand
     */
    public function getBrand()
    {
        return $this->_brand;
    }

    /**
     * @param Brand $brand
     */
    public function setBrand($brand)
    {
        $this->_brand = $brand;
    }

    /**
     * @return mixed
     */
    public function getDepartureDate()
    {
        return $this->_departureDate;
    }

    /**
     * @param mixed $departureDate
     */
    public function setDepartureDate($departureDate)
    {
        $this->_departureDate = $departureDate;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->_amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->_amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getNotValidBefore()
    {
        return $this->_notValidBefore;
    }

    /**
     * @param mixed $notValidBefore
     */
    public function setNotValidBefore($notValidBefore)
    {
        $this->_notValidBefore = $notValidBefore;
    }

    /**
     * @return mixed
     */
    public function getNotValidAfter()
    {
        return $this->_notValidAfter;
    }

    /**
     * @param mixed $notValidAfter
     */
    public function setNotValidAfter($notValidAfter)
    {
        $this->_notValidAfter = $notValidAfter;
    }

    /**
     * @return mixed
     */
    public function getTaxAmount()
    {
        return $this->_taxAmount;
    }

    /**
     * @param mixed $taxAmount
     */
    public function setTaxAmount($taxAmount)
    {
        $this->_taxAmount = $taxAmount;
    }

    /**
     * Возвращает тур-код
     *
     * @return string
     */
    public function getTourCode()
    {
        return $this->_tourCode;
    }

    /**
     * Устанавливает тур-код
     *
     * @param string $tourCode
     */
    public function setTourCode($tourCode)
    {
        $this->_tourCode = $tourCode;
    }
}