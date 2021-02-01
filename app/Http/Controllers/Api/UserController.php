<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


/**
 * @group User management
 * APIs for managing user
 */
class UserController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = \Auth::user('web');
            if (!$this->user) {
                return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
            }
            return $next($request);
        });

        parent::__construct();
    }

    /**
     * Retrieve user info
     * [Получение данных пользователя]
     *
     * @response {
     * "userId": 77,
     * "userTypeId": 0,
     * "holdingId": 0,
     * "clientId": 0,
     * "email": "alexander.yanitsky@trivago.ru",
     * "contacts": {
     * "firstName": "Александр",
     * "lastName": "Яницкий",
     * "middleName": null,
     * "contactEmails": "alexander.yanitsky@trivago.ru",
     * "mobile": "+79104696327"
     * },
     * "lastAccessIp": "192.168.1.207",
     * "last_activity_at": "1970-01-01 00:00:00",
     * "last_login_at": "1970-01-01 00:00:00",
     * "created_at": "2019-03-04 13:38:49",
     * "mobile": "+79104696327",
     * "isVerifiedMobile": true,
     * "twoFactor": ["email"],
     * "passenger": null,
        * "role": [
     * {
     * "roleId": 1,
     * "name": "userRailwayOperatorRole",
     * "description": "Оператор заказов частных пользователей"
     * }
     * ],
     * }
     * @response 401 {
     *  "error": true,
     *  "message": "Auth required"
     * }
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getUser()
    {
        return ResponseHelpers::jsonResponse($this->user);
    }

    /**
     * Update user contacts
     * [Обновление контактных данных пользователя (ВНИМАНИЕ: меняется только содержимое секции "contacts" пользователя)]
     *
     * @bodyParam contactEmails string required Контактный Email пользователя.
     * @bodyParam contactPhone string Контактный номер телефона пользователя.
     * @bodyParam firstName string required Имя пользователя.
     * @bodyParam lastName string required Фамилия пользователя.
     * @bodyParam middleName string Отчетсво пользователя.
     * @bodyParam twoFactor array способ двухфакторной авттороизацию (email, phone).
     * @response {
     *  "ContactEmails": "vasya_01@mail.ru",
     *  "ContactPhone": "+71234567890",
     *  "FirstName": "Вася",
     *  "LastName": "Васичкин",
     *  "MiddleName": "Васильевич"
     * }
     * @response 400 {
     *  "error": true,
     *  "messages": {
     *   "ContactEmails": [
     *     "Контактный E-mail указан неверно",
     *     "Контактный E-mail не может быть пустым"
     *    ],
     *    "FirstName": [
     *     "Имя не может быть пустым"
     *    ],
     *    "LastName": [
     *     "Фамилия не может быть пустой"
     *    ]
     *   }
     *  }
     * @response 401 {
     *  "error": true,
     *  "message": "Auth required"
     * }
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function updateUserContacts(Request $request)
    {
        $contacts = $this->user->contacts;

        $ContactEmail = $request->get('contactEmails', '');
        $ContactPhone = $request->get('contactPhone', '');
        $FirstName = $request->get('firstName', '');
        $LastName = $request->get('lastName', '');
        $MiddleName = $request->get('middleName', '');

        $contacts = [
            "contactEmails" => $ContactEmail,
            "contactPhone" => $ContactPhone,
            "firstName" => $FirstName,
            "lastName" => $LastName,
            "middleName" => $MiddleName
        ];

        if (!$this->user->validateContacts($contacts)) {
            return ResponseHelpers::jsonResponse(['error' => true, 'messages' => $this->user->validationErrors], 400);
        }

        $this->user->mobile = $ContactPhone;
        $this->user->twoFactor = $request->twoFactor;
        $this->user->contacts = $contacts;
        $this->user->save();

        return ResponseHelpers::jsonResponse($this->user->contacts, 200);
    }

    /**
     * User password change
     * [Изменения пароля пользователя]
     *
     * @bodyParam currentPassword string required Текущий пароль пользователя.
     * @bodyParam password string required Новый пароль.
     * @bodyParam repeatPassword string required Повтор нового пароля.
     *
     * @response {
     *  "success": true
     * }
     * @response 403 {
     *  "error": true,
     *  "messages": {
     *   "currentPassword": [
     *     "Неверный текущий пароль"
     *    ]
     *   }
     *  }
     * @response 400 {
     *  "error": true,
     *  "messages": {
     *   "repeatPassword": [
     *     "Пароли не совпадают"
     *    ]
     *   }
     *  }
     * @response 401 {
     *  "error": true,
     *  "message": "Auth required"
     * }
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function changePassword(Request $request)
    {
        $oldPassword = $request->get('currentPassword');
        $password = $request->get('password');
        $repeatPassword = $request->get('repeatPassword');

        if (!Hash::check($oldPassword, $this->user->password)) {
            return ResponseHelpers::jsonResponse(['error' => true, 'messages' => ['currentPassword' => ['Неверный текущий пароль']]], 403);
        }

        if ($password !== $repeatPassword) {
            return ResponseHelpers::jsonResponse(['error' => true, 'messages' => ['repeatPassword' => ['Пароли не совпадают']]], 400);
        }

        $this->user->password = Hash::make($password);
        $this->user->save();

        $oldSessionId = session()->getId();

        \Auth::login($this->user);

        if (session()->getId() !== $oldSessionId) {
            session()->put('prevSession', $oldSessionId);
        }

        return ResponseHelpers::jsonResponse(['success' => true], 200);
    }

}