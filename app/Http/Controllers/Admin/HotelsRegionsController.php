<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Admin\Hotel\{HotelRegion};
use Illuminate\Support\Facades\Validator;
use Trivago\Hotels\Lib\Provider\GdsRegion;
use URL;

class HotelsRegionsController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list($parent_id = 0)
    {
        return view('admin.hotel.regions.list',['parent_id' => $parent_id])->with('title', 'Регионы');
    }

    /**
     * @param int $parent_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create($parent_id = 0)
    {
        $options[GdsRegion::TYPE['COUNTRY']] = GdsRegion::TYPE['COUNTRY'];
        $options[GdsRegion::TYPE['CITY']] = GdsRegion::TYPE['CITY'];
        $options[GdsRegion::TYPE['PLACE']] = GdsRegion::TYPE['PLACE'];
        $options[GdsRegion::TYPE['AIRPORT']] = GdsRegion::TYPE['AIRPORT'];

        $region_options = [];

        return view('admin.hotel.regions.create_edit', compact('parent_id','options', 'region_options'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $rules = [
            'name_ru' => 'required',
            'name_en' => 'required',
            'type'  => 'required',
            'parent_id' => 'numeric',
            'slug_ru' => 'required',
            'slug_en' => 'required',
            'popularity' => 'numeric',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $is_sng = 'false';

        if ($request->input('is_sng')) {
            $is_sng = 'true';
        }

        HotelRegion::create(array_merge($request->all(), ['is_sng' => $is_sng]));

        return redirect(URL::route('admin.hotels_regions.list'))->with('success', 'Информация успешно добавлена');
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $region = HotelRegion::where('id', $id)->first();

        if (!$region) abort(404);

        $options[GdsRegion::TYPE['COUNTRY']] = GdsRegion::TYPE['COUNTRY'];
        $options[GdsRegion::TYPE['CITY']] = GdsRegion::TYPE['CITY'];
        $options[GdsRegion::TYPE['PLACE']] = GdsRegion::TYPE['PLACE'];
        $options[GdsRegion::TYPE['AIRPORT']] = GdsRegion::TYPE['AIRPORT'];

        if($region->parent_id)
            $region_options[$region->parent_id] = $region->parent->name_ru;
        else
            $region_options = [];

        return view('admin.hotel.regions.create_edit', compact('region','options','region_options'));
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
            'name_en' => 'required',
            'type'  => 'required',
            'parent_id' => 'numeric',
            'slug_ru' => 'required',
            'slug_en' => 'required',
            'popularity' => 'numeric',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data['name_ru'] = $request->input('name_ru');
        $data['name_ru'] = $request->input('name_ru');
        $data['type'] = $request->input('type');
        $data['parent_id'] = $request->input('parent_id');
        $data['slug_en'] = $request->input('slug_en');
        $data['slug_ru'] = $request->input('slug_ru');
        $data['parent_slug'] = $request->input('parent_slug');
        $data['latitude'] = $request->input('latitude');
        $data['longitude'] = $request->input('longitude');

        $is_sng = 'false';

        if ($request->input('is_sng')) {
            $is_sng = 'true';
        }

        $data['is_sng'] = $is_sng;

        HotelRegion::where('id', $id)->update($data);

        return redirect(URL::route('admin.hotels_regions.list'))->with('success', 'Данные обновлены');
    }

    /**
     * @param $id
     */
    public function destroy($id)
    {
        HotelRegion::where('id', $id)->delete();
    }
}