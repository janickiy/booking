<?php

namespace ReservationKit\src\Modules\Galileo\Model\Entity;

class Booking extends \RK_Avia_Entity_Booking
{
    /**
     * @var bool
     */
    private $_isInternationalSOS;

    /**
     * Версия записи UniversalRecord
     *
     * @var int
     */
    private $_version;

    /**
     * Номер UniversalRecord для Galileo
     * Используется для чтения или отмены брони
     *
     * @var string
     */
    private $_locatorUniversalRecord;

    /**
     * Номер ProviderReservationInfo для Galileo
     * Используется для отображения на сайте (операторы работают с ним)
     *
     * @var string
     */
    private $_locatorProviderReservation;

    /**
     * Номер AirReservation для Galileo
     * Используется для выписки
     *
     * @var
     */
    private $_locatorAirReservation;

    /**
     * Номер брони у перевозчика
     *
     * @var string
     */
    private $_locatorSupplier;

    /**
     * Код перевозчика
     *
     * @var string
     */
    private $_codeSupplier;

    /**
     * Дата создания брони у перевозчика
     *
     * @var \RK_Core_Date
     */
    private $_createDateSupplier;

    /**
     * @var array
     */
    private $_airPricingInfoRef;

    /**
     * @var array
     */
    private $_ticketingModifiersRefList;

    public function __construct()
    {
        parent::__construct();
        $this->setSystem('galileo');
        $this->setIsInternationalSOS(false);
    }

    /**
     * @return bool
     */
    public function isInternationalSOS()
    {
        return $this->_isInternationalSOS;
    }

