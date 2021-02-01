<?php


namespace App\Services\External\InnovateMobility\Models;

class RailwayMainServiceOrderItemReference extends RequestModel
{
    /**
     * {@inheritDoc}
     */
    protected static $type = 'ApiContracts.Railway.V1.Common.RailwayMainServiceOrderItemReference, ApiContracts';

    /**
     * {@inheritDoc}
     */
    protected static $validationMessages = [
        'OrderItemId.required' => 'Отсутсвует идентификатор позиции в заказе поездки',
        'OrderItemId.numeric' => 'Неверно указан идентификатор позиции в заказе поездки',

    ];

    /**
     * {@inheritDoc}
     */
    protected static function getValidationRules()
    {
        return [
            'OrderItemId' => 'required|numeric',
        ];
    }
}