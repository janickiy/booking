<?php


namespace App\Services\External\InnovateMobility\Models;

class RailwayCarTransportationRequest extends RequestModel
{
    /**
     * {@inheritDoc}
     */
    protected static $type = 'ApiContracts.Railway.V1.AdditionalBaggage.RailwayCarTransportationRequest, ApiContracts';

    /**
     * {@inheritDoc}
     */
    protected static $validationMessages = [
        'BaggageName.required' => 'Отсутвует описание багажа',
        'Weight.required' => 'Отсутвует вес',
        'Weight.digits_between' => 'Вес должен быть в килограммах от 1 до 99999 включительно',
        'CarBrand.required' => 'Отсутвует марка транспортного средства',
        'CarModel.required' => 'Отсутвует модель транспортного средства',
        'RegistrationNumber.required' => 'Отсутвует государственный регистрационный номер',
        'RegistrationCertificateNumber.required' => 'Отсутвует номер свидетельства о регистрации транспортного средства',
    ];

    /**
     * {@inheritDoc}
     */
    protected static function getValidationRules()
    {
        return [
            'BaggageName' => 'required',
            'Weight' => 'required|digits_between:1,99999',
            'CarBrand' => 'required',
            'CarModel' => 'required',
            'RegistrationNumber' => 'required',
            'RegistrationCertificateNumber' => 'required',
        ];
    }
}