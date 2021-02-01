<?php

namespace App\Http\Requests\Api\V1\Bus\Reservation;

use App\Http\Requests\Api\ClientApiRequest;

class ConfirmRequest extends ClientApiRequest
{
    public function messages()
    {
        return [
            'OrderId.required'  => 'Не указаны покупатели',
            'OrderId.integer'   => 'Не указаны покупатели',
        ];
    }

    public function rules()
    {
        return [
            'OrderId' => 'required|integer',
        ];
    }
}
