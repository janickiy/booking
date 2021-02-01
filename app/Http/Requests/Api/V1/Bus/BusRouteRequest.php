<?php

namespace App\Http\Requests\Api\V1\Bus;

use App\Http\Requests\Api\ClientApiRequest;

class BusRouteRequest extends ClientApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [
            'Provider.required'         => 'Не передан поставщик',
            'Provider.string'           => 'Не верный формат поставщика',
            'RaceId.required'           => 'Не передан идентификатор рейса',
            'RaceId.string'             => 'Не верный идентификатор рейса',
            'DepartureDate.required'    => 'Не передана дата отправления',
            'DepartureDate.date'        => 'Не верный формат даты',
        ];
    }

    public function rules()
    {
        return [
            'Provider'      => 'required|string',
            'RaceId'        => 'required|string',
            'DepartureDate' => 'required|date',
        ];
    }
}
