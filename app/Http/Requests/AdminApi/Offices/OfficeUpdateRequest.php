<?php

namespace App\Http\Requests\AdminApi\Offices;

use App\Http\Requests\AdminApi\Request;

class OfficeUpdateRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'titleRu'           => 'required|string',
            'titleEn'           => 'required|string',
            'contact_email'     => 'required||email',
            'delivery_email'    => 'required||email',
            'phone'             => 'required|string|min:7',
            'addressEn'         => 'required|string',
            'addressRu'         => 'required|string',
            'longitude'         => 'string|nullable',
            'latitude'          => 'string|nullable',
            'code'              => "required|string|nullable",
            'fax'               => 'required|string|nullable',
            'cityRu'            => 'required|string|nullable',
            'cityEn'            => 'required|string|nullable',
            'sms_phone'         => 'required|string|min:7',
            'iata_codes'        => 'required|string|nullable',
            'schedule'          => 'required|string|nullable',
        ];
    }

    public function messages()
    {
        return [
            'titleRu.required'      => 'Отсутствует название офиса',
            'titleEn.required'      => 'Отсутствует название офиса',
            'cityRu.required'       => 'Отсутствует город',
            'cityEn.required'       => 'Отсутствует город',
            'sms_phone.required'    => 'Отсутствует номер телефона для смс',
            'phone.required'        => 'Отсутствует телефон',
            'addressEn.required'    => 'Отсутствует адрес',
            'addressRu.required'    => 'Отсутствует адрес',
            'contact_email.email'   => 'Неверный формат email',
            'delivery_email.email'  => 'Неверный формат email',
        ];
    }
}
