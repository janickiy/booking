<?php

namespace App\Services\External\Payture\Models;

class ChequeRequest extends RequestModel
{
    /**
     * {@inheritDoc}
     */
    protected static $validationMessages = [
        'Positions.array' => 'Неверно указан Список позиций чека',
    ];

    /**
     * {@inheritDoc}
     */
    protected static function getValidationRules()
    {
        return [
            'Positions' => 'array|nullable',
            'Message' => 'max:50',
            'AdditionalMessages' => 'array'
        ];
    }
}