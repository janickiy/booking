<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Admin\{AdminUser,AdminRole, AdminUserRole};
use Illuminate\Support\Facades\Validator;
use App\Helpers\{ResponseHelpers,StringHelpers};
use Mail;
use URL;

class UsersController extends Controller
{
    /**
     * Список пользователей админки
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list()
    {
        return view('admin.users.list')->with('title','Пользователи админки');
    }

    public function create()
    {
        $roles = AdminRole::get();
        $role_list = [];

        foreach ($roles as $role) {
            if ($role->name) {
                $role_list[$role->adminRoleId] = $role->name;
            }
        }

        return view('admin.users.create_edit', compact('role_list'));
    }

    /**
     * Добавляем нового пользователя админки
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'adminRoleId' => 'required|array',
            'email' => 'required|email|unique:admin_users,email',
            'name' => 'required',
            'login' => 'required',
            'password' => 'required|min:6',
            'confirm_password' => 'required|min:6|same:password',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {

            $adminUserId = AdminUser::create(array_merge($request->all(),['password' => app('hash')->make($request->input('password'))]))->adminUserId;

            if ($adminUserId) {
                foreach ($request->adminRoleId as $adminRoleId) {
                    AdminUserRole::create(['adminUserId' => $adminUserId, 'adminRoleId' => $adminRoleId]);
                }

            } else abort(500);

            return redirect(URL::route('admin.users.list'))->with('success', 'Пользователь добавлен');
        }
    }

    /**
     * форма редактирования пользователя админки
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $roles = AdminRole::get();

        $role_list = [];

        foreach ($roles as $role) {
            if ($role->name) {
                $role_list[$role->adminRoleId] = $role->name;
            }
        }

        $userData = AdminUser::find($id);

        $adminRoleId = [];
        foreach ($userData->roles as $role) {
            $adminRoleId[] = $role->adminRoleId;
        }

        if ($userData) {
            return view('admin.users.create_edit', compact('userData','role_list', 'adminRoleId'));
        }

        abort(404);
    }

    /**
     * Обнавляем данные пользователя
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request)
    {
        $id = $request->adminUserId;

        if (!is_numeric($id)) abort(500);

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:admin_users,email,' . $request->adminUserId .',adminUserId',
            'adminRoleId' => 'required|array',
            'password' => 'min:6|nullable',
            'password_confirmation' => 'min:6|same:password|nullable',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {

            if (!empty($request->password) && !empty($request->confirm_password)) {
                if ($request->password != $request->confirm_password) {
                    return back()->withInput()->withErrors(['confirm_password' => "Пароли не совпадают!"]);
                }
            }

            $data['name'] = trim($request->name);
            $data['email'] = $request->email;

            if ($request->input('password') && $id == \Auth::id()) {
                $data['password'] = app('hash')->make($request->input('password'));
            }

            AdminUser::where('adminUserId',$id)->update($data);
            AdminUserRole::where(['adminUserId' => $id])->delete();

            foreach ($request->adminRoleId as $adminRoleId) {
                AdminUserRole::create(['adminUserId' => $id, 'adminRoleId' => $adminRoleId]);
            }

            return redirect(URL::route('admin.users.list'))->with('success', 'Данные пользователя обновлены');
        }
    }

    /**
     * удаляем пользователя алдминки
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        if ($id != \Auth::id()) {
            AdminUser::where('adminUserId', '=', $id)->delete();
            AdminUserRole::where('adminUserId', '=', $id)->delete();
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function changeUserPassword($id)
    {
        if (!is_numeric($id)) abort(500);

        $user = AdminUser::where('adminUserId',$id)->first();

        if (!$user) {
            return ResponseHelpers::jsonResponse([
                'result' => false,
                'message' => 'Пользователя с таким ID не в базе данных'
            ], 404);
        }

        $data['password'] = app('hash')->make(StringHelpers::randomText(8));

        if (AdminUser::where('adminUserId',$id)->update($data)) {

            $userMsg['email'] = $user->email;
            $userMsg['name'] = $user->name;
            $userMsg['password'] = $data['password'];
            $userMsg['created_at'] = date('Y-m-d H:i:s');

             Mail::send('auth.passwords.admin.new', ['data' => $userMsg], function ($message) use ($userMsg) {
                 $message->from('us@example.com', 'Таларии');
                 $message->to($userMsg['email'])->subject('Новый пароль от админки!');
             });

            return ResponseHelpers::jsonResponse([
                'result' => true,
                'message' => 'Пароль сменён'
            ], 200);
        } else {
            return ResponseHelpers::jsonResponse([
                'result' => false,
                'message' => 'Ошибка веб приложения! Пароль не был изменен'
            ], 500);
        }
    }
}