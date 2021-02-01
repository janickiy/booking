<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminApi\TrainsCar\TrainCarStoreUpdateRequest;
use App\Models\References\Trains;
use Illuminate\Http\Request;
use App\Models\References\TrainsCar;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class TrainsCarController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     */
    public function list()
    {
        return view('admin.trains_car.list')->with('title','Типы вагонов');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     */
    public function create()
    {
        $options = self::getTrainsHtmlSelectOptionsList();
        return view('admin.trains_car.create_edit', compact('options'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(TrainCarStoreUpdateRequest $request)
    {
        $schemes = $this->handleSchemesUpload($request);
        $data = $request->all();
        $data['trainName'] = !empty($request->get('trainName')) ? $request->get('trainName'):'';
        TrainsCar::create(array_merge($data,['schemes' => $schemes, 'isAddedManually' => true]));

        return redirect(URL::route('admin.trainscar.list'))->with('success', 'Тип вагона добавлен');
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $trainsCar = TrainsCar::where('id',$id)->first();
        if (!$trainsCar) abort(404);

        $options = self::getTrainsHtmlSelectOptionsList();
        return view('admin.trains_car.create_edit', compact('trainsCar', 'options'));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(TrainCarStoreUpdateRequest $request)
    {
        $id = $request->get('id');
        $trainCar = TrainsCar::find($id);
        if (!$trainCar) abort(404);

        $trainCar->schemes = $this->handleSchemesUpload($request);
        $trainCar->typeRu = $request->get('typeRu');
        $trainCar->typeEn = $request->get('typeEn');
        $trainCar->description = $request->get('description');
        $trainCar->typeScheme = $request->get('typeScheme');
        if ($request->has('train_id'))
            $trainCar->train_id = $request->get('train_id');
        if($request->has('trainName') && !empty($request->get('trainName')))
            $trainCar->trainName = $request->get('trainName');

        $trainCar->save();
        return redirect(URL::route('admin.trainscar.list'))->with('success', 'Данные обновлены');
    }

    /**
     * TODO: вернуть ответ + когда удаляем тип вагона - удаляем связанные схемы из поля schemes
     * @param $id
     */
    public function destroy($id)
    {
        $trainsCar = TrainsCar::where('id', $id)->first();

        if ($trainsCar) {
            if (Storage::exists($trainsCar->scheme)) Storage::delete($trainsCar->scheme);
        }

        TrainsCar::where(['id' => $id])->delete();
    }

    /**
     * Загружает схемы и возвращает массив или null для поля schemes
     * @param Request $request
     * @return array|null
     */
    private function handleSchemesUpload(Request $request) {
        $resultSchemes = [];
        $schemesFiles = $request->file('schemes');
        $schemesKeys = $request->get('schemes');

        if ($schemesKeys) {
            foreach ($schemesKeys as $num => $scheme) {
                $key = $scheme['key'];
                $path = $scheme['file_path'];
                if (isset($schemesFiles[$num]['file'])) {
                    $path = $schemesFiles[$num]['file']->store('scheme');
                }
                $resultSchemes[$key] = $path;
            }
        } else {
            $resultSchemes = null;
        }

        return $resultSchemes;
    }

    /**
     * Генерирует список поездов для HTML select тега
     * @return mixed
     */
    private static function getTrainsHtmlSelectOptionsList() {
        $trains = Trains::get();

        return $trains->mapWithKeys(function ($train) {
            $label = $train->trainNumber;
            if ($train->trainName) $label .= " ({$train->trainName})";
            return [$train->id => $label];
        });
    }
}