<?php

namespace ReservationKit\src\Modules\Galileo\Model\Response;

use ReservationKit\src\Modules\Galileo\Model\Abstracts\Response;
use ReservationKit\src\Modules\Galileo\Model\GalileoException;

class UniversalRecordCancelRsp extends Response
{
    public function __construct($response)
    {
        $this->setResponse($response);
    }

    public function parse()
    {
        if (isset($this->getResponse()->Body->Fault)) {
            $messageError = (string) $this->getResponse()->Body->Fault->faultstring;

            if (preg_match('/Universal Record (.{6}) has already been cancelled/', $messageError)) {
                $this->getBooking()->setStatus(\RK_Avia_Entity_Booking::STATUS_CANCEL);
                $this->getBooking()->setCancelDate(\RK_Core_Date::now());
            }

            return true;
        }

        if ($this->getResponse()->Body->UniversalRecordCancelRsp) {
            $content = $this->getResponse()->Body->UniversalRecordCancelRsp;
        } else {
            throw new GalileoException('Bad Response Content in Book Cancel');
        }

        if (isset($content->ProviderReservationStatus['Cancelled']) && ((string) $content->ProviderReservationStatus['Cancelled']) === 'true') {
            $this->getBooking()->setStatus(\RK_Avia_Entity_Booking::STATUS_CANCEL);
            $this->getBooking()->setCancelDate(\RK_Core_Date::now());

        } else {
            throw new GalileoException('Bad Response PNRRead');
        }
    }
}