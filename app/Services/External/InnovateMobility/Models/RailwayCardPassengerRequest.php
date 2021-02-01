<?php


namespace App\Services\External\InnovateMobility\Models;

use App\Helpers\LangHelper;

class RailwayCardPassengerRequest extends RequestModel
{
    /**
     * {@inheritDoc}
     */
    protected static $validationMessages = [
        'Sex.required' => 'Отсутвует пол пассажира',
        'DocumentNumber.required' => 'Отсутвует номер документа',
        'DocumentType.required' => 'Отсутвует номер документа',
        'CitizenshipCode.required' => 'Отсутвует гражданство',
        'Phone.required' => 'Отсутвует телефон пассажира',
        'Birthday.date_format' => 'Неверный формат даты рождения',
        'ContactEmails.array' => 'Список контактные емэйлов не соответвует формату',
    ];

    /**
     * {@inheritDoc}
     */
    protected static function getValidationRules()
    {
        return [
            'Sex' => 'required',
            'DocumentNumber' => 'required',
            'DocumentType' => 'required|in:'.implode(',',array_keys(LangHelper::trans('references/im.documentType'))),
            'CitizenshipCode' => 'required',
            'Birthday' => 'date_format:Y-m-d\TH:i:s',
            'ContactEmails' => 'array',

        ];
    }
}