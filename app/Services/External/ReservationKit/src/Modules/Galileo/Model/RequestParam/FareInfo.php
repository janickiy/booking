<?php

namespace ReservationKit\src\Modules\Galileo\Model\RequestParam;

use ReservationKit\src\Modules\Galileo\Model\Entity\Brand;
use ReservationKit\src\Modules\Galileo\Model\Entity\FareInfo as EntityFareInfo;
use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Galileo\Model\Helper\Request;

class FareInfo extends XmlElement
{
    /**
     * @param EntityFareInfo $fareInfo
     */
    public function __construct(EntityFareInfo $fareInfo)
    {
        $attributesFareInfo = array(
            'Key'               => $fareInfo->getKey(),
            'Origin'            => $fareInfo->getDepartureAirportCode(),
            'Destination'       => $fareInfo->getArrivalAirportCode(),
            'EffectiveDate'     => $fareInfo->getEffectiveDate(),           //'2015-08-13T03:05:00.000-07:00',
            'FareBasis'         => $fareInfo->getFareBasis(),
            'PassengerTypeCode' => $fareInfo->getPassengerTypeCode(),
            'Amount'            => $fareInfo->getAmount()->getAmount(),
            'DepartureDate'     => $fareInfo->getDepartureDate(),

            //'PrivateFare' => 'AirlinePrivateFare',
            //'PseudoCityCode' => 'PCC'
        );

        if ($fareInfo->getNotValidBefore()) $attributesFareInfo['NotValidBefore'] = $fareInfo->getNotValidBefore();
        if ($fareInfo->getNotValidAfter()) $attributesFareInfo['NotValidAfter'] = $fareInfo->getNotValidAfter();

        // Тур код
        if ($fareInfo->getTourCode()) $attributesFareInfo['TourCode'] = $fareInfo->getTourCode();

        $attributesFareRuleKey = array('FareInfoRef' => $fareInfo->getKey(), 'ProviderCode' => '1G');

        $contentFareInfo = array();
        $contentFareInfo[] = new XmlElement('FareRuleKey', $attributesFareRuleKey, $fareInfo->getRuleKey(), 'air');

        $Brand = $fareInfo->getBrand();

        if ($Brand instanceof Brand) {
            $attributesBrand = array(
                'Key'     => $fareInfo->getKey(),
                'BrandID' => $Brand->getBrandID(),
                'Name'    => $Brand->getName(),
                'Carrier' => $Brand->getCarrier(),
            );

            $contentFareInfo[] = new XmlElement('Brand', $attributesBrand, array_merge(
                Request::getListRequestParam('BrandTitle', $Brand->getTitles()),
                Request::getListRequestParam('BrandText', $Brand->getTexts())
            ), 'air', false);
        }

        $FareInfo = new XmlElement('FareInfo', $attributesFareInfo, $contentFareInfo, 'air');

        parent::__construct(null, array(), $FareInfo);
    }
}