<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 22.05.2018
 * Time: 13:54
 */

namespace App\Services\External\InnovateMobility\Models;

use App\Helpers\LangHelper;

class OrderFullCustomerRequest extends RequestModel
{
    protected static $type = 'ApiContracts.Order.V1.Reservation.OrderFullCustomerRequest, ApiContracts';

    protected static $validationMessages = [
        'DocumentNumber.required' => 'Не указан номер документа',
        'DocumentType.required' => 'Не указан тип документа',
        'DocumentType.in' => 'Неверно указан тип документа',
        'FirstName.required' => 'Не указано имя',
        'LastName.required' => 'Не указана фамилия',
        'Sex.required' => 'Не указан пол',
        'Sex.in' => 'Неверно указан пол',
    ];

    protected static function getValidationRules()
    {
        return [
            'DocumentNumber' => 'required',
            'DocumentType' => 'required|in:'.implode(',',array_keys(LangHelper::trans('references/im.documentType'))),
            'FirstName' => 'required',
            'LastName' => 'required',
            'Sex' => 'required|in:'.implode(',',array_keys(LangHelper::trans('references/im.sex'))),
            'Index' => 'required|numeric'
        ];
    }
}