<?php

namespace App\Http\Requests\Api\V1\Offices;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'limit'         => 'integer',
            'filter'        => 'array',
            'with'          => 'array',
            'sort'          => 'in:id,city_id',
            'sortDirection' => 'in:asc,desc',
        ];
    }
}
