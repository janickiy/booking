<?php

/**
 * Класс для работы с датами
 */
class RK_Core_Date
{
    const DATE_FORMAT_DB         = 'Y-m-d H:i:s';
    const DATE_FORMAT_DB_DATE    = 'Y-m-d';
    const DATE_FORMAT_TIME       = 'H:i';
    const DATE_FORMAT_NO_SEC     = 'Y-m-d H:i';
    const DATE_FORMAT_RUS        = 'd.m.Y H:i:s';
    const DATE_FORMAT_RUS_NO_SEC = 'd.m.Y H:i';
    const DATE_FORMAT_RUS_SHORT_NO_SEC = 'd.m.y H:i';
    const DATE_FORMAT_DATEPICKER = 'd.m.Y';           // Формат даты дэйтпикера
    const DATE_FORMAT_SERVICES   = 'Y-m-d\TH:i:s';    // Формат веб-сервисов
    const DATE_FORMAT_ISO_8601   = 'Y-m-d\TH:i:s.uO'; // Формат стандарта ISO 8601 Y-m-d\TH:i:s.uO или c (c - не работает с DateTime. php bug)

    /**
     * Объект времени
     *
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * Формат даты
     *
     * @var string
     */
    protected $_format;

    public function __construct($timestamp = null, $format = null)
    {
        if ($timestamp instanceof RK_Core_Date) {
            $this->_dateTime = $timestamp->getDateTime();
            $this->_format = $format ? str_replace('!', '', $format) : $timestamp->getFormat();

        } else if ($timestamp instanceof \DateTime) {
            $this->_dateTime = $timestamp;
            $this->_format = self::DATE_FORMAT_DB;

        } else if (!$timestamp) {
            $this->_dateTime = new \DateTime();
            //$this->_dateTime = date(self::DATE_FORMAT_DB); // FIXME for phalanger
            $this->_format = self::DATE_FORMAT_DB;

        } else {
            if (is_int($timestamp)) {
                $this->_dateTime = new \DateTime('@' . $timestamp);
                $this->_dateTime->setTimezone(new \DateTimeZone(date_default_timezone_get()));
                $this->_format = $format ? str_replace('!', '', $format) : self::DATE_FORMAT_DB;

            } else {
                $this->_dateTime = \DateTime::createFromFormat($format, $timestamp);
                //$this->_dateTime = date($format, strtotime(str_replace('.', '-', $timestamp))); // FIXME for phalanger
                $this->_format = str_replace('!', '', $format);
            }
        }
    }

    /**
     * Возвращает метку даты
     *
     * @return DateTime
     */
    public function getDateTime()
    {
        return $this->_dateTime;
    }

    /**
     * Возвращает формат даты
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * Возвращает дату в виде строки
     *
     * @param $format
     * @return bool|string
     */
    public function getValue($format = '')
    {
        $resultFormat = empty($format) ? $this->getFormat() : $format;

        return $this->getDateTime()->format($resultFormat);
    }

    /**
     * Возвращает метку даты
     *
     * @return int
     */
    public function getTimestamp()
    {
        return (int) $this->getDateTime()->format('U');
    }

    /**
     * Возвращает текущую дату и время
     *
     * @return RK_Core_Date
     */
    public static function now()
    {
        return new self();
    }

    /**
     * Возвращает обьект даты в текстовом формате
     *
     * Не преобразовывает дату, если исходная дата уже сохранена в нужном формате
     *
     * @param string $format Нужный формат
     * @return RK_Core_Date
     */
    public function formatTo($format)
    {
        if ($format != $this->getFormat()) {
            return new RK_Core_Date($this, $format);
        }

        return new RK_Core_Date($this);
    }

    /**
     * Определяет разницу между датами
     *
     * @param RK_Core_Date $date1
     * @param RK_Core_Date $date2
     */
    public static function diff(RK_Core_Date $date1, RK_Core_Date $date2)
    {
        $ts1 = strtotime($date1->formatTo(RK_Core_Date::DATE_FORMAT_DB));
        $ts2 = strtotime($date2->formatTo(RK_Core_Date::DATE_FORMAT_DB));

        $secondsDiff = $ts1 - $ts2;

        $diff['H'] = $secondsDiff / 3600;
        $diff['i'] = $secondsDiff / 60;
        $diff['s'] = $secondsDiff;

        return $diff;
    }

    public function __toString()
    {
        return ($this->getDateTime() instanceof DateTime) ? $this->getValue() : '';
    }

    /**
     * Возвращает дату из строки в соответствие с форматом $format
     *
     * Для года в формате "y" соответствую года с 1970 по 2069 (например, 05JUN68)
     * Если $yearToLessSide = true, то год будет меньше date('Y'), но больше date('Y') - 99
     *
     * @param $date
     * @param $format
     * @param bool $yearToLessSide Смещение года
     * @return RK_Core_Date
     */
    public static function createFromFormat($format, $date, $yearToLessSide = false)
    {
        $dateTime = \DateTime::createFromFormat($format, $date);
        $year = $dateTime->format('Y');

        if ($yearToLessSide && $year > date('Y')) {
            $year = '19' . $dateTime->format('y');
        }

        return new RK_Core_Date($year . $dateTime->format('-m-d'), self::DATE_FORMAT_DB_DATE);
    }
}