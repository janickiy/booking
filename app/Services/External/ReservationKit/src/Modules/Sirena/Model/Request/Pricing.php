<?php

namespace ReservationKit\src\Modules\Sirena\Model\Request;

use ReservationKit\src\Component\XML\XmlElement;

use ReservationKit\src\Modules\Avia\Model\Helper\SearchRequestOrBookingHelper;
use ReservationKit\src\Modules\Sirena\Model\Request;
use ReservationKit\src\Modules\Sirena\Model\Helper\RequestHelper;

class Pricing extends Request
{
    /**
     * @param \RK_Avia_Entity_Booking|\RK_Avia_Entity_Search_Request $searchRequestOrBooking
     */
    public function __construct($searchRequestOrBooking)
    {
        // Сегменты
        $this->addParam('Segments',
            new XmlElement('', array(),
                RequestHelper::getListRequestParam('Segment', $searchRequestOrBooking->getSegments())
            )
        );

        // Пассажиры
        $this->addParam('Passengers',
            new XmlElement('', array(),
                RequestHelper::getListRequestParam('Passenger', $searchRequestOrBooking->getPassengers())
            )
        );

        // Параметры ответа
        $this->addParam('AnswerParams',
            new XmlElement('answer_params', array(), array(
                new XmlElement('show_available', array(), 'true'),
                new XmlElement('show_io_matching', array(), 'true'),
                new XmlElement('show_flighttime', array(), 'true'),
                new XmlElement('show_varianttotal', array(), 'true'),
                new XmlElement('show_baseclass', array(), 'true'),

                // Общие для всех запросов
                new XmlElement('lang', array(), 'en'),
                new XmlElement('curr', array(), 'RUB'),
            ))
        );

        // Параметры ответа
        $this->addParam('RequestParams',
            new XmlElement('request_params', array(), array(
                new XmlElement('min_results', array(), 'spOnePass'),
                new XmlElement('max_results', array(), '250'),
                //new XmlElement('mix_scls', array(), 'true'),
                //new XmlElement('mix_ac', array(), 'true'),
                new XmlElement('fingering_order', array(), 'differentFlightsCombFirst'),
                new XmlElement('tick_ser', array(), 'ЭБМ'),
                new XmlElement('et_if_possible', array(), 'true'),
                new XmlElement('timeout', array(), '150'),
            ))
        );

        parent::__construct();
    }

    public function getRequestName()
    {
        return 'pricing';
    }

    public function getRequestAttributes()
    {
        return array();
    }
}