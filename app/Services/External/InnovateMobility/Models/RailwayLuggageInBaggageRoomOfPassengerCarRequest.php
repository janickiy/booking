<?php


namespace App\Services\External\InnovateMobility\Models;

class RailwayLuggageInBaggageRoomOfPassengerCarRequest extends RequestModel
{
    /**
     * {@inheritDoc}
     */
    protected static $type = 'ApiContracts.Railway.V1.AdditionalBaggage.RailwayLuggageInBaggageRoomOfPassengerCarRequest, ApiContracts';

    /**
     * {@inheritDoc}
     */
    protected static $validationMessages = [
        'BaggageName.required' => 'Отсутвует описание багажа',
        'BaggagePlaceQuantity.required' => 'Отсутвует количество мест',
        'BaggagePlaceQuantity.digits_between' => 'Количество мест должно быть от 1 до 3 включительно',
        'BaggageRoomCarNumber.required' => 'Отсутвует номер вагона',
        'BaggageDeclaredValue.required' => 'Отсутвует объявленная стоимость',

    ];

    /**
     * {@inheritDoc}
     */
    protected static function getValidationRules()
    {
        return [
            'BaggageName' => 'required',
            'BaggagePlaceQuantity' => 'required|digits_between:1,3',
            'BaggageRoomCarNumber' => 'required',
            'BaggageDeclaredValue' => 'required|digits_between:1,999999',
        ];
    }
}