<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{User, UserRole, Role};
use Illuminate\Support\Facades\Validator;
use URL;

class PortalUsersController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list()
    {
        return view('admin.portal_users.list')->with('title', 'Пользователи портала');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $role_list = [];

        foreach (Role::get() as $role) {
            $role_list[$role->roleId] = $role->description;
        }

        return view('admin.portal_users.create_edit', compact('role_list'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'userTypeId' => 'numeric|nullable',
                'holdingId' => 'numeric|nullable',
                'clientId' => 'numeric|nullable',
                'email' => 'required|email|unique:users,email',
                'lastName' => 'required',
                'firstName' => 'required',
                'password' => 'required|min:6',
                'confirm_password' => 'required|min:6|same:password',
                'allowedIp' => 'array|nullable'
            ],
            [
                'email.email' => 'Адрес электронной почты указан неверно!',
                'email.unique' => 'Пользователь с таким адресом электронной почты уже есть в базе данных!',
            ]
        );

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {

            $userId = User::create(array_merge($request->all(), [
                'password' => app('hash')->make($request->input('password')),
                'login' => $request->email,
                'isVerifiedMobile' => 'true',
                'userTypeId' => 0,
                'mobile' => $request->mobile,
                'twoFactor' => $request->twoFactor,
                'contacts' => [
                    'firstName' => $request->firstName,
                    'lastName' => $request->lastName,
                    'middleName' => $request->middleName,
                    'contactEmails' => $request->email,
                    'contactPhone' => $request->mobile,
                ],
            ]))->userId;

            if ($userId) {
                if ($request->roleId)
                    foreach ($request->roleId as $roleId) {
                        UserRole::create(['userId' => $userId, 'roleId' => $roleId]);
                    }
            } else abort(500);

            return redirect(URL::route('admin.portalusers.list'))->with('success', 'Пользователь добавлен');
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $userData = User::find($id);

        if ($userData) {

            $role_list = [];

            foreach (Role::get() as $role) {
                $role_list[$role->roleId] = $role->description;
            }

            $roleId = [];

            foreach ($userData->roles as $role) {
                $roleId[] = $role->roleId;
            }

            return view('admin.portal_users.create_edit', compact('userData', 'role_list', 'roleId'));
        }

        abort(404);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request)
    {
        $id = $request->userId;

        if (!is_numeric($id)) abort(500);

        $validator = Validator::make($request->all(),
            [
                'userTypeId' => 'numeric',
                'holdingId' => 'numeric|nullable',
                'clientId' => 'numeric|nullable',
                'email' => 'required|email|unique:users,email,' . $id . ',userId',
                'password' => 'min:6|nullable',
                'lastName' => 'required',
                'firstName' => 'required',
                'password_confirmation' => 'min:6|same:password|nullable',
                'allowedIp' => 'array|nullable'
            ],
            [
                'email.email' => 'Адрес электронной почты указан неверно!',
                'email.unique' => 'Пользователь с таким адресом электронной почты уже есть в базе данных!',
            ]
        );

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {

            if (!empty($request->password) && !empty($request->confirm_password)) {
                if ($request->password != $request->confirm_password) {
                    return back()->withInput()->withErrors(['confirm_password' => "Пароли не совпадают!"]);
                }
            }

            $user = User::find($id);

            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->contacts = [
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'middleName' => $request->middleName,
                'contactEmails' => $request->email,
                'contactPhone' => $request->mobile,
            ];
            $user->contacts->lastName = $request->lastName;
            $user->contacts->middleName = $request->middleName;
            $user->contacts->contactEmails = $request->email;
            $user->contacts->contactPhone = $request->mobile;
            $user->twoFactor = $request->twoFactor;

            if ($request->input('password')) {
                $user->password = app('hash')->make($request->input('password'));
            }

            $user->save();

            UserRole::where('userId', $id)->delete();

            if ($request->roleId)
                foreach ($request->roleId as $roleId) {
                    UserRole::create(['userId' => $id, 'roleId' => $roleId]);
                }

            return redirect(URL::route('admin.portalusers.list'))->with('success', 'Данные пользователя обновлены');
        }
    }

    /**
     * @param $id
     */
    public function destroy($id)
    {
        User::where('userId', $id)->delete();
        UserRole::where('userId', $id)->delete();
    }
}