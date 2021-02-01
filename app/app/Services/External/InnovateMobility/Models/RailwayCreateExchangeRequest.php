<?php

namespace App\Services\External\InnovateMobility\Models;

use App\Helpers\LangHelper;

class RailwayCreateExchangeRequest extends RequestModel
{
    /**
     * {@inheritDoc}
     */
    protected static $type = 'ApiContracts.Railway.V1.Messages.Reservation.RailwayCreateExchangeRequest, ApiContracts';

    /**
     * {@inheritDoc}
     */
    protected static $validationMessages = [
        'DepartureDate.required' => 'Не указана дата отправления',
        'DepartureDate.date_format' => 'Неверно указан формат дата отправления',
        'TrainNumber.required' => 'Не указан номер поезда',
        'CarType.required' => 'Не указан тип вагона',
        'OrderItemBlankId.required' => 'Не указан переоформляемый бланк',
        'OrderItemBlankId.numeric' => 'Не указан переоформляемый бланк',
        'LowerPlaceQuantity.numeric' => 'Неверно указано количество нижних мест',
        'UpperPlaceQuantity.numeric' => 'Неверно указано количество верхних мест',
        'OrderItemId.required' => 'Не указан идентификатор возвратной позиции',
        'OrderItemId.numeric' => 'Неверно указан идентификатор возвратной позиции',
        'Index.required' => 'Не указан индекс позиции',
        'CabinGenderKind.in' => 'Неверно указан гендерный признак купе',
        'CarStorey.id' => 'Неверно указан этаж вагона',
        'PlaceRange.id' => 'Неверно указан диапазон мест',
        'CabinPlaceDemands.in' => 'Неверно указаны дополнительные требования к местам',
        'ProviderPaymentForm.in' => 'Неверно указаны форма оплаты',
        'Index.numeric' => 'Не указан индекс позиции',
    ];

    /**
     * {@inheritDoc}
     */
    protected static function getValidationRules()
    {
        return [
            'DepartureDate' => 'required|date_format:Y-m-d\TH:i:s',
            'TrainNumber' => 'required',
            'CarType' => 'required|in:'.implode(',',array_keys(LangHelper::trans('references/im.carTypes'))),
            'OrderItemBlankId' => 'required|numeric',
            'CabinGenderKind' => 'in:'.implode(',',array_keys(LangHelper::trans('references/im.cabinGenderKind'))),
            'CarStorey' => 'in:'.implode(',',array_keys(LangHelper::trans('references/im.carStorey'))),
            'PlaceRange' => 'in:'.implode(',',array_keys(LangHelper::trans('references/im.placeRange'))),
            'CabinPlaceDemands.in' => 'in:'.implode(',',array_keys(LangHelper::trans('references/im.cabinPlaceDemands'))),
            'ProviderPaymentForm.in' => 'in:'.implode(',',array_keys(LangHelper::trans('references/im.providerPaymentForm'))),
        ];
    }
}