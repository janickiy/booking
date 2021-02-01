<?php

namespace App\Helpers;

/**
 * Хелпер для работы с 3D-договорами
 */
class TriPartyAgreementsHelper
{
    public static function getAgreementBy()
    {
        $agreements = [];

        $agreementsTrivago = config('agreements.trivago');

        // Логика для ручных договоров и с автоматическим расчетом
        // ...

        //$agreements = $agreementsTrivago;

        return $agreements;
    }
}