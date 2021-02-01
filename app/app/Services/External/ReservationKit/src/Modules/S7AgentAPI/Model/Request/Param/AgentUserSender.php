<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;

class AgentUserSender extends XmlElement
{
    public function __construct($isContact = false, $isSitaPOS = false, $version = '0.50')
    {
        if ($version === '0.50') {
            $contentAgentUserSender = array(
                new XmlElement('OtherIDs', array(), array(
                    new XmlElement('OtherID', array('Description' => 'airlineVendorID'), 'S7', 'ns1'),
                    new XmlElement('OtherID', array('Description' => 'airportCode'), 'MOW', 'ns1'),
                    new XmlElement('OtherID', array('Description' => 'erspUserID'), 'SWSALL/1SWS1MW', 'ns1'),
                    new XmlElement('OtherID', array('Description' => 'isoCountry'), 'RU', 'ns1'),
                    new XmlElement('OtherID', array('Description' => 'requestorID'), '42192986', 'ns1'),
                    new XmlElement('OtherID', array('Description' => 'requestorType'), '1', 'ns1'),
                ), 'ns1'),
                new XmlElement('PseudoCity', array(), 'MOW963', 'ns1'),
                new XmlElement('AgentUserID', array(), '8134/8134A', 'ns1'),
                new XmlElement('UserRole', array(), '115', 'ns1'),
            );

            if ($isSitaPOS) {
                array_unshift($contentAgentUserSender, array(
                    new XmlElement('Name', array(), 'SITA', 'ns1'),
                    new XmlElement('Type', array(), 'SitaPOS', 'ns1'),
                ));
            }

            if ($isContact) {
                array_unshift($contentAgentUserSender, new XmlElement('Contacts', array(), array(
                    new XmlElement('Contact', array(), array(
                        new XmlElement('PhoneContact', array(), array(
                            new XmlElement('Application', array(), 'AGENT NAME', 'ns1'),
                            new XmlElement('Number', array('CountryCode' => '007', 'AreaCode' => '903'), '7777777', 'ns1'),
                        ), 'ns1')
                    ), 'ns1')
                ), 'ns1'));
            }

        } else if ($version === '0.52') {
            $contentAgentUserSender = array();
            $contentAgentUserSender[] = new XmlElement('Name', array(), 'S7-AIDL', 'ns1');

            if ($isContact) {
                $contentAgentUserSender[] =
                    new XmlElement('Contacts', array(), array(
                        new XmlElement('Contact', array(), array(
                            new XmlElement('PhoneContact', array(), array(
                                new XmlElement('Application', array(), 'PROD BOOK', 'ns1'),
                                new XmlElement('Number', array('CountryCode' => '007', 'AreaCode' => '999'), '8783424', 'ns1'),
                            ), 'ns1')
                        ), 'ns1')
                    ), 'ns1');
            }

            $contentAgentUserSender[] =
                new XmlElement('OtherIDs', array(), array(
                    new XmlElement('OtherID', array('Description' => 'POS_Type'), '1', 'ns1'),
                    new XmlElement('OtherID', array('Description' => 'requestorType'), 'U', 'ns1'),
                    //new XmlElement('OtherID', array('Description' => 'Password'), 'Amadeus01', 'ns1'),
                ), 'ns1');
            $contentAgentUserSender[] = new XmlElement('PseudoCity', array(), 'MOWS728CC', 'ns1');
            $contentAgentUserSender[] = new XmlElement('AgentUserID', array(), 'ID', 'ns1');
            $contentAgentUserSender[] = new XmlElement('UserRole', array(), 'AS', 'ns1');
        }

        parent::__construct('AgentUserSender', array(), $contentAgentUserSender, 'ns1');
    }
}