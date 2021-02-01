<?php

namespace ReservationKit\src\Modules\Galileo\Model\Entity;

use ReservationKit\src\Modules\Galileo\Model\Entity\FareInfo as GalileoFareInfo;
//use ReservationKit\src\Modules\Galileo\Model\Entity\Price;
//use ReservationKit\src\Modules\Galileo\Model\Entity\BaggageAllowanceInfo;

/**
 * Класс с информацией о прайсе, специфичной (необходимой) для Galileo
 */
class Price extends \RK_Avia_Entity_Price
{
    /**
     * Уникальный ключ-идентификатор в формате Base64UUID
     *
     * @var string
     */
    private $_key;

    private $_approximateTotalPrice;

    private $_approximateBasePrice;

    private $_approximateTaxes;

    private $_includesVAT;

    /**
     * @var array
     */
    private $_hostTokens;

    /**
     * Возможные значения:
     *   Auto, Manual, ManualFare, Guaranteed, Invalid, Restored, Ticketed, Unticketable, Reprice, Expired,
     *   AutoUsingPrivateFare, GuaranteedUsingAirlinePrivateFare, Airline, AgentAssisted, VerifyPrice,
     *   AltSegmentRemovedReprice, AuxiliarySegmentRemovedReprice, DuplicateSegmentRemovedReprice, Unknown,
     *   GuaranteedUsingAgencyPrivateFare , AutoRapidReprice
     *
     * @var string
     */
    private $_pricingMethod;

    /**
     * Дополнительная информация о тарифе
     *
     * @var GalileoFareInfo[]
     */
    private $_fareInfo;

    /**
     * Массив связей элементов GalileoFareInfo с сегментами
     *
     * Необходимо при формировании запроса бронирования
     *
     * @var BookingInfo[]
     */
    private $_bookingInfo;

    /**
     * Массив с информацией о багаже для каждого сегмента
     *
     * @var BaggageAllowanceInfo[] $_baggageAllowances
     */
    private $_baggageAllowances = array();

    /**
     * Информация об актуальности тарифа
     *
     * @var FareGuaranteeInfo
     */
    private $_fareGuaranteeInfo;

