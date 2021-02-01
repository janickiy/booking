<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 05.07.2018
 * Time: 10:09
 */

namespace App\Http\Middleware;

use App\Services\SessionLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;

class StartSessionLog
{
    public function handle(Request $request, Closure $next)
    {

        SessionLog::start($request);
        return $next($request);
    }

    /**
     * @param Request $request
     * @param  Response $response
     */
    public function terminate($request, $response)
    {
        $responseData = json_decode($response->getContent());

        $user = auth('web')->user();

        if($user){
            SessionLog::put('user_id',$user->userId);
            $prevSession = session()->get('prevSession', false);
            \App\Models\User::where('userId',$user->userId)->update(['last_activity_at' => Carbon::now()]);

            if($prevSession){
                session()->forget('prevSession');
                \App\Models\SessionLog::where('session_id', $prevSession)->update(['user_id' => $user->userId]);
            }
        }

        SessionLog::put('response', $responseData);
        SessionLog::put('response_code', $response->getStatusCode());
        SessionLog::put('log_end_time', microtime(true));

        SessionLog::store();
    }
}