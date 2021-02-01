<?php

namespace ReservationKit\src\Modules\Avia\Model\Helper;

use ReservationKit\src\RK;

class FareFamilies
{
    public static function getInfo($infoKey, $aviaCompanyCode, $fareCode)
    {
        $fareFamiliesPath     = RK_ROOT_PATH . '/Modules/Avia/Source/FareFamilies/';    // TODO сделать получение пути через RK
        $fareFamiliesFileName = $aviaCompanyCode . '.json';

        if (!file_exists($fareFamiliesPath . $fareFamiliesFileName)) {
            return null;
        }

        $result = array();

        $fareFamiliesContent = file_get_contents($fareFamiliesPath . $fareFamiliesFileName);
        $fareFamiliesJson = json_decode($fareFamiliesContent, true);

        //pr($fareFamiliesJson);

        //$iteratorArray = new \RecursiveArrayIterator($fareFamiliesJson);
        //$iteratorMode  = \RecursiveIteratorIterator::SELF_FIRST;

        // TODO парсить параметры через set объект
        foreach ($fareFamiliesJson as $fareFamily) {
            if (preg_match('/' . $fareFamily['tariffCodePattern'] . '/', $fareCode)) {
                if ($infoKey === 'baseClass') {
                    return $fareFamily['baseClass'];
                }

                foreach ($fareFamily['parameters'] as $parameter) {
                    if ('description' === $infoKey) {
                        return $parameter['shortDescription'][strtolower(RK::getContainer()->getAppLanguage())];
                    }

                    if ($parameter['code'] === $infoKey) {
                        return $parameter['needToPay'];
                    }
                }

                break;
            }
        }

        return null;
    }

    public static function isRefundable()
    {

    }

    public static function getRefundable($aviaCompanyCode, $fareCode)
    {
        return self::getInfo('refundable', $aviaCompanyCode, $fareCode);
    }

    public static function getBaggage($aviaCompanyCode, $fareCode)
    {
        return self::getInfo('baggage', $aviaCompanyCode, $fareCode);
    }
}