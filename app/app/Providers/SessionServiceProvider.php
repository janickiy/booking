<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 13.02.2018
 * Time: 10:36
 */

namespace App\Providers;


class SessionServiceProvider extends \Illuminate\Session\SessionServiceProvider
{
    public function register()
    {
        parent::register();
    }

    public function boot()
    {

    }

    protected function checkMainDomainAuth()
    {

    }

}