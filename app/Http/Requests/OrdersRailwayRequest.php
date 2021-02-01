<?php

namespace App\Http\Requests;

class OrdersRailwayRequest extends Request
{
    protected static $validationMessages = [
        'userId.required' => 'Не указано значение :attribute',
        'userId.integer' => 'Значение :attribute должно быть числом',
        'holdingId.integer' => 'Значение :attribute должно быть числом',
        'clientId.integer' => 'Значение :attribute должно быть числом',
        'complexOrderId.integer' => 'Значение :attribute должно быть числом',
        'orderStatus.integer' => 'Значение :attribute должно быть числом',
    ];

    protected static function getValidationRules()
    {
        return [
            'userId' => 'required|integer',
            'holdingId' => 'integer|nullable',
            'clientId' => 'integer|nullable',
            'complexOrderId' => 'integer|nullable',
            'orderStatus' => 'integer',
            'passengersData' => 'required',
            'paymentsData' => 'required',
        ];
    }
}