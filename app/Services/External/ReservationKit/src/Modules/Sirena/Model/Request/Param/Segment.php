<?php

namespace ReservationKit\src\Modules\Sirena\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Sirena\Model\Entity\Segment as SirenaSegment;

class Segment extends XmlElement
{
    /**
     * Элемента сегмента для запросов <pricing> и <pricing_variant>
     * Формат для <pricing_variant> такой же, как у запроса <pricing>, но в каждом сегменте перевозки параметры авиакомпания, рейс и код класса бронирования являются обязательными.
     *
     * @param SirenaSegment $segment TODO сделать тип данных
     * @param null $segmentNum
     */
    public function __construct(/*SirenaSegment*/ $segment, $segmentNum = null)
    {
        // Общий элемента для запросов <pricing> и <pricing_variant>
        $contentSegment = array(
            new XmlElement('departure', array(), $segment->getDepartureCode()),
            new XmlElement('arrival', array(), $segment->getArrivalCode()),
            new XmlElement('date', array(), $segment->getDepartureDate()->formatTo('d.m.Y')->getValue()),
            //new XmlElement('time_from', array(), $segment->getDepartureTimeRangeFrom()->formatTo('Hi')->getValue()),
            //new XmlElement('time_till', array(), $segment->getDepartureTimeRangeTo()->formatTo('Hi')->getValue()),
            //new XmlElement('company', array(), $segment->getOperationCarrierCode()),
            //new XmlElement('class', array(), $segment->getBaseClass()),
        );

        // Обязательный элемента для запроса <pricing_variant>: номер рейса
        if ($segment->getFlightNumber()) {
            $contentSegment[] = new XmlElement('flight', array(), $segment->getFlightNumber());
        }

        // Обязательный элемента для запроса <pricing_variant>: перевозчик
        if ($segment->getOperationCarrierCode()) {
            $contentSegment[] = new XmlElement('company', array(), $segment->getOperationCarrierCode());
        }

        // Обязательный элемента для запроса <pricing_variant>: подкласс
        if ($segment->getSubClass()) {
            $contentSegment[] = new XmlElement('subclass', array(), $segment->getSubClass());
        }

        parent::__construct('segment', array(), $contentSegment);
    }
}