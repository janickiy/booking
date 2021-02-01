<?php

namespace ReservationKit\src\Modules\Galileo\Model\Response\Parser;

use ReservationKit\src\Modules\Galileo\Model\Entity\FareInfo as GalileoFareInfo;

class FareInfoParser
{
    public static function parse($FareInfoXml)
    {
        $fareInfo = new GalileoFareInfo();

        // Ключ-идентификатор
        $fareInfo->setKey((string) $FareInfoXml['Key']);

        // Код тарифа
        $fareInfo->setFareCode((string) $FareInfoXml['FareBasis']);

        // Багаж (по количеству)
        if (isset($FareInfoXml->BaggageAllowance->NumberOfPieces)) {
            $NumberOfPieces = (string) $FareInfoXml->BaggageAllowance->NumberOfPieces;
            $fareInfo->setBaggageAllowance($NumberOfPieces . 'PC');
        }

        // Багаж (по весу)
        if (isset($FareInfoXml->BaggageAllowance->MaxWeight,
            $FareInfoXml->BaggageAllowance->MaxWeight['Value'],
            $FareInfoXml->BaggageAllowance->MaxWeight['Unit'])) {
            $Value = (string) $FareInfoXml->BaggageAllowance->MaxWeight['Value'];
            $Unit  = (string) $FareInfoXml->BaggageAllowance->MaxWeight['Unit'];

            $fareInfo->setBaggageAllowance($Value . str_replace(array('Kilograms'), array('K'), $Unit));
        }

        // Тур-код
        if (isset($FareInfoXml['TourCode'])) {
            $fareInfo->setTourCode((string) $FareInfoXml['TourCode']);
        }

        // Ключи для правил
        $fareInfo->setRuleKey((string) $FareInfoXml->FareRuleKey);

        // Указатель скидки
        $fareInfo->setFareTicketDesignator((string) $FareInfoXml->FareTicketDesignator['Value']);

        // Информация о бренде
        if (isset($FareInfoXml->Brand, $FareInfoXml->Brand['BrandID'], $BrandList)) {
            $BrandID = (string) $FareInfoXml->Brand['BrandID'];
            $fareInfo->setBrand($BrandList[$BrandID]);
        }

        return $fareInfo;
    }
}