    /**
     * Ключ ссылка на модификатор прайса
     *
     * @var
     */
    private $_ticketingModifiersRef;

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->_key = $key;
    }

    /**
     * @return \RK_Core_Money
     */
    public function getApproximateTotalPrice()
    {
        return $this->_approximateTotalPrice;
    }

    /**
     * @param mixed $approximateTotalPrice
     */
    public function setApproximateTotalPrice($approximateTotalPrice)
    {
        $this->_approximateTotalPrice = $approximateTotalPrice;
    }

    /**
     * @return \RK_Core_Money
     */
    public function getApproximateBasePrice()
    {
        return $this->_approximateBasePrice;
    }

    /**
     * @param mixed $approximateBasePrice
     */
    public function setApproximateBasePrice($approximateBasePrice)
    {
        $this->_approximateBasePrice = $approximateBasePrice;
    }

    /**
     * @return \RK_Core_Money
     */
    public function getApproximateTaxes()
    {
        return $this->_approximateTaxes;
    }

    /**
     * @param mixed $approximateTaxes
     */
    public function setApproximateTaxes($approximateTaxes)
    {
        $this->_approximateTaxes = $approximateTaxes;
    }

    /**
     * @return \RK_Core_Money
     */
    public function getTaxes()
    {
        return $this->_taxes;
    }

    /**
     * @param mixed $taxes
     */
    public function setTaxes($taxes)
    {
        $this->_taxes = $taxes;
    }

    /**
     * @return mixed
     */
    public function getIncludesVAT()
    {
        return $this->_includesVAT;
    }

    /**
     * @param mixed $includesVAT
     */
    public function setIncludesVAT($includesVAT)
    {
        $this->_includesVAT = $includesVAT;
    }

    /**
     * @return string
     */
    public function getPricingMethod()
    {
        return $this->_pricingMethod;
    }

    /**
     * @param string $pricingMethod
     */
    public function setPricingMethod($pricingMethod)
    {
        $this->_pricingMethod = $pricingMethod;
    }

    /**
     * @return GalileoFareInfo[]
     */
    public function getFareInfo()
    {
        return $this->_fareInfo;
    }

    /**
     * @param GalileoFareInfo[] $fareInfo
     */
    public function setFareInfo($fareInfo)
    {
        $this->_fareInfo = $fareInfo;
    }

    /**
     * @param GalileoFareInfo $fareInfo
     */
    public function addFareInfo(GalileoFareInfo $fareInfo)
    {
        $this->_fareInfo[$fareInfo->getKey()] = $fareInfo;
    }

    /**
     * Возвращает связи GalileoFareInfo с сегментами
     *
     * @return BookingInfo[]
     */
    public function getBookingInfoList()
    {
        return $this->_bookingInfo;
    }

    /**
     * Возвращает BookingInfo по номеру сегмента
     *
     * @param int $numSegment Номер сегмента
     * @return null|BookingInfo
     */
    public function getBookingInfoBySegmentNum($numSegment)
    {
        return isset($this->_bookingInfo[$numSegment]) ? $this->_bookingInfo[$numSegment] : null;
    }

    /**
     * Устанавливает связи FareInfo с сегментами
     *
     * @param BookingInfo[] $bookingInfo
     */
    public function setBookingInfoList($bookingInfo)
    {
        $this->_bookingInfo = $bookingInfo;
    }

    /**
     * Добавляет связь GalileoFareInfo с сегментами в массив связей
     *
     * @param BookingInfo $bookingInfo
     */
    public function addBookingInfo(BookingInfo $bookingInfo)
    {
        $this->_bookingInfo[] = $bookingInfo;
    }

    /**
     * Возвращает дополнительную информацию о прайсе по ключу-ссылке
     *
     * @param $keyRef
     * @return GalileoFareInfo|null
     */
    public function getFareInfoByRef($keyRef)
    {
        return isset($this->_fareInfo[$keyRef]) ? $this->_fareInfo[$keyRef] : null;
    }

    /**
     * Возвращает информацию о багаже
     * TODO вынести багаж в общий класс
     *
     * @return BaggageAllowanceInfo[]
     */
    public function getBaggageAllowances()
    {
        return $this->_baggageAllowances;
    }

    /**
     * Возвращает информацию о багаже для указанного сегмента
     *
     * @return BaggageAllowanceInfo
     */
    public function getBaggageAllowancesByNumSegment($num)
    {
        return isset($this->_baggageAllowances[$num]) ? $this->_baggageAllowances[$num] : null;
    }

    /**
     * Устанавливает информацию о багаже
     *
     * @param BaggageAllowanceInfo[] $baggageAllowances
     */
    public function setBaggageAllowances($baggageAllowances)
    {
        $this->_baggageAllowances = $baggageAllowances;
    }

    /**
     * Добавляет информацию о багаже для соответствующего сегмента
     *
     * @param BaggageAllowanceInfo $baggageAllowanceInfo
     */
    public function addBaggageAllowances($baggageAllowanceInfo)
    {
        $this->_baggageAllowances[] = $baggageAllowanceInfo;
    }

    /**
     * @return array
     */
    public function getHostTokens()
    {
        return isset($this->_hostTokens) ? $this->_hostTokens : array();
    }

    /**
     * @param array $hostTokens
     */
    public function setHostTokens($hostTokens)
    {
        $this->_hostTokens = $hostTokens;
    }

    /**
     * @return FareGuaranteeInfo
     */
    public function getFareGuaranteeInfo()
    {
        return $this->_fareGuaranteeInfo;
    }

    /**
     * @param FareGuaranteeInfo $fareGuaranteeInfo
     */
    public function setFareGuaranteeInfo($fareGuaranteeInfo)
    {
        $this->_fareGuaranteeInfo = $fareGuaranteeInfo;
    }

    /**
     * Возвращает ключ-ссылку модификатора
     *
     * @return mixed
     */
    public function getTicketingModifiersRef()
    {
        return $this->_ticketingModifiersRef;
    }

    /**
     * Устанавликает ключ-ссылку модификатора
     *
     * @param mixed $ticketingModifiersRef
     */
    public function setTicketingModifiersRef($ticketingModifiersRef)
    {
        $this->_ticketingModifiersRef = $ticketingModifiersRef;
    }

    /**
     * Проверяет актуальность текущего тарифа
     *
     * @return bool
     * @throws \RK_Core_Exception
     */
    public function isExpired()
    {
        if ($this->getFareGuaranteeInfo()) {
            if ($this->getFareGuaranteeInfo()->getGuaranteeType() === 'Expired' ||
                $this->getFareGuaranteeInfo()->getGuaranteeType() === 'Invalid') {
                return true;
            }

            return false;
        }

        throw  new \RK_Core_Exception('Can\'t check price isExpired. Not set FareGuaranteeInfo');
    }
}