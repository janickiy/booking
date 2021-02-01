<?php

namespace App\Http\Requests\Api\V1\Bus;

use App\Http\Requests\Api\ClientApiRequest;

class RacePricingRequest extends ClientApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [
            'OriginCode.required'       => 'Не передана дата отправления',
            'OriginCode.string'         => 'Не верный формат даты',
            'DestinationCode.required'  => 'Не передана дата отправления',
            'DestinationCode.string'    => 'Не верный формат даты',
            'DepartureDate.required'    => 'Не передана дата отправления',
            'DepartureDate.date'        => 'Не верный формат даты',
        ];
    }

    public function rules()
    {
        return [
            'OriginCode'        => 'required|string',
            'DestinationCode'   => 'required|string',
            'DepartureDate'     => 'required|date',
        ];
    }
}
