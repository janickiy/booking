<?php

namespace App\Http\Requests\Api\V1\Aeroexpress;

use App\Http\Requests\Api\ClientApiRequest;

class SearchRequest extends ClientApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [
            'departureDate.required'        => 'Не передана дата отправления',
            'departureDate.date'            => 'Не верный формат даты',
        ];
    }

    public function rules()
    {
        return [
            'departureDate'   => 'required|date',
        ];
    }
}
