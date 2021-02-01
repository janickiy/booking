<?php

namespace ReservationKit\src\Modules\Avia\Model\Helper;

use ReservationKit\src\Modules\Avia\Model\Type\SSRDataType;

class SSRHelper
{
    /**
     * Разбирает DOCS строку возвращает указанные данные
     * Пример формата:
     * - Galileo uapi FreeText="P/US/F1234567/US/17SEP69/M/24SEP15/SMITH/JACK -1SMITH/JACKMR"
     *
     * @param string $docs
     * @param string $info
     * @return array|string|null
     */
    public static function getFromDOCS($docs, $info = '')
    {
        list($docs, $passengerName) = explode(' -1', $docs);

        $docsInfo = explode('/', $docs);
        $docsInfoParse = array(
            SSRDataType::DOC_TYPE      => $docsInfo[0],
            SSRDataType::DOC_COUNTRY   => $docsInfo[1],
            SSRDataType::DOC_NUMBER    => $docsInfo[2],
            SSRDataType::PAX_COUNTRY   => $docsInfo[3],
            SSRDataType::PAX_DOB       => $docsInfo[4],
            SSRDataType::PAX_GENDER    => $docsInfo[5],
            SSRDataType::DOC_EXPIRED   => $docsInfo[6],
            SSRDataType::PAX_LASTNAME  => $docsInfo[7],
            SSRDataType::PAX_FIRSTNAME => $docsInfo[8],
        );

        if (empty($info)) {
            return $docsInfoParse;
        }

        return isset($docsInfoParse[$info]) ? $docsInfoParse[$info] : null;
    }
}