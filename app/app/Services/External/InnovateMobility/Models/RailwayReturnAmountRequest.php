<?php

namespace App\Services\External\InnovateMobility\Models;

class RailwayReturnAmountRequest extends RequestModel
{
    /**
     * {@inheritDoc}
     */
    protected static $type = 'ApiContracts.Railway.V1.Messages.Return.RailwayReturnAmountRequest, ApiContracts';

    /**
     * {@inheritDoc}
     */
    protected static $validationMessages = [

        'CheckDocumentNumber.required' => 'Не указан номер документа для проверки',
        'OrderItemBlankIds.array' => 'Указан неверный идентификаторы бланков',
        'OrderItemId.required' => 'Не указан идентификатор покупочной позиции в заказе',
        'OrderItemId.numeric' => 'Не указан идентификатор покупочной позиции в заказе',
    ];

    /**
     * {@inheritDoc}
     */
    protected static function getValidationRules()
    {
        return [
            'OrderItemId' => 'required|numeric',
            'CheckDocumentNumber' => 'required',
            'OrderItemBlankIds' => 'array|nullable'
        ];
    }
}