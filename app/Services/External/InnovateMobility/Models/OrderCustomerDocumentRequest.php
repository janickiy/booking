<?php

namespace App\Services\External\InnovateMobility\Models;

class OrderCustomerDocumentRequest extends RequestModel
{
    protected static $validationMessages = [
        'OrderCustomerId.numeric' => 'Не указан идентификатор пользователя в заказе',
        'DocumentValidTill.date_format' => 'Неверно указан срок действия документа',
    ];

    protected static function getValidationRules()
    {
        return [
            'DocumentNumber' => 'required',
            'DocumentValidTill' => 'required|date_format:Y-m-d\TH:i:s',
        ];
    }
}