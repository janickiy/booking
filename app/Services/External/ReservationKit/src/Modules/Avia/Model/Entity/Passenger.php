<?php

/**
 * Пассажир
 */
class RK_Avia_Entity_Passenger extends RK_Base_Entity_Passenger
{
    /**
     * Данные пассажира
     *
     * Ключи и их значения:
     * firstname - имя
     * lastname - фамилия
     * middlename - отчество
     * borndate - дата рождения
     * age - возраст (должен быть указан на момент вылета в последнем сегменте)
     * gender - пол (M - мужской, F - женский)
     * nationality - национальность (код страны)
     * doc_type - тип документа
     * doc_number - номер документа
     * doc_country - страна выдачи документа (код страны)
     * doc_expired - срок действия документа
     * phone - телефон
     * email - email
     * fqtv - номер мильной карты
     * ...
     * born_country - страна рождения(код страны),
     * born_city - город рождения(код города),
     * visa_num - номер визы,
     * visa_city - город выдачи визы(код города),
     * visa_date - дата выдачи визы,
     * visa_for_country - виза для страны(код страны),
     * city - город проживания(код города),
     * state - штат/область проживания,
     * address - адрес проживания,
     * zip - индекс адреса проживания
     * contact
     *
     * @var array
     */
    protected $_values = array(
        'id'          => null,
        'firstname'   => null,
        'lastname'    => null,
        'middlename'  => null,
        'ticketnum'   => null,
        'country'     => null,
        'borndate'    => null,
        'age'         => null,
        'gender'      => null,
        'nationality' => null,
        'doc_type'    => null,
        'doc_number'  => null,
        'doc_country' => null,
        'doc_expired' => null,
        'phone'       => null,
        'email'       => null,
        'fqtv'        => null,
        'loyalty_card'=> null,

        /*
        'visaPlace' => null,
        'visaBirthCountry' => null,
        'visaBirthCity' => null,
        'visaExpired' => null,
        'arrAddressCountry' => null,
        'arrAddressState' => null,
        'arrAddressCity' => null,
        'arrAddressPostalCode' => null,
         */
    );

    /**
     * Проверяет параметр на правильность перед его установкой в объект
     *
     * @param type $name
     * @param type $value
     * @return type
     * @codeCoverageIgnore
     */
    public function validate($name, $value)
    {
        // TODO: email
        switch ($name) {
            case 'age':
            case 'baggage':
                if (((int) $value) < 0) {
                    return false;
                }
                break;

			case 'nationality':
			case 'country':
                if (!preg_match('/\w{2}/', $value)) {
                    return false;
                }
				break;

            case 'gender':
                if ($value != 'M' && $value != 'F') {
                    return false;
                }
                break;

            case 'visa_date':
            case 'borndate':
            case 'doc_expired':
                // TODO: сделать по-уму, если нужно будет
                /* @var $value RK_Core_Date */
                /*if (!preg_match('/\d{2}[\.-]\d{2}[\.-]\d{4}/', $value->getValue())) {
                    return false;
                }*/
                break;

            case 'doctype':
                if ($value != 'P' && $value != 'C' && $value != 'A' && $value != 'B' && $value != 'M' && $value != 'F') {
                    return false;
                }
                break;

            default:
                return true;
                break;
        }

        return true;
    }

    /**
     * Обработка значений устанавливаемых параметров
     *
     * @param string $name
     * @param $value
     * @return mixed
     */
    public function processing($name, $value)
    {
        switch ($name) {
            case 'phone':
                if (is_string($value)) {
                    $value = preg_replace('/[^0-9]/', '', $value);
                }
                break;

            default:
                break;
        }

        return $value;
    }

    /**
     * Возвращает id пассажира
     *
     * @return string
     */
    public function getId()
    {
        return $this->getValue('id');
    }

    /**
     * @param string $id
     */
    public function setid($id)
    {
        $this->setValue('id', $id);
    }

