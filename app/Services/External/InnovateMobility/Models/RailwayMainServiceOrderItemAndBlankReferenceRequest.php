<?php


namespace App\Services\External\InnovateMobility\Models;

class RailwayMainServiceOrderItemAndBlankReferenceRequest extends RequestModel
{
    /**
     * {@inheritDoc}
     */
    protected static $type = 'ApiContracts.Railway.V1.Common.RailwayMainServiceOrderItemAndBlankReference, ApiContracts';

    /**
     * {@inheritDoc}
     */
    protected static $validationMessages = [
        'OrderItemBlankId.required' => 'Отсутсвует идентификатор бланка',
        'OrderItemBlankId.numeric' => 'Неверно указан идентификатор бланка',
        'OrderItemId.required' => 'Отсутсвует идентификатор позиции в заказе поездки',
        'OrderItemId.numeric' => 'Неверно указан идентификатор позиции в заказе поездки',
    ];

    /**
     * {@inheritDoc}
     */
    protected static function getValidationRules()
    {
        return [
            'OrderItemBlankId' => 'required|numeric',
            'OrderItemId' => 'required|numeric',
        ];
    }
}