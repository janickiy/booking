<?php

namespace ReservationKit\src\Modules\Galileo\Model;

use ReservationKit\src\Modules\Core\Model\Enum\ConditionEnum;

class RequisiteChecker
{
    public static function isApplyRule($requisiteRule, $searchRequest)
    {
        $result = true;
        $result = $result && self::checkCarriers($requisiteRule, $searchRequest);
        $result = $result && self::checkNeed3DAgreements($requisiteRule, $searchRequest);
        $result = $result && self::checkCountryDeparture($requisiteRule, $searchRequest);

        return $result;
    }

    public static function checkPCC()
    {

    }

    public static function checkCarriers(RequisiteRules $requisiteRule, \RK_Avia_Entity_Search_Request $searchRequest)
    {
        // Проверяется - есть ли запрашиваемые перевозчики в списке разрешенных для поиска
        if ($requisiteRule->isExistSearchRuleField('carriers')) {
            $carriersValue = $requisiteRule->getSearchRuleByField('carriers')['value'];
            $carriers = $searchRequest->getCarriers();

            if (!empty($carriers) && !empty($carriersValue)) {
                $intersectCarriers = array_intersect($carriers, $carriersValue);

                if (empty($intersectCarriers)) {
                    return false;
                }
            }
        }

        // Проверяется - все ли запрашиваемые перевозчики запрещены для поиска
        if ($requisiteRule->isExistSearchRuleField('excludeCarriers'))
		{
            $carriersValue = $requisiteRule->getSearchRuleByField('excludeCarriers')['value'];
            $carriers = $searchRequest->getCarriers();

            if (!empty($carriers) && !empty($carriersValue))
			{
                $intersectCarriers = array_intersect($carriers, $carriersValue);
                if ($carriers === $intersectCarriers) return false;
            }
        }

        // Проверяется - все ли запрашиваемые перевозчики из 3Д договоров. Для этих перевозчиков запускаются отдельные поиски (и они не проверяются).
        // Если все, то поиск отбрасывается
        if (!$requisiteRule->isExistConditionForSearchRuleField('need3DAgreement') || $requisiteRule->getSearchRuleByField('need3DAgreement')['condition'] !== ConditionEnum::ONLY) {
            $triPartyAgreements = $searchRequest->getTriPartyAgreements();

            if (!empty($triPartyAgreements)) {
                $agreementCarriers = array();
                $carriers = $searchRequest->getCarriers();

                foreach ($triPartyAgreements as $agreement) {
                    $agreementCarriers[] = $agreement->getCarrier();
                }

                $diffCarriers = array_diff($carriers, $agreementCarriers);

                if (empty($diffCarriers) && !empty($carriers)) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function checkCountryDeparture(RequisiteRules $requisiteRule, \RK_Avia_Entity_Search_Request $searchRequest)
    {
        if ($requisiteRule->isExistSearchRuleField('countryDeparture')) {
            $countryDepartureValue = $requisiteRule->getSearchRuleByField('countryDeparture')['value'];
            $countryDepartureCondition = $requisiteRule->getSearchRuleByField('countryDeparture')['condition'];

            $isContains = in_array($searchRequest->getCountryDeparture(), $countryDepartureValue);

            if ($countryDepartureCondition === ConditionEnum::CONTAINS && !$isContains) {
                return false;
            }

            if ($countryDepartureCondition === ConditionEnum::NOT_CONTAINS && $isContains) {
                return false;
            }
        }

        return true;
    }

    public static function checkNeed3DAgreements(RequisiteRules $requisiteRule, \RK_Avia_Entity_Search_Request $searchRequest)
    {
        if ($requisiteRule->isExistSearchRuleField('need3DAgreement')) {
            $triPartyAgreements = $searchRequest->getTriPartyAgreements();

            // Необходимо наличие 3х стороннего договора для поиска
            if ($requisiteRule->getSearchRuleByField('need3DAgreement')['condition'] === ConditionEnum::ONLY && empty($triPartyAgreements)) {
                return false;
            }

            // Запрещено наличие 3х стороннего договора для поиска
            if ($requisiteRule->getSearchRuleByField('need3DAgreement')['condition'] === ConditionEnum::NONE && !empty($triPartyAgreements)) {
                return false;
            }
        }

        return true;
    }
}