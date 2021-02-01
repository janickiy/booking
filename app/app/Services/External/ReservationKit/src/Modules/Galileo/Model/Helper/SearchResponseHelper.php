<?php

namespace ReservationKit\src\Modules\Galileo\Model\Helper;

use ReservationKit\src\Modules\Galileo\Model\Entity\Booking as GalileoBooking;
use ReservationKit\src\Modules\Galileo\Model\RequisiteRules as GalileoRequisiteRules;

class SearchResponseHelper
{
    /**
     * Группировка результатов поиска по PCC
     *
     * @param \RK_Avia_Entity_Booking[] $offers Список предложений
     * @return \RK_Avia_Entity_Booking[]|[]
     */
    public static function groupOffersByPCC($offers)
    {
        $groupGalileoOffers = [];
        $remainingOffers = [];

        foreach ($offers as $offer) {
            if ($offer instanceof GalileoBooking && $offer->getRequisiteRules() instanceof GalileoRequisiteRules) {
                $groupGalileoOffers[$offer->getRequisiteRules()->getSearchPCC()][] = $offer;
            } else {
                $remainingOffers[] = $offer;
            }
        }

        return [$groupGalileoOffers, $remainingOffers];
    }

    public static function excludeSimilarOffers($offers)
    {
        $offers = self::excludeSimilarOffersByPCC($offers);

        return $offers;
    }

    /**
     * TODO сделать систему приоритетов на основе приоритета реквизитов. Добавить новое поле "priority" в таблицу реквизитов
     *
     * Удаляет дубликаты предложений на основании преоритета WAB'ов
     *
     * @param \RK_Avia_Entity_Booking[] $offers Список предложений
     * @return \RK_Avia_Entity_Booking[]|[]
     */
    public static function excludeSimilarOffersByPCC($offers)
    {
        $results = array();

        if (isset($offers)) {
            // Группировка результатов поиска Galileo по PCC
            list($groupGalileoOffers, $remainingOffers) = self::groupOffersByPCC($offers);

            // Тупой, но понятный фильтр одинаковых предложений

            /* @var \RK_Avia_Entity_Booking $offer36WB */
            /* @var \RK_Avia_Entity_Booking $offer6UQ2 */
            /* @var \RK_Avia_Entity_Booking $offerL8W */
            /* @var \RK_Avia_Entity_Booking $offer33VU */
            /* @var \RK_Avia_Entity_Booking $offer80UE */

            // Предложения из '36WB' заменяют такие же предложения в '6UQ2', 'L8W' и '33VU'
            if (isset($groupGalileoOffers['36WB'])) {
                foreach ($groupGalileoOffers['36WB'] as $offer36WB) {

                    if (isset($groupGalileoOffers['6UQ2'])) {
                        foreach ($groupGalileoOffers['6UQ2'] as $key6UQ2 => $offer6UQ2) {
                            if ($offer36WB->isEqualOffer($offer6UQ2)) {
                                unset($groupGalileoOffers['6UQ2'][$key6UQ2]);
                            }
                        }
                    }

                    if (isset($groupGalileoOffers['L8W'])) {
                        foreach ($groupGalileoOffers['L8W'] as $keyL8W => $offerL8W) {
                            if ($offer36WB->isEqualOffer($offerL8W)) {
                                unset($groupGalileoOffers['L8W'][$keyL8W]);
                            }
                        }
                    }

                    if (isset($groupGalileoOffers['33VU'])) {
                        foreach ($groupGalileoOffers['33VU'] as $key33VU => $offer33VU) {
                            if ($offer36WB->isEqualOffer($offer33VU)) {
                                unset($groupGalileoOffers['33VU'][$key33VU]);
                            }
                        }
                    }

                    if (isset($groupGalileoOffers['80UE'])) {
                        foreach ($groupGalileoOffers['80UE'] as $key80UE => $offer80UE) {
                            if ($offer36WB->isEqualOffer($offer80UE)) {
                                unset($groupGalileoOffers['80UE'][$key80UE]);
                            }
                        }
                    }
                }
            }

            // Предложения из '6UQ2' заменяют такие же предложения в '80UE'
            if (isset($groupGalileoOffers['6UQ2'])) {
                foreach ($groupGalileoOffers['6UQ2'] as $offer6UQ2)
                    if ($groupGalileoOffers['80UE'])
                        foreach ($groupGalileoOffers['80UE'] as $key80UE => $offer80UE) {
                            if ($offer6UQ2->isEqualOffer($offer80UE)) unset($groupGalileoOffers['80UE'][$key80UE]);
                        }
            }

            // Предложения из 'L8W' заменяют такие же предложения в '80UE'
            if (isset($groupGalileoOffers['L8W'])) {
                foreach ($groupGalileoOffers['L8W'] as $offerL8W) {
                    foreach ($groupGalileoOffers['80UE'] as $key80UE => $offer80UE) {
                        if ($offerL8W->isEqualOffer($offer80UE)) {
                            unset($groupGalileoOffers['80UE'][$key80UE]);
                        }
                    }
                }
            }

            /*
            // Предложения из '80UE' заменяют такие же предложения в '33VU'
            if (isset($groupGalileoOffers['80UE'])) {
                foreach ($groupGalileoOffers['80UE'] as $offer80UE) {
                    foreach ($groupGalileoOffers['33VU'] as $key33VU => $offer33VU) {
                        if ($offer80UE->isEqualOffer($offer33VU)) {
                            unset($groupGalileoOffers['33VU'][$key33VU]);
                        }
                    }
                }
            }
            */

            $results = $remainingOffers;

            // Объединение результатов отдельный WAB'ов в единый массив
            foreach ($groupGalileoOffers as $WABName => $WABOffers) {
                if (is_array($WABOffers) && count($WABOffers)) {
                    $results = array_merge($results, $WABOffers);
                }
            }
        }

        return $results;
    }
}