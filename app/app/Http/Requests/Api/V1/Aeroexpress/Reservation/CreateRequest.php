<?php

namespace App\Http\Requests\Api\V1\Aeroexpress\Reservation;

use App\Http\Requests\Api\ClientApiRequest;
//use App\Services\External\InnovateMobility\StaticReferences;

class CreateRequest extends ClientApiRequest
{
    public function messages()
    {
        return [
            'Customers.required'                        => 'Не указаны покупатели',
            'Customers.array'                           => 'Не указаны покупатели',
            'Customers.*.DocumentNumber.required'       => 'Не указан номер документа',
            'Customers.*.DocumentType.required'         => 'Не указан тип документа',
            'Customers.*.DocumentType.in'               => 'Неверно указан тип документа',
            'Customers.*.FirstName'                     => 'Не указано имя',
            'Customers.*.LastName'                      => 'Не указана фамилия',
            'Customers.*.Sex.required'                  => 'Не указан пол',
            'Customers.*.Sex.in'                        => 'Неверно указан пол',
            'ReservationItems.required'                 => 'Не указано резервирование',
            'ReservationItems.array'                    => 'Не указано резервирование',
            'ReservationItems.*.TariffId.required'      => 'Не указан TariffId',
            'ReservationItems.*.TariffId.string'        => 'Не верный формат TariffId',
            'ReservationItems.*.DepartureDate.required' => 'Не указан DepartureDate',
            'ReservationItems.*.DepartureDate.array'    => 'Не верный формат DepartureDate',
            'ReservationItems.*.Passengers.required'    => 'Не указаны пассажиры для бронирования',
            'ReservationItems.*.Passengers.array'       => 'Не верный формат пассажиров',
            'ReservationItems.*.Index.required'         => 'Не указан Индекс позиции',
            'ReservationItems.*.Index.array'            => 'Не верный формат Индекс позиции',
        ];
    }

    public function rules()
    {
        return [
            'Customers'                         => 'required|array',
            'Customers.*.DocumentNumber'        => 'required',
            'Customers.*.DocumentType'          => 'required|in:'.implode(',',array_keys(trans('references/im.documentType'))),
            'Customers.*.FirstName'             => 'required',
            'Customers.*.LastName'              => 'required',
            'Customers.*.Sex'                   => 'required|in:'.implode(',',array_keys(trans('references/im.sex'))),
            'Customers.*.Index'                 => 'required|numeric',
            'ReservationItems'                  => 'required|array',
            'ReservationItems.*.TariffId'       => 'required|string',
            'ReservationItems.*.DepartureDate'  => 'required|date',
            'ReservationItems.*.Passengers'     => 'required|array',
            'ReservationItems.*.Index'          => 'required|integer',
        ];
    }
}
