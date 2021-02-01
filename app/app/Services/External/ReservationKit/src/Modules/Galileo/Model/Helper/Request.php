<?php

namespace ReservationKit\src\Modules\Galileo\Model\Helper;

use ReservationKit\src\Modules\Avia\Model\Entity\Search\Params\Passenger;
use ReservationKit\src\Modules\Galileo\Model\RequestParam;
use ReservationKit\src\Component\XML\XmlElement;

// TODO отрефакторить класс
class Request
{
    private static $_classRef = array(
        'Economy'         => 'Y',
        'Business'        => 'C',
        'First'           => 'F',
        'PremiumEconomy'  => 'W'
    );

    /**
     * @param string $requestParamName
     * @param array $items
     * @param mixed $options
     * @return XmlElement[]
     */
    public static function getListRequestParam($requestParamName, array $items, $options = null)
    {
        $listRequestParam = array();

        foreach ($items as $key => $item) {
            $listRequestParam[] = self::buildRequestParam($requestParamName, $item, $key, $options);
        }

        return $listRequestParam;
    }

    /**
     * @param string $requestParamName
     * @param mixed $item
     * @param int|null $key
     * @param mixed|null $options
     * @return XmlElement
     */
    public static function buildRequestParam($requestParamName, $item, $key = null, $options = null)
    {
        $requestParamClass = 'ReservationKit\\src\\Modules\\Galileo\\Model\\RequestParam\\' . $requestParamName;

        if (!class_exists($requestParamClass)) {
            throw new \RuntimeException('Класс-параметр ' . $requestParamName . ' не существует');
        }

        // TODO $requestParamClass instanceof XmlElement. Сделать эту проверку

        return new $requestParamClass($item, $key, $options);
    }

    /**
     * FIXME найти, где используется и заменить методом аналогом выше
     *
     * @param array $passengers
     * @return array
     */
    public static function getSearchPassengerList(array $passengers)
    {
        $searchPassengerList = array();

        /* @var Passenger $passenger */
        foreach ($passengers as $passenger) {
            for ($i = 0; $i < $passenger->getCount(); $i++) {
                $searchPassengerList[] = new RequestParam\SearchPassenger($passenger);
            }
        }

        return $searchPassengerList;
    }

    /**
     * Возвращает массив элементов BookingInfo
     *
     * @param \RK_Avia_Entity_Booking $booking
     * @param $typePassenger
     * @return array
     * @throws \Exception
     */
    public static function getListBookingInfo(\RK_Avia_Entity_Booking $booking, $typePassenger)
    {
        $prices = $booking->getPrices();

        if (isset($prices[$typePassenger])) {
            $pricePassenger  = $prices[$typePassenger];
            $bookingInfoList = $pricePassenger->getBookingInfoList();

            return $bookingInfoList;
        }

        throw new \Exception('Prices not found');
    }

    public static function getFilterByCarriers($nameFilter, array $carriers)
    {
        if (!empty($carriers)) {
            $carriersXmlElementList = array();

            foreach ($carriers as $carrierCode) {
                $carriersXmlElementList[] = new XmlElement('Carrier', array('Code' => $carrierCode), array(), 'com');
            }

            return new XmlElement($nameFilter, array(), $carriersXmlElementList, 'air');
        }

        return null;
    }

    /**
     * Возвращает базовый класс по типу
     * TODO не сработает для PremiumEconomy
     *
     * @param $type
     * @return mixed
     */
    public static function getBaseClassByType($type)
    {
        return self::$_classRef[ucfirst(strtolower($type))];
    }

    /**
     * Возвращает тип класса по базовому классу
     *
     * @param $baseClass
     * @return mixed
     */
    public static function getTypeClassByBase($baseClass)
    {
        return array_search($baseClass, self::$_classRef);
    }
}