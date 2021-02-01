<?php

namespace App\Http\Middleware;

use Closure;

class AdminPanelMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $user = $request->user('admin');

        if($user){
            if(!$user->hasAccess($request->route()->getName(), $request->getMethod())) abort(403);
            \View::share('user', $user);
        }

        return $next($request);
    }
}
