<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 10.04.2018
 * Time: 13:59
 */

namespace App\Models\Traits;


trait RequestQuery
{

    public static function queryFromRequest(array $params)
    {
        $query = self::query();

        foreach ($params as $name => $param) {

            switch ($name) {
                case 'keyword':
                    if (method_exists(self::class, 'scopeByKeyword') && mb_strlen($param) > 1) {
                        $query = $query->byKeyword($param);
                    }
                    break;
                case 'sort':
                    $direction = $param[1] ?? 'asc';
                    $query = $query->orderBy($param[0], $direction);
                    break;
                case 'limit':
                    $query = $query->limit($param[0]);
                    if (isset($param[1])) {
                        $query = $query->offset($param[1]);
                    }
                    break;
                case 'with':
                    foreach ($param as $relation){
                        if(in_array($relation,self::$attachable)){
                            if(isset($params['keyword']) && $relation === 'stations') {
                                $intKeyword = (int)$params['keyword'];
                                if ("$intKeyword" === $params['keyword']) {
                                    $query = $query->with([$relation => function ($q) use ($intKeyword) {
                                        $q->where('code', $intKeyword);
                                    }]);
                                }else{
                                    $query = $query->with($relation);
                                }
                            }elseif($relation === 'airports'){
                                $query = $query->with([$relation => function($q){
                                    $q->orderBy('info->popularity','desc');
                                }]);
                            }else{
                                $query = $query->with($relation);
                            }

                        }
                    }

                    break;
                case 'has':
                    foreach ($param as $relation){
                        if(in_array($relation,self::$attachable)){
                            $query = $query->has($relation);
                        }
                    }
                    break;
                default:
                    if (isset(self::$queryable) && in_array($name, self::$queryable)) {
                        if (count($param) > 1) {
                            $query = $query->where($name, $param[0], $param[1]);
                        } elseif (count($param) == 1) {
                            $query = $query->where($name, $param[0]);
                        }
                    }
                    break;
            }

        }

        if(!in_array('limit',array_keys($params)) && in_array('with',array_keys($params))){
            $query = $query->limit(100);
        }

        return $query;
    }
}