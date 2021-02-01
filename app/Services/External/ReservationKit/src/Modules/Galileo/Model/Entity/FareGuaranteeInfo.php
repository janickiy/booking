<?php

namespace ReservationKit\src\Modules\Galileo\Model\Entity;

class FareGuaranteeInfo
{
    /**
     * Дата до которой тариф актуален
     *
     * @var \RK_Core_Date
     */
    private $_guaranteeDate;

    /**
     * Статус актуальности тарифа
     *
     * @var enum Auto, Manual, ManualFare, Guaranteed, Invalid, Restored, Ticketed, Unticketable, Reprice, Expired,
     *           AutoUsingPrivateFare, GuaranteedUsingAirlinePrivateFare, Airline, GuaranteeExpired,
     *           AgencyPrivateFareNoOverride, Unknown
     */
    private $_guaranteeType;

    /**
     * @return \RK_Core_Date
     */
    public function getGuaranteeDate()
    {
        return $this->_guaranteeDate;
    }

    /**
     * @param \RK_Core_Date $guaranteeDate
     */
    public function setGuaranteeDate($guaranteeDate)
    {
        $this->_guaranteeDate = $guaranteeDate;
    }

    /**
     * @return string
     */
    public function getGuaranteeType()
    {
        return $this->_guaranteeType;
    }

    /**
     * @param string $guaranteeType
     */
    public function setGuaranteeType($guaranteeType)
    {
        $this->_guaranteeType = $guaranteeType;
    }
}