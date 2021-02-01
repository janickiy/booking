<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 05.07.2018
 * Time: 10:07
 */

namespace App\Http\Controllers\Api;


use App\Helpers\ResponseHelpers;
use App\Helpers\XMLHelpers;
use App\Http\Controllers\Controller;
use App\Jobs\Soap1CRailwayOrderPush;
use App\Models\Old\News\CorpNews;
use App\Models\OrdersRailway;
use App\Services\External\Soap1c\v2\RailwayOrder;
use App\Services\QueueBalanced;
use App\Services\SessionLog;

class TestController extends Controller
{

    public function checkAfterMiddleware()
    {
        $page = 1;
        $limit = CorpNews::PER_PAGE;
        $offset = CorpNews::PER_PAGE * ($page - 1);

        $news = SessionLog::logQuery(CorpNews::class, function () use ($limit, $offset) {
            return CorpNews::published($limit, $offset)->get();
        });

        $data = [];

        foreach ($news as $n) {
            $data[] = [
                'id' => $n->id,
                'date' => date('d.m.Y', $n->date),
                'title' => $n->title
            ];
        }

        return ResponseHelpers::jsonResponse($data);
    }

    public function test()
    {
        echo '123';
    }


}