<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\TManager;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\TranslationManager\Manager;
use URL;

class TManagerController extends Controller
{

    protected $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
        parent::__construct();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list()
    {
        return view('admin.tmanager.list')->with('title','Менеджер переводов');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('admin.tmanager.create_edit');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'locale' => 'required',
            'group' => 'required',
            'key' => 'required',
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {

            TManager::create(array_merge($request->all()));
            TManager::flushCache(TManager::class);

            return redirect(URL::route('admin.tmanager.list'))->with('success', 'Данные добавлены');
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $tmanager = TManager::find($id);

        if ($tmanager) {
            return view('admin.tmanager.create_edit', compact('tmanager'));
        }

        abort(404);
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
            'locale' => 'required',
            'group' => 'required',
            'key' => 'required',
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {
            $data['locale'] = $request->input('locale');
            $data['group'] = $request->input('group');
            $data['key'] = $request->input('key');
            $data['value'] = $request->input('value');

            TManager::where('id', $request->id)->update($data);
            TManager::flushCache(TManager::class);

            return redirect(URL::route('admin.tmanager.list'))->with('success', 'Данные обновлены');
        }
    }

    /**
     * @param $id
     */
    public function destroy($id)
    {
        TManager::where('id', $id)->delete();
        TManager::flushCache(TManager::class);
    }
}