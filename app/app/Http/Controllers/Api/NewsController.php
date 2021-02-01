<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 05.02.2018
 * Time: 10:25
 */

namespace App\Http\Controllers\Api;


use App\Helpers\ResponseHelpers;
use App\Http\Controllers\Controller;
use App\Models\Old\News\CorpNews;
use App\Services\SessionLog;

class NewsController extends Controller
{
    public function list($page=1)
    {
        $limit = CorpNews::PER_PAGE;
        $offset = CorpNews::PER_PAGE * ($page-1);

        $news = SessionLog::logQuery(CorpNews::class, function()use($limit, $offset){
            return CorpNews::published($limit, $offset)->get();
        });

        $data = [];

        foreach ($news as $n){
            $data[] = [
                'id' => $n->id,
                'date' => date('d.m.Y', $n->date),
                'title' => $n->title
            ];
        }

        return ResponseHelpers::jsonResponse($data);
    }
}