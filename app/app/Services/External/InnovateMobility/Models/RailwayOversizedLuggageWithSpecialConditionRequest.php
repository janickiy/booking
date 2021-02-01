<?php

namespace App\Services\External\InnovateMobility\Models;

class RailwayOversizedLuggageWithSpecialConditionRequest extends RequestModel
{
    /**
     * {@inheritDoc}
     */
    protected static $validationMessages = [
        'BaggageName.required' => 'Отсутсвует описание багажа',
        'Weight.digits_between' => 'Вес должен бфть указан в килограммах от 1 до 99999',
        'Weight.required' => 'Отсутсвует вес в килограммах',
        'BaggagePlaceQuantity.required' => 'Отсутсвует количество мест',
        'BaggagePlaceQuantity.digits_between' => 'Количество мест должно быть указано от 1 до 3 включительно',
    ];

    /**
     * {@inheritDoc}
     */
    protected static function getValidationRules()
    {
        return [
            'BaggageName' => 'required',
            'Weight' => 'required|digits_between:1,99999',
            'BaggagePlaceQuantity' => 'required|digits_between:1,3'
        ];
    }
}