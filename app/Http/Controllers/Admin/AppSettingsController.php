<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 23.08.2018
 * Time: 13:31
 */

namespace App\Http\Controllers\Admin;

use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use URL;

class AppSettingsController extends Controller
{

    /**
      * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function listSettings()
    {
        return view('admin.settings.list')->with('title','Настройки');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        return view('admin.settings.create_edit');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255|unique:settings',
            'value' => 'required',
            'accessLevel' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {

            Settings::create($request->all());

            return redirect(URL::route('admin.settings.list'))->with('success', 'Информация успешно добавлена');
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $setting = Settings::where('settingId', $id)->first();

        if (!$setting) abort(404);

        return view('admin.settings.create_edit', compact('setting'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        if (!is_numeric($request->settingId)) abort(500);

        $rules = [
            'name' => 'required|max:255|unique:settings,name,' . $request->settingId .',settingId',
            'value' => 'required',
            'accessLevel' => 'required|numeric',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {
            $role = Settings::find($request->settingId);
            $role->name = $request->name;
            $role->accessLevel = $request->accessLevel;
            $role->description = $request->description;
            $role->value = $request->value;
            $role->save();

            return redirect('/cp/settings')->with('success', 'Данные обновлены');
        }
    }

    /**
     * @param $id
     */
    public function destroy($id)
    {
        Settings::where(['settingId' => $id])->delete();
    }
}