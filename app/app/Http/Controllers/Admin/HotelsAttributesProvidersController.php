<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Admin\Hotel\{HotelsAttributesProviders};
use Illuminate\Support\Facades\Validator;
use URL;

class HotelsAttributesProvidersController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list($attribute_id)
    {
        return view('admin.hotel.attributes_providers.list', compact('attribute_id'))->with('title', 'Атрибуты провайдеров');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create($attribute_id)
    {
        return view('admin.hotels_attributes_providers.create_edit',compact('attribute_id'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $rules = [
            'attribute_id' => 'required|numeric',
            'provider' => 'required',
            'type' => 'required',
            'code' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        HotelsAttributesProviders::create($request->all());

        return redirect(URL::route('admin.hotels_attributes_providers.list'))->with('success', 'Информация успешно добавлена');
    }

    /**
     * @param $attribute_id
     * @param $type
     * @param $code
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($attribute_id,$type,$code)
    {
        $attribute = HotelsAttributesProviders::where('attribute_id', $attribute_id)->where('type',$type)->where('code',$code)->first();

        if (!$attribute) abort(404);

        return view('admin.hotel.attributes_providers.create_edit', compact('attribute','attribute_id'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'attribute_id' => 'required|numeric',
            'provider' => 'required',
            'type' => 'required',
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data['attribute_id'] = $request->input('attribute_id');
        $data['provider'] = $request->input('provider');
        $data['type'] = $request->input('type');
        $data['code'] = $request->input('code');

        HotelsAttributesProviders::where('attribute_id', $request->input('attribute_id'))->update($data);

        return redirect(URL::route('admin.hotels_attributes_providers.list'))->with('success', 'Данные обновлены');
    }

    /**
     * @param $id
     * @param $type
     * @param $code
     */
    public function destroy($id,$type,$code)
    {
        HotelsAttributesProviders::where('attribute_id', $id)->where('type',$type)->where('code',$code)->delete();
    }
}