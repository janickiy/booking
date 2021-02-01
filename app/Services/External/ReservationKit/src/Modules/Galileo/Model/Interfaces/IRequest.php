<?php

namespace ReservationKit\src\Modules\Galileo\Model\Interfaces;

interface IRequest
{
    const SERVICE_AirService                = 'AirService';
    const SERVICE_UniversalRecord           = 'UniversalRecordService';
    const SERVICE_CurrencyConversionService = 'CurrencyConversionService';
    const SERVICE_GdsQueueService           = 'GdsQueueService';

    const FUNCTION_LowFareSearchReq           = 'LowFareSearchReq';
    const FUNCTION_AvailabilitySearchReq      = 'AvailabilitySearchReq';
    const FUNCTION_AirPriceReq                = 'AirPriceReq';
    const FUNCTION_AirFareRulesReq            = 'AirFareRulesReq';
    const FUNCTION_AirCreateReservationReq    = 'AirCreateReservationReq';
    const FUNCTION_AirTicketingReq            = 'AirTicketingReq';
    const FUNCTION_UniversalRecordRetrieveReq = 'UniversalRecordRetrieveReq';
    const FUNCTION_UniversalRecordModifyReq   = 'UniversalRecordModifyReq';
    const FUNCTION_UniversalRecordCancelReq   = 'UniversalRecordCancelReq';
    const FUNCTION_CurrencyConversionReq      = 'CurrencyConversionReq';
    const FUNCTION_GdsQueuePlaceReq           = 'GdsQueuePlaceReq';

    public function getWSDLServiceName();

    public function getWSDLFunctionName();

    public function getFunctionAttributes();

    public function getFunctionNameSpace();
}