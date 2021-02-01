<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Hotel\{HotelsCitizenship, HotelsOffers, HotelRegion, HotelOrders};
use App\Helpers\StringHelpers;
use URL;

class HotelsOrdersController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list()
    {
        return view('admin.hotel.orders.list')->with('title', 'Заказы');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function offers($id)
    {
        $offer = HotelsOffers::find($id);

        if (!$offer) abort(404);

        $params = StringHelpers::ObjectToArray($offer->params);
        $target = StringHelpers::ObjectToArray($offer->target);
        $options = StringHelpers::ObjectToArray($offer->options);
        $provider = $offer->provider;

        $region = null;

        if (isset($target['region_id'])) {
            $region = HotelRegion::select('name_ru')->find($target['region_id']);
        }

        return view('admin.hotel.orders.offers', compact('params', 'target', 'provider', 'region', 'options'))->with('title', 'Предложение');
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function guests($id)
    {
        $order = HotelOrders::find($id);


        if (!$order) abort(404);

        $guests = StringHelpers::ObjectToArray($order->guests);

        return view('admin.hotel.orders.guests', compact('guests'))->with('title', 'Гости');
    }


}