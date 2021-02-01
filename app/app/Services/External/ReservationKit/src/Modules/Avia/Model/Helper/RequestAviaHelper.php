<?php

namespace ReservationKit\src\Modules\Avia\Model;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Avia\Model\Exception\InvalidTypeException;
use ReservationKit\src\Modules\Avia\Model\Interfaces\IRequestAvia;

abstract class RequestAviaHelper implements IRequestAvia
{
    /**
     * @param string $requestParamName
     * @param array $items
     * @param mixed $options
     * @return XmlElement[]
     * @throws InvalidTypeException
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
     * @throws InvalidTypeException
     */
    public static function buildRequestParam($requestParamName, $item, $key = null, $options = null)
    {
        $requestParamClass = 'ReservationKit\\src\\Modules\\' . static::getServiceName() . '\\Model\\Request\\Param\\' . $requestParamName;

        if (!class_exists($requestParamClass)) {
            throw new \RuntimeException('Класс-параметр ' . $requestParamName . ' не существует');
        }

        if (!$requestParamClass instanceof XmlElement) {
            throw new InvalidTypeException('Некорректный тип параметра ' . $requestParamName . ' в запросе');
        }

        return new $requestParamClass($item, $key, $options);
    }

    /**
     * Возвращает базовый класс по типу
     *
     * @param $type
     * @return mixed
     */
    public static function getBaseClassByType($type)
    {
        return isset(static::getClassRefs()[$type]) ? static::getClassRefs()[$type]: null;
    }

    /**
     * Возвращает тип класса по базовому классу
     *
     * @param $baseClass
     * @return mixed
     */
    public static function getTypeClassByBase($baseClass)
    {
        return array_search($baseClass, static::getClassRefs());
    }
}