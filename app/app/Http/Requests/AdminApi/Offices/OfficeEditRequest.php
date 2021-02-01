<?php

namespace App\Http\Requests\AdminApi\Offices;

use App\Http\Requests\AdminApi\Request;

class OfficeEditRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'required|integer|exists:office,id',
        ];
    }
}
