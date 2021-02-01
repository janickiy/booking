<?php

namespace App\Services\External\Payture\Models;

class PayInfoRequest extends RequestModel
{
    /**
     * {@inheritDoc}
     */
    protected static $validationMessages = [

        'PAN.required' => 'Не указан номер карты',
        'PAN.numeric' => 'Неверно указан номер карты',
        'EMonth.required' => 'Не указан месяц истечения срока действия карты',
        'EMonth.digits' => 'Неверно указан месяц истечения срока действия карты',
        'EYear.required' => 'Не указан год истечения срока действия карты',
        'EYear.digits' => 'Неверно указан год истечения срока действия карты',
        'CardHolder.required' => 'Не указано фамилия и имя держателя карты',
        'SecureCode.numeric' => 'Неверно указан CVC2/CVV2',
        'OrderId.required' => 'Не указан идентификатор платежа в системе ТСП',
        'OrderId.numeric' => 'Указан неверный Идентификатор платежа в системе ТСП',
        'Amount.required' => 'Не указан сумма платежа',
        'Amount.numeric' => 'Неверно указана сумма платежа',
    ];

    /**
     * {@inheritDoc}
     */
    protected static function getValidationRules()
    {
        return [
            'PAN' => 'required',
            'EMonth' => 'required|digits:2',
            'EYear' => 'required|digits:2',
            'CardHolder' => 'required|max:50',
            'SecureCode' => 'numeric|nullable',
            'OrderId' => 'required|max:50',
            'Amount' => 'required|numeric'
        ];
    }
}