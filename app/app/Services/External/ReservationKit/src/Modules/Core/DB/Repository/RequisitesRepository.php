<?php

namespace ReservationKit\src\Modules\Core\DB\Repository;

use ReservationKit\src\Modules\Galileo\Model\RequisiteRules;
use ReservationKit\src\Modules\Galileo\Model\Requisites;

class RequisitesRepository extends AbstractRepository
{
    /** @var RequisitesRepository */
    private static $_instance;

    /**
     * @return RequisitesRepository
     */
    public static function getInstance()
    {
        return self::$_instance ? self::$_instance : (self::$_instance = new self());
    }

    public function findById($id)
    {
        $row = parent::findById($id);

        $result = $this->_unserializeRules($row);

        return $result;
    }

    public function findByPackageId($packageId)
    {
        $rows = $this->getDB()->query('SELECT * FROM ' . $this->getTable() . ' WHERE package_id = ? AND is_active = true', array($packageId))->fetchArray();

        foreach ($rows as $key => $row) {
            $row = $this->_unserializeRules($row);
			
            $rows[$key]['requisite_rule'] = $row['requisite_rule'];
        }
		
        return $rows;
    }

    private function _unserializeRules($row)
    {
        if (!empty($row['requisite_rule'])) {
            // TODO RequisiteRules объект только для Галилео
            $requisiteRules = new RequisiteRules();
            $requisiteRules->fillFromJson($row['requisite_rule']);

            $row['requisite_rule'] = $requisiteRules;
        }

        return $row;
    }

    public function getDbDomain()
    {
        return 'main';
    }

    public function getTable()
    {
        return 'rk_requisites';
    }
}