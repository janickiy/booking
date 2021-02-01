<?php

namespace App\Http\Requests\Api\V1\Aeroexpress\Reservation;

use App\Http\Requests\Api\ClientApiRequest;

class AutoReturnRequest extends ClientApiRequest
{
    public function messages()
    {
        return [
            'OrderId.required'  => 'Не передан номер заказа',
            'OrderId.integer'   => 'Не верный формат заказа',
        ];
    }

    public function rules()
    {
        return [
            'OrderId'   => 'required|integer',
        ];
    }
}
