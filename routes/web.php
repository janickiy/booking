<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', 'FrontendController@index')->name('index');

Route::get('lang/{locale}', 'FrontendController@lang')->name('lang');

Route::get('avia', 'FrontendController@avia')->name('avia');
Route::get('transfer', 'FrontendController@transfer')->name('transfer');
Route::get('trains', 'FrontendController@trains')->name('trains');
Route::get('hotels', 'FrontendController@hotels')->name('hotels');
Route::get('auto', 'FrontendController@auto')->name('auto');
Route::get('insurance', 'FrontendController@insurance')->name('insurance');
Route::get('mice', 'FrontendController@mice')->name('mice');
Route::get('support', 'FrontendController@mice')->name('support');
Route::get('contacts', 'FrontendController@mice')->name('contacts');

Route::get('page/{slug}', 'FrontendController@page')->name('page');
Route::get('path/{slug}', 'FrontendController@path')->name('path');

Auth::routes();

Route::prefix('users')
    ->middleware('auth:web')
    ->group(function (){
        Route::get('profile', 'HomeController@index')->name('profile.index');
        Route::post('verify/confirm_mobile', 'Auth\VerifyController@verifyMobile')->name('verify.mobile');
        Route::post('verify/sendotp', 'Auth\VerifyController@sendOPT')->name('verify.sendotp');
    });

Route::get('login', '\App\Http\Controllers\Api\AuthController@showLoginForm')->name('login');
Route::post('login', '\App\Http\Controllers\Api\AuthController@login')->name('login.submit');
Route::get('logout', '\App\Http\Controllers\Api\AuthController@userLogout')->name('logout');

Route::get('auth/token', '\App\Http\Controllers\Auth\TwoFactorController@showTokenForm');
Route::post('auth/token', '\App\Http\Controllers\Auth\TwoFactorController@validateTokenForm');
Route::post('auth/two-factor', '\App\Http\Controllers\Auth\TwoFactorController@setupTwoFactorAuth');