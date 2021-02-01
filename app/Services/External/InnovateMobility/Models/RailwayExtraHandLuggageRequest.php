<?php


namespace App\Services\External\InnovateMobility\Models;

class RailwayExtraHandLuggageRequest extends RequestModel
{
    /**
     * {@inheritDoc}
     */
    protected static $type = 'ApiContracts.Railway.V1.AdditionalBaggage.RailwayExtraHandLuggageRequest, ApiContracts';

    /**
     * {@inheritDoc}
     */
    protected static $validationMessages = [
        'BaggageName.required' => 'Отсутвует описание багажа',
        'Weight.required' => 'Отсутвует вес',
        'Weight.digits_between' => 'Вес должен указан в килограммах от 1 до 99999 включительно',
        'BaggagePlaceQuantity.required' => 'Отсутвует количество мест',
        'BaggagePlaceQuantity.digits_between' => 'Количество мест должно быть от 1 до 3 включительно',
    ];

    /**
     * {@inheritDoc}
     */
    protected static function getValidationRules()
    {
        return [
            'BaggageName' => 'required',
            'Weight' => 'required|digits_between:1,99999',
            'BaggagePlaceQuantity' => 'required|digits_between:1,3',
        ];
    }
}