    /**
     * @param bool $isInternationalSOS
     */
    public function setIsInternationalSOS($isInternationalSOS)
    {
        $this->_isInternationalSOS = $isInternationalSOS;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * @param int $version
     */
    public function setVersion($version)
    {
        $this->_version = $version;
    }

    /**
     * @return string
     */
    public function getLocatorUniversalRecord()
    {
        return $this->_locatorUniversalRecord;
    }

    /**
     * @param string $locatorUniversalRecord
     */
    public function setLocatorUniversalRecord($locatorUniversalRecord)
    {
        $this->_locatorUniversalRecord = $locatorUniversalRecord;
    }

    /**
     * @return string
     */
    public function getLocatorProviderReservation()
    {
        return $this->_locatorProviderReservation;
    }

    /**
     * @param string $locatorProviderReservation
     */
    public function setLocatorProviderReservation($locatorProviderReservation)
    {
        $this->_locatorProviderReservation = $locatorProviderReservation;
    }

    /**
     * @return mixed
     */
    public function getLocatorAirReservation()
    {
        return $this->_locatorAirReservation;
    }

    /**
     * @param mixed $locatorAirReservation
     */
    public function setLocatorAirReservation($locatorAirReservation)
    {
        $this->_locatorAirReservation = $locatorAirReservation;
    }

    /**
     * Возвращает номер брони у перевозчика
     *
     * @return string
     */
    public function getLocatorSupplier()
    {
        return $this->_locatorSupplier;
    }

    /**
     * Устанавливает номер брони у перевозчика
     *
     * @param string $locatorSupplier
     */
    public function setLocatorSupplier($locatorSupplier)
    {
        $this->_locatorSupplier = $locatorSupplier;
    }

    /**
     * @return string
     */
    public function getCodeSupplier()
    {
        return $this->_codeSupplier;
    }

    /**
     * @param string $codeSupplier
     */
    public function setCodeSupplier($codeSupplier)
    {
        $this->_codeSupplier = $codeSupplier;
    }

    /**
     * @return \RK_Core_Date
     */
    public function getCreateDateSupplier()
    {
        return $this->_createDateSupplier;
    }

    /**
     * @param \RK_Core_Date $createDateSupplier
     */
    public function setCreateDateSupplier($createDateSupplier)
    {
        $this->_createDateSupplier = $createDateSupplier;
    }

    /**
     * @return array
     */
    public function getAirPricingInfoRef()
    {
        return $this->_airPricingInfoRef;
    }

    /**
     * @param array $airPricingInfoRefList
     */
    public function setAirPricingInfoRef($airPricingInfoRefList)
    {
        $this->_airPricingInfoRef = $airPricingInfoRefList;
    }

    /**
     * @param string $airPricingInfoRef
     */
    public function addAirPricingInfoRef($airPricingInfoRef)
    {
        $this->_airPricingInfoRef[] = $airPricingInfoRef;
    }

    /**
     * @return array
     */
    public function getTicketingModifiersRefList()
    {
        return $this->_ticketingModifiersRefList;
    }

    /**
     * @param array $ticketingModifiersRefList
     */
    public function setTicketingModifiersRefList($ticketingModifiersRefList)
    {
        $this->_ticketingModifiersRefList = $ticketingModifiersRefList;
    }

    public function addTicketingModifiersRef($ticketingModifiersRef)
    {
        if (!is_array($this->_ticketingModifiersRefList)) {
            $this->_ticketingModifiersRefList = array();
        }

        $this->_ticketingModifiersRefList[] = $ticketingModifiersRef;
    }

    public function getTotalPrice()
    {
        $sum = null;

        foreach ($this->getPrices() as $price) {
            if (!isset($sum)) {
                $sum = new \RK_Core_Money(0.0, $price->getTotalFare()->getCurrency());
            }

            $sum = $sum->add($price->getTotalFare());
        }

        return $sum;
    }

    public function getBasePrice()
    {
        $sum = null;

        foreach ($this->getPrices() as $price) {
            if (!isset($sum)) {
                $sum = new \RK_Core_Money(null, $price->getBaseFare()->getCurrency());
            }

            $sum = $sum->add($price->getBaseFare());
        }

        return $sum;
    }

    public function getApproximateTotalPrice()
    {
        $sum = null;

        foreach ($this->getPrices() as $price) {
            if (!isset($sum)) {
                $sum = new \RK_Core_Money(null, $price->getApproximateTotalPrice()->getCurrency());
            }

            $sum = $sum->add($price->getApproximateTotalPrice());
        }

        return $sum;
    }

    public function getApproximateBasePrice()
    {
        $sum = null;

        foreach ($this->getPrices() as $price) {
            if (!isset($sum)) {
                $sum = new \RK_Core_Money(null, $price->getApproximateBasePrice()->getCurrency());
            }

            $sum = $sum->add($price->getApproximateBasePrice());
        }

        return $sum;
    }

    public function getEquivalentBasePrice()
    {
        $sum = null;

        foreach ($this->getPrices() as $price) {
            if ($price->getEquivFare()) {
                if (!isset($sum)) {
                    $sum = new \RK_Core_Money(null, $price->getEquivFare()->getCurrency());
                }

                $sum = $sum->add($price->getEquivFare());

            } else {
                return null;
            }
        }

        return $sum;
    }

    // TODO переименовать в getTotalAmountTaxes
    public function getTaxes()
    {
        $sum = null;

        foreach ($this->getPrices() as $price) {
            if (!isset($sum)) {
                if ($price->isSetTaxes()) {
                    $currency = $price->getTaxesSum()->getCurrency();
                } else {
                    $currency = $this->getApproximateTaxes()->getCurrency();
                }

                $sum = new \RK_Core_Money(0.0, $currency);
            }

            if ($price->isSetTaxes()) {
                $sum = $sum->add($price->getTaxesSum());
            } else {
                $sum = $sum->add($this->getApproximateTaxes());
            }

        }

        return $sum;
    }

    public function getApproximateTaxes()
    {
        $sum = null;

        foreach ($this->getPrices() as $price) {
            if (!isset($sum)) {
                $sum = new \RK_Core_Money(null, $price->getApproximateTaxes()->getCurrency());
            }

            $sum = $sum->add($price->getApproximateTaxes());
        }

        return $sum;
    }

    /**
     * Возвращает пассажира по ключу-ссылке
     *
     * @param $keyRef
     * @return null|Passenger
     */
    public function getPassengerByKeyRef($keyRef)
    {
        $passengers = $this->getPassengers();

        foreach ($passengers as $passenger) {
            if ($passenger->getKey() === $keyRef) {
                return $passenger;
            }
        }

        return null;
    }

    /**
     * Проверяет наличие туркода в прайсах бронирования
     *
     * @return bool
     */
    public function hasTourCodeInPrices()
    {
        foreach ($this->getPrices() as $price) {
            foreach ($price->getFareInfo() as $fareInfo) {
                if ($fareInfo->getTourCode()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Проверяет актуальность всех тарифов
     *
     * @return bool
     * @throws \RK_Core_Exception
     */
    public function hasExpiredPrice()
    {
        foreach ($this->getPrices() as $price) {
            if ($price->isExpired()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Осторожно!
     * Это динамический метод. Возвращает дату относительно текущего времени.
     * Использовать только при создании бронирования.
     *
     * Можно сделать метод статическим и безопасным, если считать nextDay относительно даты создания бронирования
     *
     * Соловцов Александр, 31.07.2016 17:53:14:
     * 1. Если до вылета меньше 5 часов, ТЛ время создания заявки + 30 мин.
     * 2. Если до вылета от 5 до 36 часов, ТЛ время создания заявки + 1 час.
     * 3. Если время до вылета более 36 часов, до 72 часов, ставим 12 часов.
     * 4. Если время до вылета более 72 часов, до 2 недель, ставим 24 часа.
     * 5. Если время до вылета больше 2 недель, ставим от 24 до 1 недели.
     * 6. Также время которое мы ставим не может быть больше того что отвечает Галилео! (это доп. проверка).
     *    23-59 обрезается до времени создания заявки, если TL в ответе Галилео >= 36 часов, но <= 48 часам
     *
     * @return \RK_Core_Date
     */
    public function calculateTiсketTimelimit()
    {
        if ($this->getTimelimit() instanceof \RK_Core_Date) {

            if ($this->getStatus() === 'new') {
                // Используется для расчета TL перед отправкой запроса бронирования. Т.к. бронь еще не создана, то даты создания у нее нет.
                // TL расчитывается относительно текущего момента.
                $dateCreate = new \DateTime('now', $this->getTimelimit()->getDateTime()->getTimezone());
            } else {
                // Используется для расчета TL, когда бронь уже создана и есть дата создания брони.
                // TL расчитывается относительно даты создания брони.
                $dateCreate = $this->getBookingDate()->getDateTime();
            }

            $departureDate = $this->getFirstSegment()->getDepartureDate()->getDateTime();
            $ticketTimelimit = $this->getTimelimit()->getDateTime();

            // (1) до вылета < 5 часов
            $dateCreate5H = clone $dateCreate;
            $dateCreate5H->add(new \DateInterval('PT5H'));
            if ($departureDate < $dateCreate5H) {
                $timeLimitCalc = clone $dateCreate;
                $timeLimitCalc->add(new \DateInterval('PT30M'));

                if ($timeLimitCalc > $ticketTimelimit) {
                    $timeLimitCalc = clone $ticketTimelimit;
                }

                return new \RK_Core_Date($timeLimitCalc);
            }

            // (2) до вылета < 36 часов
            $dateCreate36H = clone $dateCreate;
            $dateCreate36H->add(new \DateInterval('PT36H'));
            if ($departureDate < $dateCreate36H) {
                $timeLimitCalc = clone $dateCreate;
                $timeLimitCalc->add(new \DateInterval('PT1H'));

                if ($timeLimitCalc > $ticketTimelimit) {
                    $timeLimitCalc = clone $ticketTimelimit;
                }

                return new \RK_Core_Date($timeLimitCalc);
            }

            // (3) до вылета < 72 часов
            $dateCreate72H = clone $dateCreate;
            $dateCreate72H->add(new \DateInterval('PT72H'));
            if ($departureDate < $dateCreate72H) {
                $timeLimitCalc = clone $dateCreate;
                $timeLimitCalc->add(new \DateInterval('PT12H'));

                if ($timeLimitCalc > $ticketTimelimit) {
                    $timeLimitCalc = clone $ticketTimelimit;
                }

                return new \RK_Core_Date($timeLimitCalc);
            }

            // (4) до вылета < 2х недель!
            $dateCreate14D = clone $dateCreate;
            $dateCreate14D->add(new \DateInterval('P14D'));
            if ($departureDate < $dateCreate14D) {
                $timeLimitCalc = clone $dateCreate;
                $timeLimitCalc->add(new \DateInterval('PT24H'));

                if ($timeLimitCalc > $ticketTimelimit) {
                    $timeLimitCalc = clone $ticketTimelimit;
                }

                return new \RK_Core_Date($timeLimitCalc);
            }

            // (5) до вылета >= 2х недель, TL < 1 недели
            $dateCreate7D = clone $dateCreate;
            $dateCreate7D->add(new \DateInterval('P7D'));
            if ($ticketTimelimit < $dateCreate7D) {
                $timeLimitCalc = clone $dateCreate;
                $timeLimitCalc->add(new \DateInterval('P7D'));

                if ($timeLimitCalc > $ticketTimelimit) {
                    $timeLimitCalc = clone $ticketTimelimit;
                }

                // 36H < TL < 48H, если TL в этом интервале, то ВРЕМЯ (не дата) меняется на время создания брони
                $hoursBeforeTL = $timeLimitCalc->diff($dateCreate)->h;
                if ($hoursBeforeTL >= 36 && $hoursBeforeTL <= 48) {
                    $timeLimitCalc->setTime($dateCreate->format('H'), $dateCreate->format('i'), $dateCreate->format('s'));
                }

                return new \RK_Core_Date($timeLimitCalc);
            }

            // (6) до вылета >= 2х недель, TL >= 1 недели TODO (5) и (6) скорее всего можно объеденить
            $timeLimitCalc = clone $dateCreate;
            $timeLimitCalc->add(new \DateInterval('P7D'));

            return new \RK_Core_Date($timeLimitCalc);

        } else {
            // Ошибка не установлен таймлимит
            // TODO сделать обработку или оповещение об ошибке
        }
    }

    public function getTiсketTimelimit()
    {
        return new \RK_Core_Date($this->getTimelimit()->getDateTime());
    }
}