<?php

namespace App\Http\Controllers\Admin;

use App\Models\SessionLog;
use App\Models\User;
use URL;

class SessionLogController extends Controller
{

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list()
    {
        return view('admin.logs.list')->with('title','Логи');
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function info($id)
    {
        $log = SessionLog::where('session_log_id',$id)->first();

        if ($log) {

            $userLog = User::where('userId',$log->user_id)->first();

            return view('admin.logs.info', compact('log','userLog'))->with('title','Логи');
        }

        abort(404);
    }
}