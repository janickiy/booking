<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Admin\{AdminRole,AdminUserRole};
use Illuminate\Support\Facades\Validator;
use URL;

class RoleController extends Controller
{

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list()
    {
        return view('admin.role.list')->with('title','Список ролей');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $app = app();
        $routes = $app->routes->getRoutes();
        $roout_list = [];

        foreach ($routes as $route) {
            $path = explode(".", $route->getName())[0];

            if ($path == 'admin')  $roout_list[] = $route;
        }

        return view('admin.role.create_edit', compact('permissions', 'roout_list'));
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:admin_roles',
            'accessLevel' => 'numeric',
            'accessMap' => 'array'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {

            AdminRole::create($request->all())->adminRoleId;

            return redirect(URL::route('admin.role.list'))->with('success', 'Информация успешно добавлена');
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $role = AdminRole::where('adminRoleId', $id)->first();

        if (!$role) abort(404);

        $app = app();
        $routes = $app->routes->getRoutes();
        $roout_list = [];

        foreach ($routes as $route) {
            $path = explode(".", $route->getName())[0];

            if ($path == 'admin')  $roout_list[] = $route;
        }

        return view('admin.role.create_edit', compact('role', 'permissions', 'roout_list'));
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request)
    {
        if (!is_numeric($request->adminRoleId)) abort(500);

        $rules = [
            'name' => 'required|unique:admin_roles,name,' . $request->adminRoleId .',adminRoleId',
            'accessLevel' => 'numeric',
            'accessMap' => 'array'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {

            $role = AdminRole::find($request->adminRoleId);
            $role->name = $request->name;
            $role->accessLevel = $request->accessLevel;
            $role->accessMap = $request->accessMap;
            $role->save();

            return redirect(URL::route('admin.role.list'))->with('success', 'Данные обновлены');
        }
    }

    /**
     * @param $id
     */
    public function destroy($id)
    {
        AdminRole::where(['adminRoleId' => $id])->delete();
        AdminUserRole::where(['adminRoleId' => $id])->delete();
    }
}