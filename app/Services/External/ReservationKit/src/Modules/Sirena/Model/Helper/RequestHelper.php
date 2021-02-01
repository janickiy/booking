<?php

namespace ReservationKit\src\Modules\Sirena\Model\Helper;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Avia\Model\RequestAviaHelper;

class RequestHelper extends RequestAviaHelper
{
    public static function getServiceName()
    {
        return 'Sirena';
    }

    /**
     * Э/Y - экономический
     * Б/C - бизнес
     * П/F - первый
     */
    protected static $_classRef = array(
        'Economy'  => 'Y',
        'Business' => 'C',
        'First'    => 'F'
    );

    /**
     *
     *
     * @return array
     */
    protected static function getClassRefs()
    {
        return [
            'Economy'  => 'Y',
            'Business' => 'C',
            'First'    => 'F'
        ];
    }
}