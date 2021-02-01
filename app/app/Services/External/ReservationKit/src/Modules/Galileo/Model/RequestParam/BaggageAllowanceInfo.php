<?php

namespace ReservationKit\src\Modules\Galileo\Model\RequestParam;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Galileo\Model\Entity\BaggageAllowanceInfo as GalileoBaggageAllowanceInfo;

class BaggageAllowanceInfo extends XmlElement
{
    /**
     * @param GalileoBaggageAllowanceInfo $baggageAllowanceInfo
     */
    public function __construct(GalileoBaggageAllowanceInfo $baggageAllowanceInfo)
    {
        $attributesBaggageAllowanceInfo = array(
            'Origin'      => $baggageAllowanceInfo->getOrigin(),
            'Destination' => $baggageAllowanceInfo->getDestination(),
            'Carrier'     => $baggageAllowanceInfo->getCarrier(),
        );

        $Texts = array();
        foreach ($baggageAllowanceInfo->getTextInfo() as $textInfo) {
            $Texts[] = new XmlElement('Text', array(), $textInfo, 'air');
        }

        $TextInfo = new XmlElement('TextInfo', array(), $Texts, 'air');

        $BaggageAllowanceInfo = new XmlElement('BaggageAllowanceInfo', $attributesBaggageAllowanceInfo, $TextInfo, 'air');

        parent::__construct(null, array(), $BaggageAllowanceInfo);
    }
}