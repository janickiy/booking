<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Segment;

class FareComponent extends XmlElement
{
    /**
     * @param Segment $segment
     * @param null $segmentNum
     */
    public function __construct(Segment $segment, $segmentNum = null)
    {
        parent::__construct('FareComponent', array('refs' => 'FL' . ($segmentNum + 1)),
            new XmlElement('FareBasis', array(),
                array(

                    new XmlElement('FareBasisCode', array(), array(
                        new XmlElement('Code', array(), $segment->getFareCode() /* . 'BSOW' /*$segment->getFareCode()*/, 'ns1'),
                        //new XmlElement('Code', array(), $segment->getSubClass() . 'FLOW' /*$segment->getFareCode()*/, 'ns1'),
                    ), 'ns1'),

                    new XmlElement('RBD', array(), $segment->getSubClass(), 'ns1')
                )
            , 'ns1')
        , 'ns1');
    }
}