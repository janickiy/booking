<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminApi\Offices\OfficeStoreRequest;
use App\Http\Requests\AdminApi\Offices\OfficeUpdateRequest;
use App\Repositories\OfficesRepository;
use App\Models\Office;
use URL;

class OfficesController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list()
    {
        return view('admin.offices.list')->with('title', 'Офисы');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('admin.offices.create_edit');
    }

    /**
     * @param OfficeStoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(OfficeStoreRequest $request)
    {
        $newOffice = $request->all();
        $newOffice['name'] = [
            'ru' => $request->input('titleRu'),
            'en' => $request->input('titleEn')
        ];
        $newOffice['city'] = [
            'ru' => $request->input('cityRu'),
            'en' => $request->input('cityEn')
        ];
        $newOffice['address'] = [
            'ru' => $request->input('addressRu'),
            'en' => $request->input('addressEn')
        ];
        $newOffice['contact_email'] = $request->input('contact_email');
        $newOffice['delivery_email'] = $request->input('delivery_email');
        $newOffice['phone'] = $request->input('phone');
        $newOffice['closed'] = $request->input('closed') ? 1 : 0;
        $newOffice['longitude'] = $request->input('longitude');
        $newOffice['latitude'] = $request->input('latitude');
        $newOffice['code'] = $request->input('code');
        $newOffice['fax'] = $request->input('fax');
        $newOffice['sms_phone'] = $request->input('sms_phone');
        $newOffice['iata_codes'] = $request->input('iata_codes');
        $newOffice['schedule'] = $request->input('schedule');
        $newOffice['city_id'] = null;
        $newOffice = Office::create($newOffice);
        if (!$newOffice) {
            return back()->withErrors(['Не удалось создать новый офис'])->withInput();
        }
        OfficesRepository::cacheClear();
        return redirect(URL::route('admin.offices.list'))->with('success', 'Данные добавлены');
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \ErrorException
     */
    public function edit($id)
    {
        $office =OfficesRepository::getOfficeById($id);
        if ($office) {
            return view('admin.offices.create_edit', compact('office'));
        }

        abort(404);
    }

    /**
     * @param OfficeUpdateRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(OfficeUpdateRequest $request)
    {
        /** @var $newOffice Office */
        $newOffice = Office::query()->findOrFail($request->input('id'));
        $newOffice->name = [
            'ru' => $request->input('titleRu'),
            'en' => $request->input('titleEn')
        ];
        $newOffice->city = [
            'ru' => $request->input('cityRu'),
            'en' => $request->input('cityEn')
        ];
        $newOffice->address = [
            'ru' => $request->input('addressRu'),
            'en' => $request->input('addressEn')
        ];
        $newOffice->contact_email = $request->input('contact_email');
        $newOffice->delivery_email = $request->input('delivery_email');
        $newOffice->phone = $request->input('phone');
        $newOffice->closed = $request->input('closed') ? 1 : 0;
        $newOffice->longitude = $request->input('longitude');
        $newOffice->latitude = $request->input('latitude');
        $newOffice->code = $request->input('code');
        $newOffice->fax = $request->input('fax');
        $newOffice->sms_phone = $request->input('sms_phone');
        $newOffice->iata_codes = $request->input('iata_codes');
        $newOffice->schedule = $request->input('schedule');
        $newOffice->save();
        OfficesRepository::cacheClear();
        if (!$newOffice) {
            return back()->withErrors(['Не удалось обновить информацию о офисе'])->withInput();
        }

        return redirect(URL::route('admin.offices.list'))->with('success', 'Данные обновлены');
    }

    /**
     * @param $id
     */
    public function destroy($id)
    {
        Office::where('id', $id)->delete();
        OfficesRepository::cacheClear();
    }
}
