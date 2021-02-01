<?php

namespace App\Http\Requests\Api\V1\Bus\Reservation;

use App\Http\Requests\Api\ClientApiRequest;

class CreateRequest extends ClientApiRequest
{
    public function messages()
    {
        return [
            'Customers.required'                            => 'Не указаны покупатели',
            'Customers.array'                               => 'Не указаны покупатели',
            'Customers.*.DocumentNumber.required'           => 'Не указан номер документа',
            'Customers.*.DocumentType.required'             => 'Не указан тип документа',
            'Customers.*.DocumentType.in'                   => 'Неверно указан тип документа',
            'Customers.*.FirstName'                         => 'Не указано имя',
            'Customers.*.LastName'                          => 'Не указана фамилия',
            'Customers.*.Sex.required'                      => 'Не указан пол',
            'Customers.*.Sex.in'                            => 'Неверно указан пол',
            'ReservationItems.required'                     => 'Не указано резервирование',
            'ReservationItems.array'                        => 'Не указано резервирование',
            'ReservationItems.*.OriginCode.required'        => 'Не указан OriginCode',
            'ReservationItems.*.OriginCode.string'          => 'Не верный формат TariffId',
            'ReservationItems.*.DepartureDate.required'     => 'Не указан DepartureDate',
            'ReservationItems.*.DepartureDate.array'        => 'Не верный формат DepartureDate',
            'ReservationItems.*.Passengers.required'        => 'Не указаны пассажиры для бронирования',
            'ReservationItems.*.Passengers.array'           => 'Не верный формат пассажиров',
            'ReservationItems.*.Index.required'             => 'Не указан Индекс позиции',
            'ReservationItems.*.Index.array'                => 'Не верный формат Индекс позиции',
            'ReservationItems.*.DestinationCode.required'   => 'Не указан Код станции назначения',
            'ReservationItems.*.DestinationCode.array'      => 'Не верный формат Кода станции назначения',
            'ReservationItems.*.TrainNumber.required'       => 'Не указан Номер поезда',
            'ReservationItems.*.TrainNumber.array'          => 'Не верный формат Номера поезда',
        ];
    }

    public function rules()
    {
        return [
            'Customers'                             => 'required|array',
            'Customers.*.DocumentNumber'            => 'required',
            'Customers.*.DocumentType'              => 'required|in:'.implode(',',array_keys(trans('references/im.documentType'))),
            'Customers.*.FirstName'                 => 'required',
            'Customers.*.LastName'                  => 'required',
            'Customers.*.Sex'                       => 'required|in:'.implode(',',array_keys(trans('references/im.sex'))),
            'Customers.*.Index'                     => 'required|numeric',
            'ReservationItems'                      => 'required|array',
            'ReservationItems.*.OriginCode'         => 'required|string',
            'ReservationItems.*.DepartureDate'      => 'required|date',
            'ReservationItems.*.DestinationCode'    => 'required|array',
            'ReservationItems.*.TrainNumber'        => 'required|integer',
            'ReservationItems.*.CarType'            => 'required|in:'.implode(',',array_keys(trans('references/im.carTypes'))),
            'ReservationItems.*.Passengers'         => 'required|array',
            'ReservationItems.*.Index'              => 'required|integer',
        ];
    }
}
