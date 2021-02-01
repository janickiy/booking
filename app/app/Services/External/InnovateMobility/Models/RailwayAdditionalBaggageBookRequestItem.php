<?php

namespace App\Services\External\InnovateMobility\Models;

class RailwayAdditionalBaggageBookRequestItem extends RequestModel
{
    protected static $validationMessages = [
        'MainServiceReference.required' => 'Не указана ссылка на основную услугу',
        'BaggageRequest.required' => 'Не указано описание запрашиваемого перевоза багажа',
        'Index.required' => 'Не указан номер позиции запроса',
        'Index.numeric' => 'Неверно указан номер позиции запроса',
    ];

    protected static function getValidationRules()
    {
        return [
            'MainServiceReference' => 'required',
            'BaggageRequest' => 'required',
            'Index' => 'required|numeric',
        ];
    }
}