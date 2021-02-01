<?php

namespace ReservationKit\src\Modules\Avia\Model\Helper;

class AgreementHelper
{
    /**
     * Правила применения 3D договоров
     *
     * @param \RK_Avia_Entity_Booking|\RK_Avia_Entity_Search_Request $bookingOrSearchRequest
     * @return bool
     */
    public static function isAllow3DAgreement($bookingOrSearchRequest)
    {
        // Правила для S7
        if ($bookingOrSearchRequest->getOperatingCompanyCode() === 'S7') {
            foreach ($bookingOrSearchRequest->getSegments() as $segment) {
                // Не применяем скидку для рейсов S74000-S74999
                if ($segment->getOperationCarrierCode() === 'S7' && (int) $segment->getFlightNumber() >= 4000 && (int) $segment->getFlightNumber() <= 4999)
                {
                    return false;
                }

                // Не применяем скидку, если оперирующий перевозчик не S7 или GH
                if ($segment->getOperationCarrierCode() !== 'S7' && $segment->getOperationCarrierCode() !== 'GH') {
                    return false;
                }

                // Не применяем скидку для тарифов FLEX AFLOW1/AFLRT1, AFLOW/AFLRT
                if (preg_match('/AFLOW|AFLRT/', $segment->getFareCode())) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function issetAuto3DAgreement($carrier, $userId = null)
    {
        global $USER;

        //
        if (is_null($userId)) {
            $userId = $USER['id'];
        }

        if (isset($userId)) {
            $agreements = \User::getParameter('[avia][three_power_agreements]', $userId);

            // Определяет наличие 3х строноннего договора
            if (isset($agreements) && is_array($agreements)) {
                foreach ($agreements as $agreement) {
                    if (strtoupper($agreement['airline']) === $carrier) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }

    public static function issetTrivago3DAgreement($carrier, $userId = null)
    {
        global $USER;

        //
        if (is_null($userId)) {
            $userId = $USER['id'];
        }

        if (isset($userId)) {
            $agreements = \User::getParameter('[avia][is_use_trivago_3d]', $userId);

            if ($agreements === 'on') {
                $agreements = \User::getParameter('[avia][trivago_3d_agreements]', $userId);

                // Определяет наличие 3х строноннего договора
                if (isset($agreements) && is_array($agreements)) {
                    foreach ($agreements as $agreement) {
                        if (strtoupper($agreement['airline']) === $carrier) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Добавляет 3D договор указанной а/к или маркетинговой из сегментов
     *
     * @param \RK_Avia_Entity_Booking|\RK_Avia_Entity_Search_Request $bookingRK
     * @param string $carrier
     * @return mixed
     */
    public static function add3DAgreement($bookingRK, $carrier = '', $userId = null)
    {
        global $USER;

        // Определение для какой а/к искать 3D договор
        if (empty($carrier)) {
            if ($bookingRK->getSegment(0)) {
                $carrier = $bookingRK->getSegment(0)->getMarketingCarrierCode();
            } else {
                // Не удалось определить а/к для 3D договора
                return false;
            }
        }

        //
        if (is_null($userId)) {
            $userId = $USER['id'];
        }

        // Трехсторонний договор
        $isset3DAgreement        = false;
        $isset3DAgreementTrivago = false;
        if (isset($userId) && SearchRequestOrBookingChecker::isAdultOnly($bookingRK) && AgreementHelper::isAllow3DAgreement($bookingRK))
		{
            $agreements = \User::getParameter('[avia][three_power_agreements]', $userId);

            // Определяет наличие 3х строноннего договора
            if (isset($agreements) && is_array($agreements)) {
                foreach ($agreements as $agreement) {
                    if (strtoupper($agreement['airline']) === $carrier) {
                        $isset3DAgreement = true;
                    }
                }
            }

            // Если нет 3х стороннего договора, то нужна установка 3х стороннего договора Таларии
            if (!$isset3DAgreement) {
                $agreements = \User::getParameter('[avia][is_use_trivago_3d]', $userId);

                if ($agreements === 'on') {
                    $agreements = \User::getParameter('[avia][trivago_3d_agreements]', $userId);

                    // Определяет наличие 3х строноннего договора
                    if (isset($agreements) && is_array($agreements)) {
                        foreach ($agreements as $agreement) {
                            if (strtoupper($agreement['airline']) === $carrier) {
                                $isset3DAgreementTrivago = true;
                            }
                        }
                    }
                }
            }

            if ($isset3DAgreement || $isset3DAgreementTrivago) {
                $triPartyAgreements = ToRkConverter::getRkTriPartyAgreements($agreements, $bookingRK->getClassType());
                $triPartyAgreement = SearchRequestOrBookingChecker::getValidTriPartyAgreement($bookingRK, $triPartyAgreements);
                if ($triPartyAgreement) {
                    if ($triPartyAgreement->getCarrier() === 'S7' && $bookingRK->getSystem() === SYSTEM_NAME_S7AGENT) {
                        $triPartyAgreement->setAccountCode('692');
                    }
                    $bookingRK->addTriPartyAgreement($triPartyAgreement);
                    return true;
                }
            }
        }

        return false;
    }
}