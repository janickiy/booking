<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $geoData = session()->get('geoData', false);
        $geoDataTryFlag = session()->get('geoDataTryFlag', false);

        if(is_file(config('trivago/sxgeo.path', storage_path('SxGeo')).'/'.config('trivago/sxgeo.filename', 'SxGeoCity.dat'))) {
            if (!$geoData && !$geoDataTryFlag) {
                $geoData = location(request()->getClientIp());
                session()->put('geoDataTryFlag', true);
                if ($geoData) {
                    session()->put('geoData', $geoData);
                }
            }
        }

        $locale = request()->cookie('lang', $geoData ? (strtolower($geoData->country->iso)!=='ru' ? 'en' : 'ru') : config('app.locale'));
        app()->setLocale($locale);

        if(config('app.proto')==='https'){
            $this->app['url']->forceScheme('https');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // TODO ru_RU.UTF-8 - устанавливать через конфиг
        setlocale(LC_TIME, 'ru_RU.UTF-8');

        Carbon::setLocale(app()->getLocale());

        $this->app->bind(
            \Trivago\Hotels\Modules\Jobs\Dispatcher::class,
            \App\Libraries\JobsDispatcher::class
        );
    }
}
