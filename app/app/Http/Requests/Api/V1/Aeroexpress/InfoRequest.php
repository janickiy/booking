<?php

namespace App\Http\Requests\Api\V1\Aeroexpress;

use App\Http\Requests\Api\ClientApiRequest;

class InfoRequest extends ClientApiRequest
{
    public function messages()
    {
        return [
            'departureDate.required'        => 'Не передана дата отправления',
            'departureDate.date'            => 'Не верный формат даты',
            'tariffId.required'             => 'Не указан номер тарифа',
            'tariffId.string'               => 'Укзан некорректный формат тарифа',
            'raceId.required'               => 'Укзан некорректный номер рейса',
        ];
    }

    public function rules()
    {
        return [
            'departureDate'   => 'required|date',
            'tariffId'        => 'required|integer',
            'raceId'          => 'string|nullable'
        ];
    }
}
