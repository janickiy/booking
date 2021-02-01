<?php

namespace App\Http\Controllers\Admin;

class LanguagesController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list()
    {
        return view('admin.languages.list')->with('title','Языки');
    }
}