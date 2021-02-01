<?php

namespace ReservationKit\src\Modules\Galileo\Model\RequestParam;

use ReservationKit\src\Component\XML\XmlElement;

class OSI extends XmlElement
{
    /**
     * Для Аэрофлота:
     * SI.SU*CTCM MOW 79035884269 MIKHAIL/P1, где:
     *  CTСM – мобильный
     *  CTCH – домашний
     *  MOW- код города, где зарегистрирован номер
     *  P1-номер пассажира в брони
     *
     * Для всех остальных а/к другой формат( например Air  Astana- KC)
     * для мобильного SI.KC*CTCP7XXXXXXXXXX-M
     *
     * @param \RK_Avia_Entity_Passenger $passenger
     * @param array|null $key
     * @param null $validatingCompanyCode
     */
    public function __construct(\RK_Avia_Entity_Passenger $passenger, $key, $validatingCompanyCode = null)
    {
        $freeText = ($validatingCompanyCode === 'SU') ? 'M MOW ': 'P';

        $attributesOSI = array(
            'Carrier' => $validatingCompanyCode,
            'Text'    => 'CTC' . $freeText . $passenger->getPhone() . '/P' . ($key + 1) // CTCM MOW79261500299/P1
        );

        $OSI = new XmlElement('OSI', $attributesOSI, null, 'com');

        parent::__construct(null, array(), $OSI);
    }
}