<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 11.05.2018
 * Time: 10:24
 */

namespace App\Services\External\InnovateMobility\Models;

use App\Helpers\LangHelper;


/**
 * Class RailwayReservationRequest
 * @package App\Services\External\InnovateMobility\Models
 */
class RailwayReservationRequest extends RequestModel
{
    /**
     * {@inheritDoc}
     */
    protected static $type = 'ApiContracts.Railway.V1.Messages.Reservation.RailwayReservationRequest, ApiContracts';

    /**
     * {@inheritDoc}
     */
    protected static $validationMessages = [
        'OriginCode' => 'Не указан пунк отпрвления',
        'DestinationCode' => 'Не указан пункт назначения',
        'DepartureDate.required' => 'Не указана дата отправления',
        'DepartureDate.date_format' => 'Неверный формат даты отправления',
        'DepartureDate.min' => 'Дата отправления указана неверно',
        'TrainNumber' => 'Не выбран поезд',
        'CarType.required' => 'Не указан тип вагона',
        'CarType.in' => 'Указан неверный тип вагона',
        'Passengers.required' => 'Не внесено ни одного пассажира',
        'Passengers.array' => 'Список пассажиров не соответвует формату',
        'Index' => 'Отсутвует указатель'
    ];

    /**
     * {@inheritDoc}
     */
    protected static function getValidationRules()
    {
        return [
            'OriginCode' => 'required',
            'DestinationCode' => 'required',
            'DepartureDate' => 'required|date_format:Y-m-d\TH:i:s',
            'TrainNumber' => 'required',
            'CarType' => 'required|in:'.implode(',',array_keys(LangHelper::trans('references/im.carTypes'))),
            'Passengers' => 'required|array',
            'Index' => 'required|numeric'
        ];
    }
}