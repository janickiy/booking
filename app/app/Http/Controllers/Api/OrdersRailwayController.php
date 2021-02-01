<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrdersRailway;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelpers;


/**
 * Class OrdersRailwayController
 * @group OrdersRailway
 * @package App\Http\Controllers\Api
 */
class OrdersRailwayController extends Controller
{
    /**
     * @param int $page
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function list($orderStatus,$page = 1)
    {
        $limit = 20;
        $offset = $limit * ($page-1);
        $userId = 0;

        $orders = OrdersRailway::where('userId', $userId)
            ->where('orderStatus', $orderStatus)
            ->offset($offset)
            ->limit($limit)
            ->orderBy('orderId','desc')
            ->get();

        if ($orders) {
            return ResponseHelpers::jsonResponse(['result' => true, 'passengers' => $orders->toArray()]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function destroy(Request $request)
    {
        $orderId = $request->orderId;

        if (OrdersRailway::where('orderId', $orderId)->delete()) {
            return ResponseHelpers::jsonResponse([
                'result' => true
            ], 200);
        } else {
            return ResponseHelpers::jsonResponse([
                'result' => false
            ], 500);
        }
    }
}