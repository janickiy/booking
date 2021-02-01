<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 13.02.2018
 * Time: 15:23
 */

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelpers;
use App\Http\Controllers\Controller;
use App\Models\{User, PasswordReset};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\Messages\Email;
use App\Services\QueueBalanced;
use App\Jobs\{EmailNotification, SmsNotification};
use App\Helpers\StringHelpers;
use App\Helpers\NotificationHelpers;
use App\Services\Messages\Sms;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use URL;
use Session;
use Cookie;
use App\Helpers\LangHelper;

/**
 * Class AuthController
 * @group Auth
 * @package App\Http\Controllers\Api
 */
class AuthController extends Controller
{

    /**
     * Check authorized
     * [Проверка авторизован ли пользователь]
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function check()
    {
        $user = \Auth::user('web');

        if ($user) {
            return ResponseHelpers::jsonResponse(['key' => encrypt($user->userId), 'locale' => app()->getLocale()], 200, true);
        }

        return ResponseHelpers::jsonResponse([], 200, true);
    }

    public function domains()
    {
        return ResponseHelpers::jsonResponse(config('app.domains'));
    }

    /**
     * Token Authorization
     * [Авторизация]
     *
     * @param $key
     * @param $locale
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function auth($key, $locale)
    {
        $id = decrypt($key);
        $user = User::where('userId', $id)->first();

        if (!$user) {
            return ResponseHelpers::jsonResponse([], 404);
        }

        if ($locale) {
            app()->setLocale($locale);
            \Cookie::queue(cookie(
                'language', $locale, 60 * 24));
        }

        $oldSessionId = session()->getId();

        auth('web')->login($user);

        if (session()->getId() !== $oldSessionId) {
            session()->put('prevSession', $oldSessionId);
        }

        $user->last_login_at = Carbon::now();

        return ResponseHelpers::jsonResponse(['email' => $user->email], 200);
    }

    /**
     * Authorized User Data
     * [данные пользователя]
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function info()
    {
        $user = \Auth::user('web');

        if (!$user) {
            return ResponseHelpers::jsonResponse([], 404);
        }

        return ResponseHelpers::jsonResponse($user);
    }

    /**
     * Sign Out
     * [Выход]
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function logout()
    {
        \Auth::guard('web')->logout();
        /* \Cookie::queue(cookie(
             config('session.cookie'), session()->getId(), -120));*/

