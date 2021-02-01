<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 29.03.2019
 * Time: 11:46
 */

namespace App\Http\Controllers\Admin;


use App\Models\References\RailwayStation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class RailwayStationsController extends Controller
{
    public function list()
    {
        return view('admin.stations.list')->with('title', 'Станции');
    }

    public function edit($id)
    {
        $station = RailwayStation::find($id);
        if (!$station) abort(404);

        return view('admin.stations.edit', ['station' => $station]);
    }

    public function update(Request $request)
    {
        $id = $request->get('railwayStationId');
        $station = RailwayStation::find($id);
        if(!$station) abort(404);

        $custom = $station->custom;

        $custom->nameRu = $request->get('customNameRu', '');
        $custom->nameEn = $request->get('customNameEn', '');

        $station->custom = $custom;
        $station->save();

        RailwayStation::flushCache(RailwayStation::class);

        return redirect(URL::route('admin.stations.list'))->with('success', 'Данные обновлены');
    }
}