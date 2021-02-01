<?php

namespace ReservationKit\src\Modules\Galileo\Model\RequestParam;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Avia\Model\Entity\Segment;

class SearchAirLeg extends XmlElement
{
    /**
     * SearchAirLeg constructor.
     * @param Segment $segment
     * @throws \RK_Core_Exception
     * @throws \ReservationKit\src\Component\DB\Exception
     */
    public function __construct(Segment $segment)
    {
        // TODO Ранее CityOrAirport некорректно возвращал данные, и надо было конкретно уточнять город или аэропорт мы ищем
        // Сейчас возможно этот метод уже не понадобиться и его стоит удалить
        //$сityOrAirport = $this->cityOrAirportCode($segment);

        //$depType = (empty($сityOrAirport[$segment->getDepartureCode()])) ? 'CityOrAirport' : $сityOrAirport[$segment->getDepartureCode()];
        $depType = 'CityOrAirport';

        // Аэропорт отправления
        $SearchOrigin = new XmlElement('SearchOrigin', array(),
            new XmlElement($depType, array('Code' => $segment->getDepartureCode()), null, 'com'),
        'air');

        //$arrType = (empty($сityOrAirport[$segment->getArrivalCode()])) ? 'CityOrAirport' : $сityOrAirport[$segment->getArrivalCode()];
        $arrType = 'CityOrAirport';

        // Аэропорт прибытия
        $SearchDestination = new XmlElement('SearchDestination', array(),
            new XmlElement($arrType, array('Code' => $segment->getArrivalCode()), null, 'com'),    // City или Airport
        'air');

        // Время отправления (интервал)
        $TimeRange = null;
        if (!empty($segment->getDepartureTimeRange())) {
            // FIXME это плохо! Надо сделать объект аэропорта с данными о нем, включающими временную зону и работать с этом объектом, а не с БД
            $departureAirportCode = $segment->getDepartureCode();
            $departureCountryTimeZone = \ReservationKit\src\RK::getContainer()->getDbAdapterFor('catalog')
                ->query('select gmt_offset from avia_airports where iata_code = ? OR city_iata_code = ?', array($departureAirportCode, $departureAirportCode))
                ->fetchColumn('gmt_offset');

            // Знак "+" или "-" смещения времени
            $offsetTimezoneSign = ($departureCountryTimeZone >= 0) ? '+' : '-';

            // Текущая дата и время в городе/аэропорте отправления
            $nowInDepartureAirport = new \RK_Core_Date();
            $nowInDepartureAirport->getDateTime()->modify($offsetTimezoneSign . abs($departureCountryTimeZone - 3) . ' hours');
            $nowInDepartureAirport->getDateTime()->modify('+30 minutes');

            if ($segment->getDepartureTimeRangeFrom()->getDateTime() < $nowInDepartureAirport->getDateTime()) {
                $segment->setDepartureTimeRangeFrom($nowInDepartureAirport->formatTo($segment->getDepartureTimeRangeFrom()->getFormat()));
            }

            $attributesTimeRange = array(
                'EarliestTime' => $segment->getDepartureTimeRangeFrom()->formatTo(\RK_Core_Date::DATE_FORMAT_SERVICES),
                'LatestTime' => $segment->getDepartureTimeRangeTo()->formatTo(\RK_Core_Date::DATE_FORMAT_SERVICES)
            );

            $TimeRange = new XmlElement('TimeRange', $attributesTimeRange, null, 'com');
        }

        $attributesSearchDepTime = array();
        if (!$TimeRange) {
            $attributesSearchDepTime = array(
                'PreferredTime' => $segment->getDepartureDate()->formatTo(\RK_Core_Date::DATE_FORMAT_DB_DATE)
            );
        }

        // Дата отправления
        $SearchDepTime = new XmlElement('SearchDepTime', $attributesSearchDepTime, $TimeRange, 'air');

        // Классы перелета
        if (!empty($segment->getTypeClass())) {
            $AirLegModifiers = new XmlElement('AirLegModifiers', array(),
                new XmlElement('PreferredCabins', array(),
                    new XmlElement('CabinClass', array('Type' => $segment->getTypeClass()), null, 'com'),
                'air'),
            'air');
        }

        parent::__construct('SearchAirLeg', array(), array($SearchOrigin, $SearchDestination, $SearchDepTime, $AirLegModifiers), 'air');
    }

    private function cityOrAirportCode(Segment $segment)
    {
        // Список кодов всех аэропортов в прелете
        $airportCodeList = array(
            $segment->getDepartureCode(),
            $segment->getArrivalCode()
        );

        $airportCodeList = array_unique($airportCodeList);
        $airportCodeString = '\'' . implode('\',\'', $airportCodeList) . '\'';

        $segmentsCountryCodeList = \ReservationKit\src\RK::getContainer()->getDbAdapterFor('catalog')
            ->query('select iata_code, city_iata_code from avia_airports where iata_code IN (' . $airportCodeString . ') OR city_iata_code IN (' . $airportCodeString . ')')
            ->fetchArray();

        $airportCodeList = array_flip($airportCodeList);

        // Установка по умолчанию типа точки отправления/прибытия
        foreach ($airportCodeList as $airportCodeListNum => $airportCode) {
            $airportCodeList[$airportCodeListNum] = 'City';
        }

        foreach ($segmentsCountryCodeList as $segmentCountryCode) {
            if (isset($airportCodeList[$segmentCountryCode['city_iata_code']])) {
                $airportCodeList[$segmentCountryCode['city_iata_code']] = 'City';
            }

            if (isset($airportCodeList[$segmentCountryCode['iata_code']])) {
                $airportCodeList[$segmentCountryCode['iata_code']] = 'Airport';
            }
        }

        return $airportCodeList;
    }
}