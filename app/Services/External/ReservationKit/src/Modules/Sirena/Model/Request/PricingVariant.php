<?php

namespace ReservationKit\src\Modules\Sirena\Model\Request;

class PricingVariant extends Pricing
{
    /**
     * @param \RK_Avia_Entity_Booking|\RK_Avia_Entity_Search_Request $searchRequestOrBooking
     */
    public function __construct($searchRequestOrBooking)
    {
        parent::__construct($searchRequestOrBooking);
    }

    public function getRequestName()
    {
        return 'pricing_variant';
    }
}