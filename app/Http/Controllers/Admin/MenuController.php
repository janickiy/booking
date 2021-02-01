<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\{Menu, Languages, Pages};
use App\Helpers\StringHelpers;
use URL;

class MenuController extends Controller
{
    public function __construct()
    {

    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list()
    {
        $catalogs = Menu::orderBy('item_order')->get();
        $cats = [];

        if ($catalogs) {
            $catalog_arr = $catalogs->toArray();

            foreach ($catalog_arr as $catalog) {
                $cats_id[$catalog['id']][] = $catalog;
                $cats[$catalog['parent_id']][$catalog['id']] = $catalog;
            }
        }

        return view('admin.menu.list', compact('cats'))->with('title', 'Меню');
    }

    /**
     * @param int $parent_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create($parent_id = 0)
    {
        if (!is_numeric($parent_id)) abort(500);

        $languages = Languages::hide()->orderBy('id')->get();

        $options[0] = 'Выберите';
        $options = ShowTree($options, 0);
        $item_order = 0;

        if ($parent_id > 0) {
            $item_order = Menu::where('parent_id',$parent_id)->max('item_order');
            $item_order = $item_order + 1;
        }

        $catalogs = Pages::where('page_path', 'false')->get();
        $options_catalog = [];


        foreach($catalogs as $catalog) {
            $title = StringHelpers::ObjectToArray($catalog->title);
            $options_catalog[$catalog->id] = $title['ru'];
        }

        $articles = Pages::where('page_path', 'true')->get();
        $options_articles = [];

        foreach ($articles as $article) {
            $title = StringHelpers::ObjectToArray($article->title);
            $options_articles[$article->id] = $title['ru'];
        }

        return view('admin.menu.create_edit', compact('parent_id', 'options', 'item_order', 'options_catalog', 'options_articles', 'languages'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|array',
            'parent_id' => 'numeric',
            'item_order' => 'required|numeric',
            'item_id' => 'numeric|nullable'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {

            $status = 'false';

            if ($request->input('status')) {
                $status = 'true';
            }

            Menu::create(array_merge($request->all(), ['status' => $status]));
            Menu::flushCache(Menu::class);

            return redirect(URL::route('admin.menu.list'))->with('success', 'Информация успешно добавлена');
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        if (!is_numeric($id)) abort(500);

        $menu = Menu::where('id', $id)->first();

        if ($menu) {
            $languages = Languages::hide()->orderBy('id')->get();
            $menu_title = StringHelpers::ObjectToArray($menu->title);
            $options[0] = 'Выберите';
            $options = ShowTree($options, 0);
            $item_order = $menu->item_order;
            $parent_id = $menu->parent_id;

            $catalogs = Pages::where('page_path', 'false')->get();
            $options_catalog = [];

            foreach($catalogs as $catalog) {
                $title = StringHelpers::ObjectToArray($catalog->title);
                $options_catalog[$catalog->id] = $title['ru'];
            }

            $articles = Pages::where('page_path', 'true')->get();
            $options_articles = [];

            foreach ($articles as $article) {
                $title = StringHelpers::ObjectToArray($article->title);
                $options_articles[$article->id] = $title['ru'];
            }

            return view('admin.menu.create_edit', compact('menu', 'parent_id', 'options', 'item_order', 'options_catalog', 'options_articles', 'languages', 'menu_title'));
        }

        abort(404);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        if (!is_numeric($request->id)) abort(500);

        $rules = [
            'title' => 'required|array',
            'parent_id' => 'numeric',
            'item_order' => 'required|numeric',
            'item_id' => 'numeric|nullable'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {

            $data['title'] = json_encode($request->title, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $data['url'] = $request->url;
            $data['menu_type'] = $request->menu_type;
            $data['item_id'] = $request->item_id;
            $data['status'] = 'false';

            if ($request->input('status')) {
                $data['status'] = 'true';
            }

            $data['item_order'] = $request->item_order;
            $data['parent_id'] = $request->parent_id;

            Menu::where('id', $request->id)->update($data);
            Menu::flushCache(Menu::class);

            return redirect(URL::route('admin.menu.list'))->with('success', 'Данные обновлены');
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {

        $parent = Menu::findOrFail($id);
        $array_of_ids = $this->getChildren($parent);
        array_push($array_of_ids, $id);

        Menu::destroy($array_of_ids);
        Menu::flushCache(Menu::class);

        return redirect(URL::route('admin.menu.list'))->with('success', 'Данные удалены');
    }

    /**
     * @param $category
     * @return array
     */
    private function getChildren($category)
    {
        $ids = [];

        foreach ($category->children as $cat) {
            $ids[] = $cat->id;
            $ids = array_merge($ids, $this->getChildren($cat));
        }

        return $ids;
    }
}