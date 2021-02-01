<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\References\Trains;
use Illuminate\Support\Facades\Validator;
use URL;

class TrainsController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * Список поездов
     */
    public function list()
    {
        return view('admin.trains.list')->with('title', 'Поезда');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * форма добавления поезда
     */
    public function create()
    {
        return view('admin.trains.create_edit');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * Добавляем поезд
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'trainNumber' => 'required|unique:trains,trainNumber',
                'trainNumberToGetRoute'  => 'required',
                'trainName' => 'required',
                'carriers' => 'required'
            ],
            [
                'trainNumber.required' => 'Укажите номер поезда!',
                'trainNumberToGetRoute.required' => 'Укажите номер маршрута поезда!',
                'trainName.required' => 'Укажите название!',
                'carriers.required' => 'Укажите перевозчиков!',
            ]
        );

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {
            $carriers = explode(",", $request->carriers);

            Trains::create(array_merge($request->all(), ['carriers' => $carriers, 'isAddedManually' => true]));

            return redirect(URL::route('admin.trains.list'))->with('success', 'Тип вагона добавлен');
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * форма редактирования поезда
     */
    public function edit($id)
    {
        $train = Trains::where('id', $id)->first();

        if ($train) {
            return view('admin.trains.create_edit', compact('train'));
        }

        abort(404);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * обнавляем данные поезда
     */
    public function update(Request $request)
    {
        $id = $request->id;

        $validator = Validator::make($request->all(),
            [
                'trainNumber' => 'required|unique:trains,trainNumber,' . $request->id,
                'trainNumberToGetRoute'  => 'required',
                'trainName' => 'required',
                'carriers' => 'required'
            ],
            [
                'trainNumberToGetRoute.required' => 'Укажите номер маршрута поезда!',
                'trainNumber.required' => 'Укажите номер поезда!',
                'trainName.required' => 'Укажите название!',
                'carriers.required' => 'Укажите перевозчиков!',
            ]
        );

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {
            $data['trainNumber'] = $request->trainNumber;
            $data['trainName'] = $request->trainName;
            $data['trainDescription'] = $request->trainDescription;
            $data['trainNumberToGetRoute'] = $request->trainNumberToGetRoute;
            $data['carriers'] = json_encode(explode(",", $request->carriers), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            Trains::where('id', $id)->update($data);

            return redirect(URL::route('admin.trains.list'))->with('success', 'Данные обновлены');
        }
    }

    /**
     * @param Request $request
     */
    public function destroy($id)
    {
        Trains::where('id',$id)->delete();
    }
}