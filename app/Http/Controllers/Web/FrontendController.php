<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pages;
use Illuminate\Http\Request;
use App\Models\{Orders,OrdersRailway,PasswordReset,User};
use Session;
use Cookie;

class FrontendController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('index')->with('title','О компании');
    }

    /**
     * @param $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function lang($locale)
    {
        if (in_array($locale, \Config::get('app.locales'))) {
            Cookie::queue(
                Cookie::forever('lang', $locale));
        }

        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function avia(Request $request)
    {
        return view('avia')->with('title','Авиа');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function transfer(Request $request)
    {
        return view('transfer')->with('title','Трансфер');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function trains(Request $request)
    {
        return view('trains')->with('title','Поезда');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function hotels(Request $request)
    {
        return view('hotels')->with('title','Отели');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function auto(Request $request)
    {
        return view('auto')->with('title','Авто');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function insurance(Request $request)
    {
        return view('insurance')->with('title','Страховка');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function mice(Request $request)
    {
        return view('mice')->with('title','MICE');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function contacts(Request $request)
    {
        return view('contacts')->with('title','Контакты');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function support(Request $request)
    {
        return view('support')->with('title','Поддержка');
    }

    /**
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function page($slug)
    {
        $page = Pages::whereSlug($slug)->published()->get()->first();

        if ($page) {
            return view('page', compact('page'))->with('title', $page->title);
        }

        abort(404);
    }

    /**
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function path($slug)
    {
        $path = Pages::whereSlug($slug)->where('page_path',0)->published()->get()->first();

        if ($path) {
            return view('path', compact('path'))->with('title', $path->title);
        }

        abort(404);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function profile()
    {
        return view('profile.index')->with('title','Личный кабинет');
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function order($id)
    {
        $user = \Auth::user('web');

        $order = Orders::where('id',$id)->where('userId', $user->userId)->first();

        if (!$order) abort(404);

        $orders = [];

        foreach ($order->orderItems as $item) {
            switch ($item->type) {
                case "railway":
                    $orders[] = OrdersRailway::where('orderId', $item->id)->where('userId', $user->userId)->first();
                    break;
            }
        }

        return view('profile.order', compact('orders'))->with('title','Заказы');
    }

    /**
     * [форма изменения пароля]     *
     * @param $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function resetPassword($token)
    {
        $result = PasswordReset::where('token',$token)->first();

        if ($result) {
            return view('profile.reset_password', compact($token))->with('title','Изменение пароля');
        }

        abort(404);
    }

    /**
     * форма восстанровления пароля
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function recoverPassword()
    {
        $user = \Auth::user('web');

        if ($user) {
            return redirect(url('/'));
        }

        return view('profile.recover_password')->with('title','Восстановлени пароля');
    }

    /**
     * verifiedMobileForm
     * [Страница верификация номера телефона]
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function verifiedMobileForm()
    {
        $user = \Auth::user('web');

        if ($user) {
            return redirect(url('/'));
        }

        return view('profile.verified_mobile')->with('title','Верификация номера телефона');
    }

    /**
     * verified2faForm
     * [Форма 2х факторной авторизации]
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function verified2faForm()
    {
        $user = \Auth::user('web');

        if ($user) {
            return redirect(url('/'));
        }

        return view('profile.2fa_form')->with('title','2х факторная авторизация');
    }
}
