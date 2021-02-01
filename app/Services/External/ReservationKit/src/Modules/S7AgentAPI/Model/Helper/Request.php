<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Helper;

use ReservationKit\src\Component\XML\XmlElement;

// TODO отрефакторить класс
class Request
{
    private static $_classRef = array(
        'Economy' => 'Y',
        'Business' => 'C',
        'First' => 'F',
        'PremiumEconomy' => 'W'
    );

    /**
     * @param string $requestParamName
     * @param array $items
     * @param mixed $options
     * @return XmlElement[]
     */
    public static function getListRequestParam($requestParamName, array $items, $options = null)
    {
        $listRequestParam = array();

        foreach ($items as $key => $item) {
            $listRequestParam[] = self::buildRequestParam($requestParamName, $item, $key, $options);
        }

        return $listRequestParam;
    }

    /**
     * @param string $requestParamName
     * @param mixed $item
     * @param int|null $key
     * @param mixed|null $options
     * @return XmlElement
     */
    public static function buildRequestParam($requestParamName, $item, $key = null, $options = null)
    {
        $requestParamClass = 'ReservationKit\\src\\Modules\\S7AgentAPI\\Model\\Request\\Param\\' . $requestParamName;

        if (!class_exists($requestParamClass)) {
            throw new \RuntimeException('Класс-параметр ' . $requestParamName . ' не существует');
        }

        // TODO $requestParamClass instanceof XmlElement. Сделать эту проверку

        return new $requestParamClass($item, $key, $options);
    }
}