<?php


namespace App\Services\External\InnovateMobility\Models;

class RailwayAnimalTransportationRequest extends RequestModel
{
    /**
     * {@inheritDoc}
     */
    protected static $type = 'ApiContracts.Railway.V1.AdditionalBaggage.RailwayAnimalTransportationRequest, ApiContracts';

    /**
     * {@inheritDoc}
     */
    protected static $validationMessages = [
        'AnimalDescription.required' => 'Отсутсвует описание животного',
    ];

    /**
     * {@inheritDoc}
     */
    protected static function getValidationRules()
    {
        return [
            'AnimalDescription' => 'required',
        ];
    }
}