    public function getPrefixName($useShortPrefix = false)
    {
        $prefix = 'MR';

        if ($this->getGender() === 'M') {
            if ($this->getType() === 'ADT' || $useShortPrefix) {
                $prefix = 'MR';
            } else {
                $prefix = 'MSTR';
            }

        } else {
            if ($this->getType() === 'ADT' || $useShortPrefix) {
                $prefix = 'MRS';
            } else {
                $prefix = 'MISS';
            }
        }

        return $prefix;
    }

    /**
     * Возвращает имя пассажира
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->getValue('firstname');
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->setValue('firstname', $firstname);
    }

    /**
     * Возвращает фамилию пассажира
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->getValue('lastname');
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->setValue('lastname', $lastname);
    }

    /**
     * Возвращает отчество пассажира
     *
     * @return string
     */
    public function getMiddlename()
    {
        return $this->getValue('middlename');
    }

    /**
     * @param string $middlename
     */
    public function setMiddlename($middlename)
    {
        $this->setValue('middlename', $middlename);
    }

    /**
     * Возвращает дату рождения пассажира в указанном формате. Стандартный формат 'd.m.Y'
     *
     * @param bool $format
     * @return RK_Core_Date
     */
    public function getBorndate($format = false)
    {
        /* @var RK_Core_Date $date */
        if ($format && ($date = $this->getValue('borndate'))) {
            //return RK_Core_Date::parse($date, 'd.m.Y')->formatTo($format);
            return $date->formatTo($format)->getValue();
        }

        return $this->getValue('borndate');
    }

    /**
     * Устанавливает дату рождения пассажира
     *
     * @param $borndate
     * @param string $format
     * @throws RK_Base_Exception
     */
    public function setBorndate($borndate, $format = 'd-m-Y')
    {
        $this->setValue('borndate', new \RK_Core_Date($borndate, $format));
    }

    /**
     * Возвращает возраст пассажира относительно момента времени
     *
     * @param RK_Core_Date|null $dateAbout
     * @return int
     */
    public function getAgeAboutDate(RK_Core_Date $dateAbout = null)
    {
        if (!$dateAbout) {
            // Возраст относительно текущего момента времени
            $dateAbout = RK_Core_Date::now();
        }

        $bornDate = new RK_Core_Date($this->getValue('borndate'), 'd-m-Y');

        $diff = $bornDate->getDateTime()->diff($dateAbout->getDateTime(), true);

        return (int) $diff->format('%y');
    }

    public function getAge()
    {
        return $this->getValue('age');
    }

    public function setAge($age)
    {
        $this->setValue('age', $age);
    }

    /**
     * Возвращает пол пассажира (M - мужской, F - женский)
     *
     * @return string
     */
    public function getGender()
    {
        return $this->getValue('gender');
    }

    /**
     * Устанавливает пол пассажира (M - мужской, F - женский)
     *
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->setValue('gender', $gender);
    }

    /**
     * Возвращает национальность пассажира (код страны)
     *
     * @return string
     */
    public function getNationality()
    {
        return $this->getValue('nationality');
    }

    /**
     * Устанавливает национальность пассажира (код страны)
     *
     * @param string $nationality
     */
    public function setNationality($nationality)
    {
        $this->setValue('nationality', $nationality);
    }

    /**
     * Возвращает тип документа пассажира
     *
     * @return string
     */
    public function getDocType()
    {
        return $this->getValue('doc_type');
    }

    /**
     * Устанавливает тип документа пассажира
     *
     * @param string $doctype
     */
    public function setDocType($doctype)
    {
        $this->setValue('doc_type', $doctype);
    }

    /**
     * Возвращает номер документа
     *
     * @return string
     */
    public function getDocNumber()
    {
        return $this->getValue('doc_number');
    }

    /**
     * Устанавливает номер документа
     *
     * @param string $docnumber
     */
    public function setDocNumber($docnumber)
    {
        $this->setValue('doc_number', $docnumber);
    }

    /**
     * Возвращает номер документа
     *
     * @return string
     */
    public function getDocCountry()
    {
        return $this->getValue('doc_country');
    }

    /**
     * Устанавливает номер документа
     *
     * @param string $country
     */
    public function setDocCountry($country)
    {
        $this->setValue('doc_country', $country);
    }

