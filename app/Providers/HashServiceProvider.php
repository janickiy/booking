<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 08.02.2018
 * Time: 16:33
 */

namespace App\Providers;


use App\Libraries\ShaHasher;
use Illuminate\Support\ServiceProvider;

class HashServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('hash', function ($app) {
            return new ShaHasher();
        });
}

    public function provides()
    {
        return ['hash'];
    }

}