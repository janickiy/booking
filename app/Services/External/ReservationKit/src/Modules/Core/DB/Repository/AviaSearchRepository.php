<?php

namespace ReservationKit\src\Modules\Core\DB\Repository;

use ReservationKit\src\RK;
use RK_Avia_Entity_Search_Request;

class AviaSearchRepository extends AbstractRepository
{
    /**
     * @var AviaSearchRepository
     */
    private static $_instance;

    /**
     * @return AviaSearchRepository
     */
    public static function getInstance()
    {
        return self::$_instance ? self::$_instance : (self::$_instance = new AviaSearchRepository());
    }

    /**
     * Возвращает обьект запроса поиска по номеру
     *
     * @param int $searchId
     * @return RK_Avia_Entity_Search_Request
     */
    public function getRequest($searchId)
    {
        $row = $this->getDB()
            ->query('SELECT id, search_object FROM rk_avia_search WHERE id = ? ORDER BY id DESC', $searchId)
            ->fetchRow();

        return unserialize(gzuncompress(base64_decode($row['search_object'])));
    }

    /**
     * Возвращает обьект запроса поиска по хешу
     *
     * @param string $hash
     * @return \RK_Avia_Entity_Search_Request
     */
    public function getRequestByHash($hash)
    {
        $row = $this->getDB()
            ->query('SELECT id, search_object FROM rk_avia_search WHERE hash = ? ORDER BY id DESC', $hash)
            ->fetchRow();

        return unserialize(gzuncompress(base64_decode($row['search_object'])));
    }

    /**
     * Сохраняет обьект поиска
     * 
     * @param RK_Avia_Entity_Search_Request $request
     * @return bool
     * @throws \RK_Core_Exception
     * @throws \ReservationKit\src\Component\DB\Exception
     */
    public function saveRequest(RK_Avia_Entity_Search_Request $request)
    {
        if (!$request->getId()) {
            if (!$this->addRequest($request)) {
                throw new \RK_Core_Exception('Can\'t add request');
            }

        } else {
            $this->getDB()
                 ->query('UPDATE ' . $this->getTable() . ' SET search_object = ? WHERE id = ?',  array( serialize($request), $request->getId() ));
        }

        return $this->getDB()->getAffectedRows() === 1;
    }

    /**
     * Возвращает результаты поиска
     * 
     * @param int $requestId номер поиска
     * @return array
     */
    public function getSearchResults($requestId)
    {
        //$results = array();

        $result = $this->getDB()
                       ->query('SELECT id, result FROM ' . $this->getTable() . ' WHERE id = ? ORDER BY id DESC', $requestId)
                       ->fetchRow();

        return unserialize(gzuncompress(base64_decode($result['result'])));
        
        /*
        foreach ($result as $data) {
            $flight = unserialize($data['result']);
            $flight->setId($data['id']);
            $results[$flight->getId()] = $flight;
        }

        return $results;
        */
    }

    /**
     * Возвращает результаты поиска по хешу
     *
     * @param int $requestId номер поиска
     * @return array
     */
    public function getSearchResultsByHash($hash)
    {
        $result = $this->getDB()
            ->query('SELECT id, result FROM ' . $this->getTable() . ' WHERE hash = ? ORDER BY id DESC', $hash)
            ->fetchRow();

        return unserialize(gzuncompress(base64_decode($result['result'])));
    }
    
    public function saveSearchResult(RK_Avia_Entity_Search_Request $request, $result)
    {
        if ($request->getId()) {
            $this->getDB()->query('UPDATE ' . $this->getTable() . ' SET result = ? WHERE id = ?',
                array(
                    base64_encode(gzcompress(serialize($result), 9)),
                    $request->getId()
                )
            );
            
        } else {
            // TODO
        }
    }

    /**
     * Добавляет новый запрос поиска и возвращает новый номер
     *
     * @param RK_Avia_Entity_Search_Request $request
     * @return null
     */
    protected function addRequest(RK_Avia_Entity_Search_Request $request)
    {
        $zipSearchObject = base64_encode(gzcompress(serialize($request), 9));

        $requestId = $this->getDB()
                          ->insert($this->getTable(), ['hash', 'search_object', 'result'], [$request->getHash(), $zipSearchObject, null])
                          ->fetchRow();

        if ($requestId['id']) {
            $request->setId($requestId['id']);
            return $requestId['id'];
        }

        return null;
    }

    public function getDbDomain()
    {
        return 'main';
    }

    public function getTable()
    {
        return 'rk_avia_search';
    }
}