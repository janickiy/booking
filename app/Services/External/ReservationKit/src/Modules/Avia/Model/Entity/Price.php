<?php

use \ReservationKit\src\RK;
use \ReservationKit\src\Modules\Core\Model\Enum\CurrencyEnum;
use \ReservationKit\src\Modules\Core\Model\Entity\CurrencyRates;
use \ReservationKit\src\Modules\Avia\Model\Entity\Tax;

use \ReservationKit\src\Modules\Avia\Model\Entity\BaggageAllowance as AviaBaggageAllowance;
use \ReservationKit\src\Modules\Galileo\Model\Entity\BaggageAllowanceInfo as GalileoBaggageAllowance;   // TODO rename BaggageAllowanceInfo to BaggageAllowance
use \ReservationKit\src\Modules\S7AgentAPI\Model\Entity\BaggageAllowance as S7BaggageAllowance;

/**
 * Прайс
 */
class RK_Avia_Entity_Price extends RK_Base_Entity_Price
{
    /**
     * Номер сегмента
     *
     * @var int
     */
    protected $_id;

    /**
     * Тип пассажира
     *
     * @var string
     * @see RK_Avia_Entity_Passenger
     */
    protected $_type;

    /**
     * Количество пассажиров с данными тарифами
     *
     * @var int
     */
    protected $_quantity;

    /**
     * Базовый тариф в валюте GDS
     *
     * @var RK_Core_Money
     */
    protected $_baseFare;

    /**
     * TODO Курс пересчета GDS. Если нигде не используется, то удалить это свойство и соответствующие методы
     *
     * @var float
     */
    protected $_rate;

    /**
     * Курсы валют
     *
     * @var CurrencyRates
     */
    protected $_currencyRates;

    /**
     * Эквивалентный тариф в запрашиваемой валюте
     *
     * @var RK_Core_Money
     */
    protected $_equivFare;

    /**
     * Итоговая цена
     *
     * @var RK_Core_Money
     */
    protected $_totalFare;

    /**
     * Размер скидки
     *
     * @var \RK_Core_Money
     */
    protected $_discountAmount;

    /**
     * Процент скидки
     *
     * @var int
     */
    protected $_discountPercent;

    /**
     * Стоимость билета
     *
     * @var array
     */
    protected $_ticketFares;

    /**
     * Список такс
     *
     * @var Tax[]
     */
    protected $_taxes = array();

    /**
     * Список кодов тарифов
     *
     * @var array
     */
    protected $_codeFares = array();

    /**
     * Строка расчета тарифа из GDS
     *
     * @var string
     */
    protected $_fareCalc;

    /**
     * Срок действия тарифа
     *
     * @var RK_Core_Date
     */
    protected $_ticketTimelimit;

    /**
     * Возвратность
     *
     * @var bool
     */
    protected $_refundable = null;

    /**
     * Доступность багажа для сегментов
     *
     * @var array
     */
    private $_baggageAllowance;

    /**
     * @var array
     */
    private $_ticketDesignator = array();

    /**
     * Дополнительные параметры вебсервисов
     *
     * @var array
     */
    protected $_extraParams = array();

    /**
     * Массив параметров для запроса правил
     *
     * @var array
     */
    protected $_fareRules = array();

    /**
     * Устнавливает номер сегмента
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * Возвращает номер сегмента
     *
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Возвращает тип пассажира, для которого применен тариф
     *
     * @return string ADT|CLD|INF|INS
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Устанавливает тип пассажира, для которого применен тариф
     *
     * @param string $value ADT|CLD|INF|INS
     */
    public function setType($value)
    {
        $this->_type = $value;
    }

    /**
     * Возвращет количество применений тарифа для пассажиров
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->_quantity;
    }

    /**
     * Устанавливает количество применений тарифа для пассажиров
     *
     * @param int $value
     */
    public function setQuantity($value)
    {
        $this->_quantity = (int) $value;
    }

    /**
     * Возвращает цену в валюте авиакомпании
     *
     * @return RK_Core_Money
     */
    public function getBaseFare()
    {
        return $this->_baseFare;
    }

    /**
     * Устанавливает цену в валюте авиакомпании
     *
     * @param RK_Core_Money $value
     */
    public function setBaseFare(RK_Core_Money $value)
    {
        $this->_baseFare = $value;
    }

    /**
     * Возвращает цену в валюте запроса (эквивалентная стоимость)
     *
     * @return float
     */
    public function getRate()
    {
        return $this->_rate;
    }

    /**
     * Устанавливает цену в валюте запроса (эквивалентная стоимость)
     *
     * @param float $value
     */
    public function setRate($value)
    {
        $this->_rate = is_numeric($value) ? (float) $value : 0.0;
    }

    /**
     * @return CurrencyRates
     */
    public function getCurrencyRates()
    {
        return $this->_currencyRates;
    }

    /**
     * @param CurrencyRates $currencyRates
     */
    public function setCurrencyRates($currencyRates)
    {
        $this->_currencyRates = $currencyRates;
    }

