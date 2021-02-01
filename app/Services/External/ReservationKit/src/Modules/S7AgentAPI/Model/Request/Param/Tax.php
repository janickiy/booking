<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;

class Tax extends XmlElement
{
    /**
     * @param \RK_Core_Money $amount
     * @param $taxCode
     */
    public function __construct($amount, $taxCode)
    {
        parent::__construct('Tax', array(), array(
            new XmlElement('Amount', array('Code' => $amount->getCurrency()), $amount->getAmount('VAL'), 'ns1'),
            new XmlElement('TaxCode', array(), $taxCode, 'ns1')
        ), 'ns1');
    }
}