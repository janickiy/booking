<?php

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use URL;

class PortalUsersRoleController extends Controller
{

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list()
    {
        return view('admin.portal_users_role.list')->with('title','Список ролей пользователей портала');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('admin.portal_users_role.create_edit');
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:portal_roles',
            'accessMap' => 'array'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {

            Role::create($request->all());

            return redirect(URL::route('admin.portal_users_role.list'))->with('success', 'Информация успешно добавлена');
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $role = Role::find($id);

        if (!$role) abort(404);

        return view('admin.portal_users_role.create_edit', compact('role'));
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request)
    {
        if (!is_numeric($request->roleId)) abort(500);

        $rules = [
            'name' => 'required|unique:portal_roles,name,' . $request->roleId .',roleId',
            'accessMap' => 'array'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {

            $role = Role::find($request->roleId);
            $role->name = $request->name;
            $role->description = $request->description;
            $role->save();

            return redirect(URL::route('admin.portal_users_role.list'))->with('success', 'Данные обновлены');
        }
    }

    /**
     * @param $id
     */
    public function destroy($id)
    {

    }
}