    /**
     * Возвращает цену в валюте запроса (эквивалентная стоимость)
     *
     * @return RK_Core_Money
     */
    public function getEquivFare()
    {
        return $this->_equivFare;
    }

    /**
     * Устанавливает цену в валюте запроса (эквивалентная стоимость)
     *
     * @param RK_Core_Money $value
     */
    public function setEquivFare(RK_Core_Money $value)
    {
        $this->_equivFare = $value;
    }

    /**
     * Возвращает итоговую стоимость тарифа
     * TODO переименовать в getTotalAmount
     *
     * @return RK_Core_Money
     */
    public function getTotalFare()
    {
        return $this->_totalFare;
    }

    /**
     * Устанавливает итоговую цену тарифа
     *
     * @param RK_Core_Money $value
     */
    public function setTotalFare(RK_Core_Money $value)
    {
        $this->_totalFare = $value;
    }

    /**
     * Возвращает размер скидки
     *
     * @return \RK_Core_Money
     */
    public function getDiscountAmount()
    {
        return $this->_discountAmount;
    }

    /**
     * Устанавливает размер скидки
     *
     * @param \RK_Core_Money $discountAmount
     */
    public function setDiscountAmount(\RK_Core_Money $discountAmount)
    {
        $this->_discountAmount = $discountAmount;
    }

    /**
     * Возвращает процент скидки
     *
     * @return int
     */
    public function getDiscountPercent()
    {
        return $this->_discountPercent;
    }

    /**
     * Устанавливает процент скидки
     *
     * @param int $discountPercent
     */
    public function setDiscountPercent($discountPercent)
    {
        $this->_discountPercent = $discountPercent;
    }

    /**
     * Возвращает все тарифы закрепленные за билетами
     *
     * @return array
     */
    public function getTicketFares()
    {
        return $this->_ticketFares;
    }

    /**
     * Возвращает стоимость билета по его номеру
     *
     * @param int $numTicket
     * @return array
     */
    public function getTicketFare($numTicket)
    {
        if (isset($this->_ticketFares[$numTicket])) {
            return $this->_ticketFares[$numTicket];
        }

        return null;
    }

    /**
     * Устанавливает тарифы для билетов
     * @param array $ticketFares
     */
    public function setTicketFares(array $ticketFares)
    {
        $this->_ticketFares = $ticketFares;
    }

    /**
     * Устанавливает тариф для билета
     * @param int $numTicket Номер билета
     * @param RK_Core_Money $value Тариф
     */
    //public function addTicketFare($numTicket, RK_Core_Money $value)
    public function addTicketFare($numTicket, $value)
    {
        $this->_ticketFares[(string) $numTicket] =  $value;
    }

    /**
     * Удаляет тариф по номеру билета
     * @param int $numTicket Номер билета
     */
    public function removeTicketFare($numTicket)
    {
        if (isset($this->_ticketFares[$numTicket])) {
            unset($this->_ticketFares[$numTicket]);
        }
    }

    /**
     * Сброс тарифов у билетов
     */
    public function resetTicketFares()
    {
        $this->_ticketFares = array();
    }

    /**
     * @return bool
     */
    public function isSetTaxes()
    {
        return !empty($this->_taxes);
    }

    /**
     * Возвращает все таксы
     *
     * @return Tax[]
     */
    public function getTaxes()
    {
        return $this->_taxes;
    }

    /**
<<<<<<< Updated upstream
     * FIXME бывают ситуации, когда у INF нет такс и метод вернет null, но это метод часто используется как getTaxesSum()->getAmount это может вызвать ошибки
     * необходимо продумать обработку значения по умолчанию, важно вернуть правильную валюту
     * 
=======
     * Возвращает таксу по коду
     * ВНИМАНИЕ: не учитывается, что такс с одинаковым кодом может быть больше 1
     *
     * @param $code
     * @return null|Tax
     */
    public function getTaxByCode($code)
    {
        foreach ($this->_taxes as $tax) {
            if ($tax->getCode() === $code) {
                return $tax;
            }
        }

        return null;
    }

    /**
>>>>>>> Stashed changes
     * Возвращает сумму такс
     *
     * @return RK_Core_Money
     * @throws RK_Core_Exception
     */
    public function getTaxesSum()
    {
        $sum = null;

        foreach ($this->getTaxes() as $tax) {
            if (!isset($sum)) {
                $sum = new \RK_Core_Money(0.0, $tax->getAmount()->getCurrency());
            }

            $sum = $sum->add($tax->getAmount());
        }

        // Если такс нет
        if (is_null($sum)) {
            if ($this->getEquivFare()) {
                $sum = new \RK_Core_Money(0.0, $this->getEquivFare()->getCurrency());
            } else {
                $sum = new \RK_Core_Money(0.0, $this->getBaseFare()->getCurrency());
            }

        }

        return $sum;
    }

