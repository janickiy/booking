<?php

namespace ReservationKit\src\Modules\Galileo\Model\RequestParam;

use ReservationKit\src\Component\XML\XmlElement;

class HostToken extends XmlElement
{
    public function __construct($hostToken, $key)
    {
        $HostToken = new XmlElement('HostToken', array('Key' => $key), $hostToken, 'com');

        parent::__construct(null, array(), $HostToken);
    }
}