<?php

namespace App\Services\External\InnovateMobility\Models;

class RailwayAutoReturnRequest extends RequestModel
{
    /**
     * {@inheritDoc}
     */
    protected static $type = 'ApiContracts.Railway.V1.Messages.Return.RailwayAutoReturnRequest, ApiContracts';

    /**
     * {@inheritDoc}
     */
    protected static $validationMessages = [
        'OrderItemBlankIds.array' => 'Указан неверный идентификаторы бланков',
        'CheckDocumentNumber.required' => 'Не указан номер документа для проверки',
        'CheckDocumentNumber.numeric' => 'Неверно указан номер документа для проверки',
        'OrderItemId.required' => 'Не указан идентификатор покупочной позиции в заказе',
        'OrderItemId.numeric' => 'Неверно указан идентификатор покупочной позиции в заказе',
    ];

    /**
     * {@inheritDoc}
     */
    protected static function getValidationRules()
    {
        return [
            'OrderItemId' => 'required|numeric',
            'CheckDocumentNumber' => 'required',
            'OrderItemBlankIds' => 'array|nullable',
        ];
    }
}