    /**
     * Добавляет таксу
     *
     * @param $code
     * @param RK_Core_Money $amount
     */
    public function addTax($code, RK_Core_Money $amount)
    {
        $this->_taxes[] = new Tax($code, $amount);
    }

    /**
     * Устанавливает массив такс
     *
     * @param array $taxesList
     */
    public function setTaxes($taxesList)
    {
        $this->_taxes = $taxesList;
    }

    /**
     * Добавляет код тарифа для указанного сегмента
     *
     * @param $segment
     * @param $codeFare
     */
    public function addFare($segment, $codeFare)
    {
        $this->_codeFares[$segment] = $codeFare;
    }

    /**
     * TODO переименовать метод в getFareCodes
     *
     * Возвращает распределение кодов тариоф по номерам сегментов
     *
     * @return array
     */
    public function getFares()
    {
        return $this->_codeFares;
    }

    /**
     * Возвращает код тарифа по номеру сегмента
     *
     * @param int $segmentNum
     * @return null|string
     */
    public function getFare($segmentNum)
    {
        return isset($this->_codeFares[$segmentNum]) ? $this->_codeFares[$segmentNum] : null;
    }

    /**
     * Возвращает строку расчета стоимости
     * Может быть пустой
     *
     * @return string
     */
    public function getFareCalc()
    {
        return $this->_fareCalc;
    }

    /**
     * Устанавливает строку расчета стоимости
     *
     * @param string $value
     */
    public function setFareCalc($value)
    {
        $this->_fareCalc = $value;
    }

    /**
     * Возвращает срок действия тарифа
     *
     * @return RK_Core_Date
     */
    public function getTicketTimelimit()
    {
        return $this->_ticketTimelimit;
    }

    /**
     * Устанавливает срок действия тарифа
     *
     * @param RK_Core_Date $value
     */
    public function setTicketTimelimit(RK_Core_Date $value)
    {
        $this->_ticketTimelimit = $value;
    }

    /**
     * Устанавливает, возвратный тариф или нет
     *
     * @param bool $bool
     */
    public function setRefundable($bool)
    {
        $this->_refundable = $bool ? true : false;
    }

    /**
     * Проверяет, возвратный тариф или нет
     *
     * @return bool
     */
    public function isRefundable()
    {
        return $this->_refundable;
    }

    /**
     * Проверяет, установлена ли возвратность тарифа
     *
     * @return bool
     */
    public function hasRefundableSet()
    {
        return $this->_refundable !== null;
    }

    /**
     * @return BaggageAllowance[]
     */
    public function getBaggageAllowance()
    {
        return $this->_baggageAllowance;
    }

    /**
     * @param BaggageAllowance[] $baggageAllowance
     */
    public function setBaggageAllowance($baggageAllowance)
    {
        $this->_baggageAllowance = $baggageAllowance;
    }

    /**
     * @param BaggageAllowance $baggageAllowance
     */
    public function addBaggageAllowance($baggageAllowance)
    {
        $this->_baggageAllowance[] = $baggageAllowance;
    }

    /**
     * @param $numSegment
     * @param $baggageAllowance
     */
    public function addBaggageAllowanceToSegment($numSegment, $baggageAllowance)
    {
        $this->_baggageAllowance[$numSegment] = $baggageAllowance;
    }

    /**
     * Возвращает доступность багажа по номеру сегмента
     *
     * @param $numSegment
     * @return S7BaggageAllowance|GalileoBaggageAllowance|null
     */
    public function getBaggageAllowanceBySegment($numSegment)
    {
        return isset($this->_baggageAllowance[$numSegment]) ? $this->_baggageAllowance[$numSegment] : null;
    }

    /**
     * @return array
     */
    public function getTicketDesignator()
    {
        return $this->_ticketDesignator;
    }

    /**
     * @param array $ticketDesignator
     */
    public function setTicketDesignator($ticketDesignator)
    {
        $this->_ticketDesignator = $ticketDesignator;
    }

    /**
     * @param string $ticketDesignator
     */
    public  function addTicketDesignator($ticketDesignator)
    {
        $this->_ticketDesignator[] = $ticketDesignator;
    }

    /**
     * Добавляет дополнительный параметр
     *
     * @param string $key
     * @param mixed $value
     */
    public function addExtraParameter($key, $value)
    {
        $this->_extraParams[$key] = $value;
    }

    /**
     * Возвращает дополнительный параметр по ключу
     *
     * @param string $key
     * @return mixed|null
     */
    public function getExtraParameter($key)
    {
        if (isset($this->_extraParams[$key])) {
            return $this->_extraParams[$key];
        }
        return null;
    }

    /**
     * Возвращает тарифные правила
     *
     * @return array
     */
    public function getFareRules()
    {
        return $this->_fareRules;
    }

    /**
     * Добавление тарифного правила
     *
     * @param RK_Avia_Entity_Rule $fareRule
     */
    public function addFareRule(RK_Avia_Entity_Rule $fareRule)
    {
        $this->_fareRules[] = $fareRule;
    }
}