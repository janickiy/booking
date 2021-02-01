<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\S7AgentAPI\Model\Request;
use ReservationKit\src\Modules\S7AgentAPI\Model\Helper\Request as HelperRequest;

use ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param\AgentUserSender;

class AirShoppingRQ extends Request
{
    /**
     * @param \RK_Avia_Entity_Search_Request $searchRequest
     */
    public function __construct(\RK_Avia_Entity_Search_Request $searchRequest)
    {
        $this->addParam('Document',
            new XmlElement('Document', array(), null, 'ns1', true)
        );

        $this->addParam('Party',
            new XmlElement('Party', array(),
                new XmlElement('Sender', array(),
                    new AgentUserSender(false, false, '0.52')
                , 'ns1')
            , 'ns1')
        );

        $this->addParam('Parameters',
            new XmlElement('Parameters', array(),
                new XmlElement('CurrCodes', array(),
                    new XmlElement('CurrCode', array(), 'RUB', 'ns1'), 'ns1'
                ), 'ns1'
            )
        );

        $this->addParam('Travelers',
            new XmlElement('Travelers', array(),
                HelperRequest::getListRequestParam('Traveler', $searchRequest->getPassengers())
            , 'ns1')
        );

        $this->addParam('CoreQuery',
            new XmlElement('CoreQuery', array(),
                new XmlElement('OriginDestinations', array(),
                    HelperRequest::getListRequestParam('OriginDestinationForSearch', $searchRequest->getSegments())
                , 'ns1')
            , 'ns1')
        );

        // 3-х стронний договор
        if ($triPartyAgreement = $searchRequest->getTriPartyAgreementByCarrierCode('S7')) {
            $this->addParam('Qualifiers',
                new XmlElement('Qualifiers', array(),
                    new XmlElement('Qualifier', array(),
                        new XmlElement('SpecialFareQualifiers', array(), array(
                            new XmlElement('AirlineID', array(), $triPartyAgreement->getCarrier(), 'ns1'),
                            new XmlElement('CompanyIndex', array(), $triPartyAgreement->getTourCode(), 'ns1'),
                            new XmlElement('Account', array(), '692', /*$triPartyAgreement->getAccountCode(),*/ 'ns1')
                        ), 'ns1')
                    , 'ns1')
                , 'ns1')
            );
        }

        // Только прямые перелеты
        $directFlight = null;
        if ($searchRequest->isDirect()) {
            $directFlight = new XmlElement('Characteristic', array(),
                new XmlElement('DirectPreferences', array(), 'Preferred', 'ns1'), 'ns1'
            );
        }

        // TODO метод getClassBase - кривой проверить и переделать
        /*
        switch ($searchRequest->getClassBase()) {
            case '':
                // Любой
                $baseClass = 'C';
                break;
            case 'C':
                // Бизнес
                $baseClass = 'D';
                break;
            default:
                $baseClass = $searchRequest->getClassBase();
        }
        */
        $baseClass = 'Y';

        $this->addParam('Preferences',
            new XmlElement('Preferences', array(),
                new XmlElement('Preference', array(),
                    new XmlElement('FlightPreferences', array(), array(
                        new XmlElement('Aircraft', array(),
                            new XmlElement('Cabins', array(),
                                new XmlElement('Cabin', array(),
                                    new XmlElement('Code', array(), $baseClass, 'ns1'), 'ns1'
                                ), 'ns1'
                            ), 'ns1'
                        ),

                        $directFlight

                    ), 'ns1'), 'ns1'
                ), 'ns1'
            )
        );

        parent::__construct();
    }

    /**
     * @return array
     */
    public function getFunctionAttributes()
    {
        return array(
            'xmlns:ns1' => 'http://www.iata.org/IATA/EDIST',
            'Version'   => '1.0'
        );
    }

    public function getWSDLServiceName()
    {
        return 'AirShoppingRQ';
    }

    public function getWSDLFunctionName()
    {
        return 'searchFlightsJourney';
    }

    public function getFunctionNameSpace()
    {
        return '';
    }
}