        return ResponseHelpers::jsonResponse(true, 200, true);
    }

    /**
     * Authorization
     * [авторизаци]
     * @bodyParam email string required
     * @bodyParam password string required
     * @bodyParam twoFactorMethod string способ двухфакторной авторизации (phone,email)
     *
     * @response {
     *  "email": "vasya_01@mail.ru",
     *  "password": "1234567",
     * }
     *
     * @response 200 {
     * {
     * "message": "Код верификации отправлен на Ваш номер телефона +79*****6327",
     * "userId": 77,
     * "key": "eyJpdiI6IjFJTVphMUZPQ1hxQmZGWHFkSmV2eXc9PSIsInZhbHVlIjoiSFVtWWhJQmhmMmcwQnJ6a1loYWNmZz09IiwibWFjIjoiMjZmZGU2MzczMWFmMGE2OGJjYWM5ZTZhZTIzNmE1NmI4OTE3YmZhZTlkMjA1MWYxMjdjM2NjZDg4MWU2YmY2YiJ9",
     * "twoFactor": false
     * }
     * }
     *
     * @response 200 {
     * {
     *"userId": 77,
     * "key": "eyJpdiI6IlpianBrbjJ2dXdcL3o4NzlUN1VhU0NRPT0iLCJ2YWx1ZSI6IjI1XC9ieTI2VXlobnFYZTlCV3QwcVZnPT0iLCJtYWMiOiI1OWE0OWViYzMyNWM0MGViYWVkODVkNGQ4YmNiNDYwNTBhYmI4MWRhZGU0NjIzZjEyMWIzYjM0NWEwMTAyZTBiIn0=",
     * "twoFactor": true
     * }
     *
     * @response 202 {
     * "message": "Выберите способ двухфакторной авторизации",
     * "userId": 77,
     * "key": "eyJpdiI6Im5iaksxTGs5djRac3FDYVQrd1dqYWc9PSIsInZhbHVlIjoiVFh4QlR6dDdUVmNRY0FFZkRWZnlJZz09IiwibWFjIjoiZTlhYWViY2RhNWZiOWE2NDU0YjcxOTI1OThiMjQ0MDc5ZDljN2JhMjZiNWI1ZGUwM2NkYWI2NTJmNjY0MmYzNiJ9",
     * "twoFactor": false,
     * "twoFactorMethod": {
     * "phone": "Телефон +79*****6327",
     * "email": "E-mail ale**********************i.ru"
     * }
     * }
     *
     * @response 202 {
     * "message": "Выберите способ двухфакторной авторизации",
     * "userId": 77,
     * "key": "eyJpdiI6Im5iaksxTGs5djRac3FDYVQrd1dqYWc9PSIsInZhbHVlIjoiVFh4QlR6dDdUVmNRY0FFZkRWZnlJZz09IiwibWFjIjoiZTlhYWViY2RhNWZiOWE2NDU0YjcxOTI1OThiMjQ0MDc5ZDljN2JhMjZiNWI1ZGUwM2NkYWI2NTJmNjY0MmYzNiJ9",
     * "twoFactor": false,
     * "twoFactorMethod": {
     * "phone": "Телефон +79*****6327",
     * "email": "E-mail ale**********************i.ru"
     * }
     * }
     *
     *
     * @response 400 {
     *  "error": {
     *    "email": [
     * "Поле email обязательно для заполнения."
     * ],
     * "password": [
     * "Поле password обязательно для заполнения."
     * ]
     *   }
     *  }
     * @response 401 {
     *  "error": true,
     *  "message": "Auth required"
     * }
     *
     *
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function login(Request $request)
    {
        if (isset($request->twoFactorMethod) && Session::get('userId')) {

            $code = Redis::get('2fa_token_' . Session::get('userId'));

            if (!$code) {
                session()->forget('userId');
                session()->forget('remember');

                return ResponseHelpers::jsonResponse([
                    'error' => 'Ошибка верификации'
                ], 400);
            }

            $user = User::find(Session::get('userId'));

            if (!empty($user->mobile) && ($user->role || $user->twoFactor)) {

                if ($user->isVerifiedMobile === false) {
                    return ResponseHelpers::jsonResponse([
                        'error' => 'Вы не можете отправить код верификации на неподтвержденный номер телефона!'
                    ], 400);
                }

                $data = [
                    'code' => $code,
                ];

                if ($request->twoFactorMethod == 'email') {
                    $message = 'Код верификации отправлен на Ваш адрес электроной почты ' . StringHelpers::hidePartText($user->email);

                    NotificationHelpers::Email2FactorNotification($user->email, $data);

                } else {
                    $message = 'Код верификации отправлен на Ваш номер телефона ' . StringHelpers::hidePartText($user->mobile);

                    NotificationHelpers::Sms2FactorNotification($user->mobile, $code);
                }

                return ResponseHelpers::jsonResponse(['message' => $message, 'userId' => $user->userId, 'key' => encrypt($user->userId), 'twoFactor' => false], 202, true);

            }
        }

        $validator = Validator::make($request->all(),
            [
                'email' => 'required|string',
                'password' => 'required|string'
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        // проверяем правльно ли указан логи и пароль
        if ($user = app('auth')->getProvider()->retrieveByCredentials($request->only('email', 'password'))) {

            // проверяем, требуется ли двухфакторная авторизация
            if ($user->role || $user->twoFactor) {

                $code = StringHelpers::generateCode();

                if ($user->twoFactor && count($user->twoFactor) > 1) {
                    $this->setAutSesions($user->userId,$code);

                    return ResponseHelpers::jsonResponse(['message' => 'Выберите способ двухфакторной авторизации', 'userId' => $user->userId, 'key' => encrypt($user->userId), 'twoFactor' => false, 'twoFactorMethod' => ['phone' => 'Телефон ' . StringHelpers::hidePartText($user->mobile), 'email' => 'E-mail ' . StringHelpers::hidePartText($user->email)]], 202, true);

                } else if ($user->twoFactor && strpos(serialize($user->twoFactor), "email") !== false) {
                    $this->setAutSesions($user->userId,$code);

                    $data = [
                        'code' => $code,
                    ];

                    $message = 'Код верификации отправлен на Ваш адрес электроной почты ' . StringHelpers::hidePartText($user->email);

                    NotificationHelpers::Email2FactorNotification($user->email, $data);

                    return ResponseHelpers::jsonResponse(['message' => $message, 'userId' => $user->userId, 'key' => encrypt($user->userId), 'twoFactor' => false], 202, true);

                } else {

                    if (!empty($user->mobile) && $user->isVerifiedMobile) {
                        $this->setAutSesions($user->userId,$code);

                        $message = 'Код верификации отправлен на Ваш номер телефона ' . StringHelpers::hidePartText($user->mobile);

                        NotificationHelpers::Sms2FactorNotification($user->mobile, $code);

                        return ResponseHelpers::jsonResponse(['message' => $message, 'userId' => $user->userId, 'key' => encrypt($user->userId), 'twoFactor' => false], 202, true);
                    }
                }
            }

            $oldSessionId = session()->getId();

            $auth = \Auth::guard('web')->attempt(['email' => $request->email, 'password' => $request->password], $request->remember);

            if(!$auth){
                return ResponseHelpers::jsonResponse([
                    'error' => LangHelper::trans('auth.failed')
                ], 401);
            }

            if (session()->getId() !== $oldSessionId) {
                session()->put('prevSession', $oldSessionId);
            }

            $user->update(['last_login_at' => Carbon::now()]);

            return ResponseHelpers::jsonResponse(['userId' => $user->userId, 'key' => encrypt($user->userId), 'twoFactor' => (bool)$user->twoFactor], 200, true);
        }

        return ResponseHelpers::jsonResponse([
            'error' => LangHelper::trans('auth.failed')
        ], 401);
    }

    /**
     * Send a link to emailpassword for recovery email
     * [Отправка ссылки восстановления пароля на email]
     *
     * @bodyParam email required email
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function email(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'email' => 'required|email',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        } else {

            $result = User::where('email', 'ilike', $request->email);

            if ($result->count() > 0) {

                $userData = $result->first();
                $token = str_random(20);

                PasswordReset::where('userId', $userData->userId)->delete();
                PasswordReset::create(['token' => $token, 'userId' => $userData->userId]);

                $data = [
                    'email' => $request->email,
                    'link' => URL::route('reset_password', ['token' => $token]),
                    'ip' => $request->ip(),
                    'site' => 'Trivago.ru'
                ];

                QueueBalanced::balance(
                    new EmailNotification(
                        new Email(
                            $request->email,
                            'web@trivago.ru',
                            'Восстановление пароля',
                            $data,
                            'email.notification.request_reset_password'
                        )
                    ),
                    'emails');
            }

            return ResponseHelpers::jsonResponse(['message' => 'На Ваш email отправлена ссылка на восстановление пароля. Проверьте свою почту'], 200);
        }
    }

    /**
     * Reset password
     * [Изменение пароля]
     * @bodyParam token required
     * @bodyParam password required
     * @bodyParam confirm_password required
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'token' => 'required',
                'password' => 'required|min:6',
                'confirm_password' => 'required|min:6|same:password',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        } else {
            $passwordReset = PasswordReset::where('token', $request->token);

            if ($passwordReset->count() > 0) {

                if (!isset($passwordReset->first()->user->userId) && $passwordReset->first()->user->userId) {
                    return ResponseHelpers::jsonResponse([], 401);
                }

                User::where('userId', $passwordReset->first()->user->userId)->update(['password' => app('hash')->make($request->password), 'userTypeId' => 0, 'lastAccessIp' => $request->ip()]);

                $data = [
                    'email' => $passwordReset->first()->user->email,
                    'site' => 'Trivago.ru'
                ];

                QueueBalanced::balance(
                    new EmailNotification(
                        new Email(
                            $passwordReset->first()->user->email,
                            'web@trivago.ru',
                            'Сброс пароля выполнен',
                            $data,
                            'email.notification.reset_password'
                        )
                    ),
                    'emails');

                $passwordReset->delete();

                return ResponseHelpers::jsonResponse(['message' => 'Сброс пароля выполнен'], 200);

            } else {
                return ResponseHelpers::jsonResponse([
                    'error' => 'Ошибка Токена'
                ], 401);
            }
        }
    }

    /**
     * verifyMobile
     * [Верификация номера телефона]
     * @bodyParam code required код верификации
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function verifyMobile(Request $request)
    {
        if (\Auth::check() === false) {
            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        $user = \Auth::user('web');

        if ($user->isVerifiedMobile === true) {
            return ResponseHelpers::jsonResponse(['message' => 'Ваш номер телефона уже верифицирован'], 200);
        }

        $validator = Validator::make($request->all(),
            [
                'code' => 'required',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        } else {

            $code = $request->session()->get('verifyMobileСode');

            if ($code == $request->input('code')) {

                User::where('userId', $user->userId)->update(['isVerifiedMobile' => 1]);

                Session::forget('verifyMobileСode');

                return ResponseHelpers::jsonResponse([
                    'isVerified' => 1,
                    'message' => "Ваш номер подтвержден."
                ], 200);

            } else {
                return ResponseHelpers::jsonResponse([
                    'error' => 'Код не совпадает.'
                ], 400);
            }
        }
    }

    /**
     * sendOtp
     * [Запрос смс для подтверждения номера телефона]
     * @bodyParam phone string номер телефона (если не оправлять, будет верефицирован из поля mobile)
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function sendOtp(Request $request)
    {
        if (\Auth::check() === false) {
            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        $user = \Auth::user('web');

        if ($request->input('phone')) {
            $phone = $request->input('phone');
            User::where('userId', $user->userId)->update(['mobile' => $phone]);
        } else {
            $phone = $user->mobile;
        }

        $code = StringHelpers::generateCode();

        QueueBalanced::balance(
            new SmsNotification(
                new Sms($phone, 'Ваш код подтверждения: ' . $code . ' Наберите его в поле ввода.')
            ),
            'sms');

        session()->put('verifyMobileСode', $code);

        return ResponseHelpers::jsonResponse(['message' => 'Запрос на верификацию номера телефона отправллен'], 200);
    }

    /**
     * verifyTwoFactor
     * [Двухфакторная верификация по sms или по email]
     * @bodyParam code required код верификаци полученный на номер телефона
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function verifyTwoFactor(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'code' => 'required',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $user = User::find(Session::get('userId'));

        if (!$user) {
            return ResponseHelpers::jsonResponse([
                'error' => 'Ошибка верификации'
            ], 500);
        }

        $code = Redis::get('2fa_token_' . $user->userId);

        if (!$code) {
            return ResponseHelpers::jsonResponse([
                'error' => 'Время сессии истекло'
            ], 408);
        }

        if ($code == $request->input('code')) {
            $user->isVerifiedMobile = true;
            $user->last_login_at = Carbon::now();
            $user->save();

            $oldSessionId = session()->getId();

            if (session()->getId() !== $oldSessionId) {
                session()->put('prevSession', $oldSessionId);
            }

            auth('web')->login($user);

            session()->forget('userId');
            session()->forget('remember');

            Redis::del('2fa_token_' . $user->userId);

            return ResponseHelpers::jsonResponse(['message' => 'Код верификации успешно подтвержден', 'userId' => $user->userId, 'key' => encrypt($user->userId), 'twoFactor' => true], 200, true);

        } else {

            return ResponseHelpers::jsonResponse([
                'error' => 'Неверно введен код верификации'
            ], 400);
        }
    }


    /** User registration
     * [Регистрация пользоватя]
     *
     * @bodyParam email string required
     * @bodyParam password required
     * @bodyParam confirm_password required
     * @bodyParam firstname string required
     * @bodyParam lastname string required
     * @bodyParam middlename string
     * @bodyParam mobile string
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function registration(Request $request)
    {
        $user = \Auth::user('web');

        if ($user) {
            return ResponseHelpers::jsonResponse([], 403, true);
        }

        $validator = Validator::make($request->all(),
            [
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|min:6',
                'confirm_password' => 'required|min:6|same:password',
                'firstname' => 'required',
                'lastname' => 'required',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $userId = User::create([
            'login' => $request->email,
            'email' => $request->email,
            'userTypeId' => 0,
            'mobile' => isset($request->mobile) ? $request->mobile : '',
            'password' => app('hash')->make($request->password),
            'contacts' => [
                'contactEmails' => $request->email,
                'contactPhone' => isset($request->mobile) ? $request->mobile : '',
                'firstName' => $request->firstname,
                'lastName' => $request->lastname,
                'middleName' => isset($request->middlename) ? $request->middlename : '',
            ],
            'isVerifiedMobile' => 'false',
        ])->userId;

        if (!$userId) {
            return ResponseHelpers::jsonResponse([
                'error' => ''
            ], 500);
        }

        $data = [
            'login' => $request->email,
            'email' => $request->email,
            'password' => $request->password,
        ];

        QueueBalanced::balance(
            new EmailNotification(
                new Email(
                    $request->email,
                    'web@trivago.ru',
                    'Добро пожаловать на портал Trivago.ru',
                    $data,
                    'email.notification.registration'
                )
            ),
            'emails');

        return ResponseHelpers::jsonResponse(['message' => 'Регистрация успешно выполнена'], 200);
    }

    /**
     * @param $userId
     * @param $code
     */
    private function setAutSesions($userId,$code)
    {
        session()->put("userId", $userId);
        Redis::set('2fa_token_' . $userId, $code);
    }
}