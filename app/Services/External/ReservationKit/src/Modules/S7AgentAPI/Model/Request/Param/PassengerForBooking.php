<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Passenger;

class PassengerForBooking extends XmlElement
{
    /**
     * @param Passenger $passenger
     * @param null $passengerNum
     */
    public function __construct(Passenger $passenger, $passengerNum = null)
    {
        $gender = $passenger->getGender() === 'M' ? 'Male' : 'Female';
        $email  = empty($passenger->getEmail()) ? 'it@trivago.ru' : $passenger->getEmail();

        // Разбиение номера телефона на части
        $result = preg_match('/(\d)(\d{3})(.*)/', (int) $passenger->getPhone(), $matches);
        $phoneAreaCode = $matches[2];
        $phoneNumber   = $matches[3];

        // Мильная карта
        $FQTVs = null;
        if (!empty($passenger->getLoyaltyCardBySupplier('S7'))) {
            $FQTVs =
            new XmlElement('FQTVs', array(),
                new XmlElement('TravelerFQTV_Information', array(), array(
                    new XmlElement('AirlineID', array(), 'S7', 'ns1'),
                    new XmlElement('Account', array(),
                        new XmlElement('Number', array(), $passenger->getLoyaltyCardBySupplier('S7'), 'ns1')
                    , 'ns1'),
                    new XmlElement('ProgramID', array(), 'S7', 'ns1')
                ), 'ns1')
            , 'ns1');
        }

        parent::__construct('Passenger', array('ObjectKey' => 'SH' . ($passengerNum + 1)), array(
            new XmlElement('PTC', array('Quantity' => '1'), $passenger->getType(), 'ns1'),

            new XmlElement('Age', array(),
                new XmlElement('BirthDate', array(), $passenger->getBorndate('Y-m-d'), 'ns1')
            , 'ns1'),

            new XmlElement('Name', array(), array(
                new XmlElement('Surname', array(), $passenger->getLastname(), 'ns1'),
                new XmlElement('Given', array(), $passenger->getFirstname(), 'ns1'),
                new XmlElement('Title', array(), $passenger->getPrefixName(true), 'ns1'),
                //new XmlElement('Middle', array(), $passenger->getMiddlename(), 'ns1'),
            ), 'ns1'),

            new XmlElement('Contacts', array(),
                new XmlElement('Contact', array(), array(
                    new XmlElement('EmailContact', array(),
                        new XmlElement('Address', array(), $email, 'ns1')
                        , 'ns1'),
                    new XmlElement('PhoneContact', array(),
                        new XmlElement('Number', array('CountryCode' => '7', 'AreaCode' => $phoneAreaCode), $phoneNumber, 'ns1')
                        , 'ns1')
                ), 'ns1')
            , 'ns1'),

            $FQTVs,

            new XmlElement('Gender', array(), $gender, 'ns1'),

            new XmlElement('PassengerIDInfo', array(),
                new XmlElement('PassengerDocument', array(), array(
                    new XmlElement('Type', array(), 'PP', 'ns1'),
                    new XmlElement('ID', array(), \RK_Core_Helper_String::translit($passenger->getDocNumber()), 'ns1'),
                    new XmlElement('BirthCountry', array(), $passenger->getNationality(), 'ns1'),
                    new XmlElement('DateOfIssue', array(), '2018-01-01', 'ns1'),
                    new XmlElement('DateOfExpiration', array(), $passenger->getDocExpired('Y-m-d')->getValue(), 'ns1'),
                    new XmlElement('CountryOfResidence', array(), $passenger->getNationality(), 'ns1'),
                ), 'ns1')
                , 'ns1')
        ), 'ns1');

    }
}