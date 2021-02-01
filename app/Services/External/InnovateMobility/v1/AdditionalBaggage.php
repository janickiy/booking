<?php

namespace App\Services\External\InnovateMobility\v1;

use App\Services\External\InnovateMobility\Request;
use App\Services\External\InnovateMobility\Models\RailwayExtraHandLuggageRequest;
use App\Services\External\InnovateMobility\Models\RailwayAnimalTransportationRequest;
use App\Services\External\InnovateMobility\Models\RailwayOversizedLuggageWithSpecialConditionRequest;
use App\Services\External\InnovateMobility\Models\RailwayLuggageInBaggageRoomOfPassengerCarRequest;
use App\Services\External\InnovateMobility\Models\RailwayCarTransportationRequest;

/**
 * Class AdditionalMeal
 * @package App\Services\External\InnovateMobility\v1
 *
 * @method static getPricing(array $options = [], boolean $map = false, array $mapOptions = []) Справка по стоимости перевозке багажа.
 * @method static getBook(array $options = [], boolean $map = false, array $mapOptions = []) Бронирование перевозки багажа
 * @method static getCancel(array $options = [], boolean $map = false, array $mapOptions = []) Отмена бронирования
 * @method static getConfirm(array $options = [], boolean $map = false, array $mapOptions = []) Отмена бронирования
 * @method static getReturn(array $options = [], boolean $map = false, array $mapOptions = []) Отмена оплаченной перевозки багажа
 */
class AdditionalBaggage extends Request
{
    /**
     * {@inheritDoc}
     */
    protected static $basePath = 'Railway/V1/AdditionalBaggage/';

    /**
     * {@inheritDoc}
     */
    protected static $methods = [
        'Pricing', // Справка по стоимости перевозке багажа.
        'Book', // Бронирование перевозки багажа
        'Cancel', // Отмена бронирования
        'Confirm', // Подтверждение брони перевозки багажа
        'Return', // Отмена оплаченной перевозки багажа
    ];

    /**
     * @param $type
     * @param $data
     * @return array
     */
    public static function BaggageRequest($type, $data)
    {
        switch ($type) {
            case 'ApiContracts.Railway.V1.AdditionalBaggage.RailwayExtraHandLuggageRequest, ApiContracts':

                $baggage = new RailwayExtraHandLuggageRequest($data);
                return $baggage->getBody();

                break;

            case 'ApiContracts.Railway.V1.AdditionalBaggage.RailwayAnimalTransportationRequest, ApiContracts':

                $baggage = new RailwayAnimalTransportationRequest($data);
                return $baggage->getBody();

                break;

            case 'ApiContracts.Railway.V1.AdditionalBaggage.RailwayOversizedLuggageWithSpecialConditionRequest, ApiContracts':

                $baggage = new RailwayOversizedLuggageWithSpecialConditionRequest($data);
                return $baggage->getBody();

                break;

            case 'ApiContracts.Railway.V1.AdditionalBaggage.RailwayLuggageInBaggageRoomOfPassengerCarRequest, ApiContracts':

                $baggage = new RailwayLuggageInBaggageRoomOfPassengerCarRequest($data);
                return $baggage->getBody();

                break;

            case 'ApiContracts.Railway.V1.AdditionalBaggage.RailwayCarTransportationRequest, ApiContracts':

                $baggage = new RailwayCarTransportationRequest($data);
                return $baggage->getBody();

                break;
        }
    }
}