    /**
     * Возвращает срок действия документа
     *
     * @return string
     */
    public function getDocExpired($format = null)
    {
        if ($format && ($date = $this->getValue('doc_expired'))) {
            return $date->formatTo($format);
        }

        return $this->getValue('doc_expired');
    }

    /**
     * Устанавливает срок действия документа
     *
     * @param string $expired
     * @param string $format
     */
    public function setDocExpired($expired, $format = 'd-m-Y')
    {
        $expired = new \RK_Core_Date($expired, $format);
        $this->setValue('doc_expired', $expired);
    }

    /**
     * Возвращает телефон пассажира
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->getValue('phone');
    }

    /**
     * Устанавливает телефон пассажира
     *
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->setValue('phone', $phone);
    }

    /**
     * Возвращает email пассажира
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getValue('email');
    }

    /**
     * Возвращает email пассажира
     *
     * @return string
     */
    public function setEmail($email)
    {
        $this->setValue('email', $email);
    }

    public function isAgeRequired()
    {
        return ($this->getType() === 'CHD' || $this->getType() === 'INF');
    }

    /**
     * Добавляет номер билета
     *
     * @param string | int $value Номер билета
     * @param int $numWay Номер пути (плеча)
     * @param int | null $numSegment Номер сегмента
     */
    public function addTicketNumber($value, $numWay, $numSegment = null)
    {
        $tickets = $this->getTicketNumbers();

        if (!isset($tickets[$numWay])) {
            $tickets[$numWay] = array();
        }

        if ($numSegment !== null) {
            $tickets[$numWay][$numSegment] = $value;
        } else {
            $tickets[$numWay][] = $value;
        }

        $this->setValue('ticketnum', $tickets);
    }

    /**
     * Сброс номеров билетов
     */
    public function resetTicketNumbers()
    {
        $this->setValue('ticketnum', array());
    }

    /**
     * Возвращает номера билетов пассажира
     */
    public function getTicketNumbers()
    {
        return $this->getValue('ticketnum');
    }

    /**
     * Устанавливает номер мильной карты
     *
     * @param string $number Номер мильной карты
     */
    public function setFQTV($number)
    {
        $this->setValue('fqtv', $number);
    }

    /**
     * Возвращает номер мильной карты
     */
    public function getFQTV()
    {
        return $this->getValue('fqtv');
    }

    /**
     * Возвращает список карт лояльности (мильные карты)
     *
     * return array|null
     */
    public function getLoyaltyCard()
    {
        return $this->getValue('loyalty_card');
    }

    /**
     * Устанавливает список карт лойльностей
     *
     * @param array $cards
     */
    public function setLoyaltyCard(array $cards)
    {
        $this->setValue('loyalty_card', $cards);
    }

    /**
     * Добавляет номер карты к коду поставщика
     *
     * @param $supplierCode
     * @param $cardNumber
     */
    public function addLoyaltyCard($supplierCode, $cardNumber)
    {
        $cards = $this->getValue('loyalty_card');
        $cards[$supplierCode] = $cardNumber;

        $this->setValue('loyalty_card', $cards);
    }

    public function getLoyaltyCardBySupplier($supplierCode)
    {
        $cards = $this->getValue('loyalty_card');

        return isset($cards[$supplierCode]) ? $cards[$supplierCode] : null;
    }

    /**
     * Возвращает хеш-идентификатора пассажира
     * Формат: md5(Имя : Фамилия : Дата рождения (Y-m-d) : Номер документа)
     *
     * TODO берется не полный номер документа. Т.к. на тестовых серверах номера документов маскируются
     * TODO Последние 4 цифры для корректной работы на тестовых серверах
     *
     * return string
     */
    public function getHash()
    {
        $hashInfo = array(
            $this->getFirstname(),
            $this->getLastname(),
            $this->getBorndate('Y-m-d'),
            substr($this->getDocNumber(), -4)
        );

        return /*md5(*/implode(':', $hashInfo)/*)*/;
    }
}