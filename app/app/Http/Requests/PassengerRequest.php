<?php

namespace App\Http\Requests;

use App\Helpers\LangHelper;

class PassengerRequest extends Request
{
    protected static $validationMessages = [
        'nameRu.firstName.max' => 'Значение :attribute должно быть не более :max символов',
        'nameRu.lastName.required' => 'Не указано фамилия пассажира',
        'nameRu.lastName.max' => 'Значение :attribute должно быть не более :max символов',
        'contacts.required' => 'Не указано значение :attribute',
        'documents.required' => 'Не указано значение :attribute',
    ];

    protected static function getValidationRules()
    {
        return [
            'nameRu' => 'required|array',
            'nameRu.firstName' => 'required|string|max:255',
            'nameRu.lastName' => 'required|string|max:255',
            'contacts' => 'required|array',
            'documents' => 'required|array',
            'documents.*.documentType' => 'required|string|in:'.implode(',',array_keys(LangHelper::trans('references/im.documentType'))),
            'documents.*.documentNumber' => 'required'
        ];
    }
}