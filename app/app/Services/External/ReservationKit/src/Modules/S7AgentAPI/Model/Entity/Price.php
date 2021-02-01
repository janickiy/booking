<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Entity;

//use ReservationKit\src\Modules\S7Agent\Model\Entity\FareInfo as GalileoFareInfo;
//use ReservationKit\src\Modules\S7Agent\Model\Entity\Price;
//use ReservationKit\src\Modules\S7Agent\Model\Entity\BaggageAllowanceInfo;

/**
 * Класс с информацией о прайсе, специфичной (необходимой) для S7Agent
 */
class Price extends \RK_Avia_Entity_Price
{
    /**
     * @var string
     */
    private $_offerItemID;
    
    /**
     * @var \RK_Core_Money
     */
    private $_totalSurcharge;

    /**
     * @var float
     */
    private $_exchangeRate;

    /**
     * @var array
     */
    private $_endorsments = array();

    /**
     * @return string
     */
    public function getOfferItemID(): string
    {
        return $this->_offerItemID;
    }

    /**
     * @param string $offerItemID
     */
    public function setOfferItemID(string $offerItemID)
    {
        $this->_offerItemID = $offerItemID;
    }

    /**
     * @return \RK_Core_Money
     */
    public function getTotalSurcharge()
    {
        return $this->_totalSurcharge;
    }

    /**
     * @param \RK_Core_Money $totalSurcharge
     */
    public function setTotalSurcharge($totalSurcharge)
    {
        $this->_totalSurcharge = $totalSurcharge;
    }

    /**
     * @return float
     */
    public function getExchangeRate()
    {
        return $this->_exchangeRate;
    }

    /**
     * @param float $exchangeRate
     */
    public function setExchangeRate($exchangeRate)
    {
        $this->_exchangeRate = $exchangeRate;
    }

    /**
     * Возвращает набор параметров endorsments
     *
     * @param bool $isGetUniq Возвращает только уникальные параметры
     * @return array
     */
    public function getEndorsments($isGetUniq = false): array
    {
        return $isGetUniq ? array_unique($this->_endorsments) : $this->_endorsments;
    }

    /**
     * @param array $endorsments
     */
    public function setEndorsments(array $endorsments)
    {
        $this->_endorsments = $endorsments;
    }

    /**
     * @param mixed $endorsment
     */
    public function addEndorsment($endorsment)
    {
        $this->_endorsments[] = $endorsment;
    }

    /**
     * @param string $pattern RegEx шаблон
     * @return null|string
     */
    public function findEndorsmentByPattern($pattern)
    {
        if (!empty($this->_endorsments)) {
            foreach ($this->_endorsments as $endorsment) {
                if (preg_match($pattern, $endorsment, $matches)) {
                    return $matches;
                }
            }
        }

        return null;
    }
}
