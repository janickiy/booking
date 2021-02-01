<?php

namespace ReservationKit\src\Modules\Galileo\Model\Enum;

use ReservationKit\src\Component\Type\Enum;

class ModifyEnum extends Enum
{
    const METHOD_UPDATE_PRICES  = 'updatePrices';

    const METHOD_ADD_COMMISSION = 'addCommission';

    const METHOD_ADD_REMARKS    = 'addRemarks';
}