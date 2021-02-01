<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 05.07.2018
 * Time: 11:45
 */

namespace App\Services;


use App\Models\OrdersLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SessionLog
{

    /**
     * @param Request $request
     */
    public static function start(Request $request)
    {
        $path = $request->getRequestUri();
        $routName = $request->route()->getName() ?? 'noname';
        $requestData = $request->all();
        $sessionId = $request->session()->getId();
        $referer = $request->headers->get('referer', '');

        $log = [
            'session_id' => $sessionId,
            'route' => $routName,
            'log_start_time' => microtime(true),
            'referer' => $referer,
            'path' => $path,
            'request' => $requestData,
            'external' => [],
            'queries' => [],
        ];

        $user = $request->user('web');

        if($user){
            $log['user_id'] = $user->userId;
        }

        session()->put('log', $log);
        session()->put('orderLog', []);
    }

    /**
     * @param $key
     * @param $value
     */
    public static function put($key, $value)
    {
        session()->put('log.'.$key, $value);
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    public static function get($key,$default=null)
    {
        return session()->get('log.'.$key, $default);
    }

    /**
     *
     */
    public static function store()
    {
        $log = session()->get('log');
        session()->forget('log');

        $logStore = new \App\Models\SessionLog($log);
        $logStore->save();

        $ol = session()->get('orderLog', []);
        session()->forget('orderLog');

        foreach ($ol as $logEntity){
            $logRecord = new OrdersLog($logEntity);
            $logRecord->session_log_id = $logStore->session_log_id;
            $logRecord->save();
        }
    }


    /**
     * @param int $orderId ID Заказа таблицы orders
     * @param string $action Действие с заказом (create, update, pay, refund, notify и т.д.)
     * @param string $message Тектс сообщения на русском для лога оператора
     * @param bool $isError Является ли это сообщением об ошибке
     * @param array $payload Данные которые можно добавить для этого сообщения
     */
    public static function orderLog(int $orderId, string $action, string $message, bool $isError=false, array $payload=[])
    {
        $ol = session()->get('orderLog',[]);

        $ol[] = [
            'order_id' => $orderId,
            'action' => $action,
            'message' => $message,
            'payload' => $payload,
            'error'  => $isError ? 'true' : 'false'
        ];

        session()->put('orderLog', $ol);
    }

    /**
     * @param $model
     * @param $callback
     * @return mixed
     */
    public static function logQuery($model, $callback)
    {
        $modelInstance = new $model();
        $connection = $modelInstance->getConnection();
        $start = microtime(true);
        $connection->enableQueryLog();
        $result = $callback();
        $query = $connection->getQueryLog();
        $connection->disableQueryLog();
        $end = microtime(true);

        $log = self::get('queries',[]);

        $log[] = ['model'=>$model,'start'=> $start, 'end' => $end, 'query' => $query];
        self::put('queries',$log);

        return $result;
    }
}