<?php

namespace App\Http\Requests\Api\V1\Bus\Reservation;

use App\Http\Requests\Api\ClientApiRequest;

class AutoReturnRequest extends ClientApiRequest
{
    public function messages()
    {
        return [
            'OrderItemId.required'  => 'Не передан номер заказа',
            'OrderItemId.integer'   => 'Не верный формат заказа',
        ];
    }

    public function rules()
    {
        return [
            'OrderItemId'   => 'required|integer',
        ];
    }
}
