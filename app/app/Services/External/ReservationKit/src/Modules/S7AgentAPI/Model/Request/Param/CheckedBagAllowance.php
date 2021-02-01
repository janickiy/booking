<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\BaggageAllowance;

class CheckedBagAllowance extends XmlElement
{
    /**
     * @param BaggageAllowance $baggageAllowance
     * @param null $segmentNum
     */
    public function __construct(BaggageAllowance $baggageAllowance, $segmentNum = null)
    {
        parent::__construct('CheckedBagAllowance', array('refs' => 'SEG' . ($segmentNum + 1), 'ListKey' => 'BG' . ($segmentNum + 1)/*$baggageAllowance->getKey()*/),
            new XmlElement('AllowanceDescription', array(), array(
                new XmlElement('ApplicableParty', array(), 'Traveller', 'ns1'),

                new XmlElement('Descriptions', array(),
                    new XmlElement('Description', array(),
                        new XmlElement('Text', array(), $baggageAllowance->getBaggageValue(), 'ns1')
                    , 'ns1')
                , 'ns1')
            ), 'ns1')
        , 'ns1');
    }
}