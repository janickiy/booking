<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Interfaces;

// TODO переименовать в IWSDLRequest
interface IRequest
{
    public function getWSDLServiceName();

    public function getWSDLFunctionName();

    public function getFunctionAttributes();

    public function getFunctionNameSpace();
}