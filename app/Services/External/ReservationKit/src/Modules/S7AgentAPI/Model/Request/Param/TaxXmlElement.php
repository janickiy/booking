<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Avia\Model\Entity\Tax;

class TaxXmlElement extends XmlElement
{
    /**
     * @param Tax $tax
     */
    public function __construct(Tax $tax)
    {
        parent::__construct('Tax', array(), array(
            new XmlElement('Amount', array('Code' => $tax->getAmount()->getCurrency()), $tax->getAmount()->getAmount('VAL'), 'ns1'),
            new XmlElement('TaxCode', array(), $tax->getCode(), 'ns1')
        ), 'ns1');
    }
}