<?php

namespace ReservationKit\src\Modules\Avia\Model\Helper;

use ReservationKit\src\Modules\Galileo\Model\Entity\Passenger as GalileoPassenger;

class Passenger
{
    /**
     * 1. Замечания по формату для ПАСПОРТНЫХ данных <SI.P1/SSRDOCSYYHK1/P/SG/S12345678/GB/12JUL66/M/23OCT15/SMITH/JOHN>:
     *   а) В зависимости от маршрута у пассажира может запрашиваться отчество или нет. Если отчество не надо указывать,
     *      то у нас в запрос попадают данные со слешем в конце, например, <P/SG/S12345678/GB/12JUL66/M/23OCT15/SMITH/JOHN/>.
     *      Необходимо убирать слеш в конце при отсутствии отчества.
     *   б) В части для кода страны, где выдан паспорт и кода национальности, попадает код страны проживания из визы.
     *      При отсутствии необходимости заполнять визу, всегда ставиться RU. Необходимо метод getCountry() заменить
     *      на getNationality() для соответствующих блоков.
     *
     *      (TODO функция getDOCS(), сделать в файле ReservationKit\Modules\Avia\Model\Passenger\Documents.php)
     *
     * DOCS Формат:
     *      (document type)/(document issue country)/(document number)/(document nationality country)/
     * 	    (date of birth)/(gender)/(document expiration date)/(last name)/(first name)/(middle name)
     *
     * @param \RK_Avia_Entity_Passenger $passenger
     * @return string
     */
    public static function getSSR(\RK_Avia_Entity_Passenger $passenger, $SSRType)
    {
        // Пол пассажира
        if ($passenger->getGender()) {
            $gender = $passenger->getGender();
            if ($passenger->getType() === 'INF') {
                $gender .= 'I';
            }
        } else {
            $gender = '';
        }

        // Гражданство
        $passportCountry = $passenger->getDocCountry();
        if (!$passportCountry) {
            $passportCountry = 'RU';
        }

        // Национальность
        $citizenshipCountry = $passportCountry;

        // Дата действия документа
        $currencyPassport = '07NOV25';
        if ($passenger->getDocExpired()) {
            $currencyPassport = strtoupper($passenger->getDocExpired()->formatTo('dMy')->getValue()); // dMy
        }

        // Номер и серия документа
        $passportNum = '';
        if ($passenger->getDocNumber()) {
            $passportNum .= \RK_Core_Helper_String::translit( $passenger->getDocNumber() );
        }
        //$passportNum = $this->prepareDocumentNumber($passportNum, $this->getService());

        // Дата рождения
        $DOB = '';
        if ($passenger->getBorndate()) {
            $DOB = strtoupper($passenger->getBorndate()->formatTo('dMy')->getValue()); // dMy
        }

        // Тип документа
        $docType = 'P';
        if ($passenger->getDocType()) {
            $docType = $passenger->getDocType();
            $docType = \RK_Gabriel_Helper_Documents::correctAvailableDocType($docType);

            // TODO
            if ($passenger instanceof GalileoPassenger) {

            } else {

            }
        }

        if ($passenger->getType() === 'CHD' || $passenger->getType() === 'INF') {
            $docType = 'F';
        }

        // ФИО
        $family = \RK_Core_Helper_String::translit( str_replace(' ', '', strtoupper( $passenger->getLastname() )) );
        $name   = \RK_Core_Helper_String::translit( str_replace(' ', '', strtoupper( $passenger->getFirstname() )) );

        $DOCAString = '';
        //if ($passenger->getType() === 'ADT') {
		if ($SSRType === 'DOCS')
			$DOCAString = ''.$docType.'/'.$passportCountry.'/'.$passportNum.'/'.$citizenshipCountry.'/'.$DOB.'/'.
			$gender.'/'.$currencyPassport.'/'.$family.'/'.$name;
        //}

        //if ($passenger->getType() === 'CHD') {
        if ($SSRType === 'CHLD')
            // /05May05
            $DOCAString = '/' . $DOB;
        //}

        //if ($passenger->getType() === 'INF' || $passenger->getType() === 'INS') {
        if ($SSRType === 'INFT')
            // .SIMPSON/MAGGIE 01JAN12
            $DOCAString = '.INFANT' . $family . '/' . $name . ' ' . $DOB;
        //}

        return strtoupper($DOCAString);
    }
}