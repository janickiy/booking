<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 23.08.2018
 * Time: 14:20
 */

namespace App\Http\Controllers\Admin;


class Controller extends \App\Http\Controllers\Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        parent::__construct();
    }

}