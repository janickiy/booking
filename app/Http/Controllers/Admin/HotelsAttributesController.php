<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Admin\Hotel\{HotelsAttributes,HotelsAttributesProviders};
use Illuminate\Support\Facades\Validator;
use URL;

class HotelsAttributesController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list()
    {
        return view('admin.hotel.attributes.list')->with('title', 'Атрибуты');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('admin.hotel.attributes.create_edit');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $rules = [
            'name_ru' => 'required',
            'type' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        HotelsAttributes::create($request->all());

        return redirect(URL::route('admin.hotels_attributes.list'))->with('success', 'Информация успешно добавлена');
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $attribute = HotelsAttributes::where('id', $id)->first();

        if (!$attribute) abort(404);

        return view('admin.hotel.attributes.create_edit', compact('attribute'));
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
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data['name_ru'] = $request->input('name_ru');
        $data['name_en'] = $request->input('name_en');
        $data['type'] = $request->input('type');

        HotelsAttributes::where('id', $id)->update($data);

        return redirect(URL::route('admin.hotels_attributes.list'))->with('success', 'Данные обновлены');
    }

    /**
     * @param $id
     */
    public function destroy($id)
    {
        HotelsAttributes::where('id', $id)->delete();
        HotelsAttributesProviders::where('attribute_id', $id)->delete();
    }
}