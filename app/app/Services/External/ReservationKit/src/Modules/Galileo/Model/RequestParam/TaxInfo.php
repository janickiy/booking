<?php

namespace ReservationKit\src\Modules\Galileo\Model\RequestParam;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Avia\Model\Entity\Tax;

class TaxInfo extends XmlElement
{
    /**
     * TaxInfo constructor.
     * @param Tax $tax
     */
    public function __construct(Tax $tax)
    {
        $attributesTaxInfo = array(
            'Category' => $tax->getCode(),
            'Amount'   => $tax->getAmount()->getAmount(),
            'Key'      => createBase64UUID()
        );

        $TaxInfo = new XmlElement('TaxInfo', $attributesTaxInfo, array(), 'air');

        parent::__construct(null, array(), $TaxInfo);
    }
}