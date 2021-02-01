<?php

namespace ReservationKit\src\Modules\Avia\Model\Helper;

class Filter
{
    public static function excludeByCompany(array $companyList, array $searchResults)
    {
        foreach ($searchResults as $system => $results) {
            foreach ($results as $numOffer => $offer) {
                foreach ($offer['segments'] as $numWay => $way) {
                    foreach ($way as $numVariant => $variant) {
                        foreach ($variant as $numSegment => $segment) {
                            if (in_array($segment['airline_operation'], $companyList)) {
                                unset($searchResults[$system][$numOffer]);
                                continue 4;
                            }
                        }
                    }
                }
            }

            // Удаление систем, из которых были удалены все предложения
            if (empty($searchResults[$system])) {
                unset($searchResults[$system]);
            }

            // Сброс ключей
            $searchResults[$system] = array_values($searchResults[$system]);
        }

        return $searchResults;
    }
}