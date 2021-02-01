<?php

/**
 * Автозагрузчик классов для Reservation Kit
 * Данный класс-одиночку можно подключить к любой клиентской реализации автозагрузки
 */
final class RK_Autoloader
{
    /**
     * Экземпляр класса
     *
     * @var RK_Autoloader
     */
    private static $_instance;

    private function __construct(){  }  // Защита от создания через new Singleton
    private function __clone()    {  }  // Защита от создания через клонирование
    private function __wakeup()   {  }  // Защита от создания через unserialize

    /**
     * Возвращает активный экземпляр класса, создает новый при первом вызове
     *
     * @return RK_Autoloader
     */
    public static function getInstance()
    {
        return self::$_instance ? self::$_instance : (self::$_instance = new RK_Autoloader());
    }

    /**
     * Находит путь к файлу класса по его имени
     *
     * @param string $className
     * @return string | null
     */
    private function findClassByName($className)
    {
        $exploded = explode('_', $className);
        $exploded[0] = 'Modules';

        if ($exploded[2] !== 'Source' && $exploded[2] !== 'Frontend' && $exploded[2] !== 'Event') {
            array_splice($exploded, 2, 0, array('Model'));
        }

        return RK_ROOT_PATH . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $exploded) . '.php';
    }

    /**
     * Находит путь к файлу с классом по namespace класса
     *
     * @param $className
     * @return string
     */
    private function findClassByNamespace($className)
    {
        $className = str_replace('ReservationKit\\src\\', '', $className);
        $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);

        return RK_ROOT_PATH . DIRECTORY_SEPARATOR . $className . '.php';
    }

    /**
     * Подключает файл с классом
     *
     * @param string $className
     * @return bool
     */
    public function loadClass($className)
    {
        if (strpos($className, 'RK_') === 0) {
            // Классическая автозагрузка классов
            require_once($this->findClassByName($className));
            return true;

        } else if (strpos($className, 'ReservationKit\src') === 0) {
            // Загрузка класса с использование namespace
            require_once($this->findClassByNamespace($className));
            return true;

        } else {
            //throw new Exception("[$className] is not a Reservation Kit class");
            return false;
        }
    }
}