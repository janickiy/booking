<?php

namespace ReservationKit\src\Modules\Core\DB\Repository;

class PackageRequisitesRepository extends AbstractRepository
{
    private static $_instance;

    public static function getInstance()
    {
        return isset(self::$_instance) ? self::$_instance : (self::$_instance = new self());
    }

    public function getDbDomain()
    {
        return 'main';
    }

    public function getTable()
    {		
        return 'rk_requisites_packages';
    }
}