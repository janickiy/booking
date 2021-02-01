<?php

namespace ReservationKit\src\Modules\Core\DB\Repository;

use ReservationKit\src\Modules\Avia\Model\Entity\Search\Settings;
use ReservationKit\src\RK;

class AviaSearchSettingsRepository extends AbstractRepository
{
    /**
     * @var AviaSearchSettingsRepository
     */
    private static $_instance;

    /**
     * @return AviaSearchSettingsRepository
     */
    public static function getInstance()
    {
        return self::$_instance ? self::$_instance : (self::$_instance = new AviaSearchSettingsRepository());
    }

    /**
     * Возвращает все поисковые настройки
     * 
     * @return Settings[]
     * @throws \ReservationKit\src\Component\DB\Exception
     */
    public function getAll()
    {
        $results = $this->getDB()
            ->query('select * from rk_avia_search_settings where is_active = 1')
            ->fetchArray();

        $resultsSettings = array();
        foreach ($results as $key => $item) {
            $settings = new Settings();
            $settings->setId($item['id']);
            $settings->setName($item['name']);
            $settings->setSystem($item['system']);
            $settings->setRules(new Settings\Rules($item['rules']));
            $settings->setIsActive($item['is_active']);

            $resultsSettings[$key] = $settings;
        }

        return $resultsSettings;
    }

    /**
     * Находит пакет по номеру
     *
     * @param int $id
     * @return Settings
     */
    public function getPackage($id)
    {
        $sql = 'select * from rk_avia_search_settings where id = ?';

        if ($row = $this->cachedFetchRow($sql, (int) $id)) {
            $settings = new Settings();
            $settings->setId($row['id']);
            $settings->setName($row['name']);
            $settings->setSystem($row['system']);
            $settings->setRules(new Settings\Rules($row['rules']));
            $settings->setIsActive($row['is_active']);

            return $settings;
        }
    }

    public function getDbDomain()
    {
        return 'main';
    }
}