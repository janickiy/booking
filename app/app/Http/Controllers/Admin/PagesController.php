<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{Pages,Languages};
use Illuminate\Support\Facades\Validator;
use App\Helpers\StringHelpers;
use URL;

class PagesController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list()
    {
        return view('admin.pages.list')->with('title','Страницы и разделы');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $languages = Languages::hide()->orderBy('id')->get();

        $catalogs = Pages::orderBy('id')->published()->where('page_path', 'false')->get();
        $options = [];

        foreach ($catalogs as $catalog) {
            $title = StringHelpers::ObjectToArray($catalog->title);
            $options[$catalog->id] = $title['ru'];
        }

        return view('admin.pages.create_edit', compact('options', 'languages'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|array',
            'content' => 'array|nullable',
            'meta_title' => 'array|nullable',
            'meta_description' => 'array|nullable',
            'meta_keywords' => 'array|nullable',
            'slug' => 'required|unique:pages',
            'parent_id' => 'numeric|nullable'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {

            $published = 'false';

            if ($request->input('published')) {
                $published = 'true';
            }

            Pages::create(array_merge($request->all(), ['published' => $published]));
            Pages::flushCache(Pages::class);

            return redirect(URL::route('admin.pages.list'))->with('success', 'Данные добавлены');
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $pageData = Pages::find($id);

        if ($pageData) {
            $page_title = StringHelpers::ObjectToArray($pageData->title);
            $page_content = StringHelpers::ObjectToArray($pageData->content);
            $page_meta_title = StringHelpers::ObjectToArray($pageData->meta_title);
            $page_meta_description = StringHelpers::ObjectToArray($pageData->meta_description);
            $page_meta_keywords = StringHelpers::ObjectToArray($pageData->meta_keywords);
            $languages = Languages::hide()->orderBy('id')->get();
            $catalogs = Pages::orderBy('id')->published()->where('page_path', 'false')->get();
            $options = [];

            foreach ($catalogs as $catalog) {
                $title = StringHelpers::ObjectToArray($catalog->title);
                $options[$catalog->id] = $title['ru'];
            }

            return view('admin.pages.create_edit', compact('pageData', 'languages', 'options', 'page_title', 'page_content', 'page_meta_title', 'page_meta_description', 'page_meta_keywords'));
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
            'title' => 'required|array',
            'content' => 'array|nullable',
            'meta_title' => 'array|nullable',
            'meta_description' => 'array|nullable',
            'meta_keywords' => 'array|nullable',
            'slug' => 'required|unique:pages,slug,' . $request->id,
            'parent_id' => 'numeric|nullable'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {
            $data['title'] = json_encode($request->title, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $data['content'] = json_encode($request->input('content'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $data['meta_title'] = json_encode($request->meta_title, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $data['meta_description'] = json_encode($request->input('meta_description'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $data['meta_keywords'] = json_encode($request->input('meta_keywords'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $data['parent_id'] = $request->input('parent_id');
            $data['slug'] = $request->input('slug');
            $published = 'false';

            if ($request->input('published')) {
                $published = 'true';
            }

            $data['published'] = $published;

            $page_path = 'false';

            if ($request->input('page_path')) {
                $page_path = 'true';
            }

            $data['page_path'] = $page_path;

            Pages::where('id', $request->id)->update($data);
            Pages::flushCache(Pages::class);

            return redirect(URL::route('admin.pages.list'))->with('success', 'Данные обновлены');
        }
    }

    /**
     * @param $id
     */
    public function destroy($id)
    {
        Pages::where('id', $id)->delete();
        Pages::flushCache(Pages::class);
    }
}