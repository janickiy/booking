<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;

class AgentUserSender_QA extends XmlElement
{
    public function __construct($isContact = false)
    {
        $contentAgentUserSender = array(
            new XmlElement('OtherIDs', array(), array(
                new XmlElement('OtherID', array('Description' => 'airlineVendorID'), 'CY', 'ns1'),
                new XmlElement('OtherID', array('Description' => 'airportCode'), 'NYC', 'ns1'),
                new XmlElement('OtherID', array('Description' => 'erspUserID'), 'LSHILOVA/1AGT4U', 'ns1'),
                new XmlElement('OtherID', array('Description' => 'isoCountry'), 'KG', 'ns1'),
                new XmlElement('OtherID', array('Description' => 'requestorID'), '11111111B', 'ns1'),
                new XmlElement('OtherID', array('Description' => 'requestorType'), '7', 'ns1'),
            ), 'ns1'),
            new XmlElement('PseudoCity', array(), 'FRU900', 'ns1'),
            new XmlElement('AgentUserID', array(), '500/5000W', 'ns1'),
            new XmlElement('UserRole', array(), '51', 'ns1'),
        );

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

        parent::__construct('AgentUserSender', array(), $contentAgentUserSender, 'ns1');
    }
}