<?php

namespace App\Http\Requests\Api\V1\Bus\Reservation;

use App\Http\Requests\Api\ClientApiRequest;

class ReturnAmountRequest extends ClientApiRequest
{
    public function messages()
    {
        return [

        ];
    }

    public function rules()
    {
        return [
            'CheckDocumentNumber'   => 'required|string',
            'OrderItemId'           => 'required|integer',
        ];
    }
}
