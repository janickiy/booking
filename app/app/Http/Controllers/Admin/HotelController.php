<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Admin\Hotel\{AdminHotel,HotelRegion,AdminHotelProvider,AdminHotelNormalize};
use Illuminate\Support\Facades\Validator;
use URL;

class HotelController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list()
    {
        return view('admin.hotel.hotel.list')->with('title', 'Список отелей');
    }


    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $hotel = AdminHotel::where('id', $id)->first();

        if (!$hotel) abort(404);

        $region = HotelRegion::find($hotel->region_id);

        $options = isset($region) ? [$region->id => $region->name_ru] : [];

        return view('admin.hotel.hotel.create_edit', compact('hotel','options'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $id = $request->id;

        if (!is_numeric($id)) abort(500);

        $validator = Validator::make($request->all(), [
            'name_ru' => 'required',
            'address_ru' => 'required',
            'region_id' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data['name_ru'] = $request->input('name_ru');
        $data['name_en'] = $request->input('name_en');
        $data['address_ru'] = $request->input('address_ru');
        $data['address_en'] = $request->input('address_en');
        $data['region_id'] = $request->input('region_id');

        AdminHotel::where('id', $id)->update($data);

        return redirect(URL::route('admin.hotel.list'))->with('success', 'Данные обновлены');
    }

    /**
     * @param $id
     */
    public function destroy($id)
    {
        AdminHotel::where('id', $id)->delete();
        AdminHotelProvider::where('hotel_id', $id)->delete();
        AdminHotelNormalize::where('hotel_id', $id)->delete();
    }
}