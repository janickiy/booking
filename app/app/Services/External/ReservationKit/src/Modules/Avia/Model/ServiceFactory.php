<?php

namespace ReservationKit\src\Modules\Avia\Model;

use ReservationKit\src\Modules\Avia\Model\Entity\Search\Settings;
use ReservationKit\src\RK;

class ServiceFactory
{
    /**
     * @var ServiceFactory
     */
    protected static $_instance;

    /**
     *
     * @return ServiceFactory
     */
    public static function getInstance()
    {
        return self::$_instance ? self::$_instance : (self::$_instance = new ServiceFactory());
    }

    /**
     * Возвращает фабрику системы бронирования по имени
     * 
     * @param string $package
     * @return AbstractServiceFactory
     */
    public static function getFactory($system)
    {
        if ($serviceFactory = RK::getContainer()->getModule($system)) {
            return new $serviceFactory();
        }
        
        throw new \RK_Core_Exception('Invalid factory');
    }
}