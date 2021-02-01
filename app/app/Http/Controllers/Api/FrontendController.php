<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelpers;
use App\Models\{OrdersAeroexpress, Pages, Orders, OrdersRailway, OrderMessages};
use App\Helpers\DateTimeHelpers;
use App\Helpers\StringHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Services\External\InnovateMobility\v1\OrderReservation;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use App\Helpers\LangHelper;
use Cookie;

/**
 * Class FrontendController
 * [Работа с форонендом]
 * @group Frontend
 * @package App\Http\Controllers\Api
 */
class FrontendController extends Controller
{
    protected $app;

    protected $files;

    public function __construct( Application $app, Filesystem $files)
    {
        $this->app            = $app;
        $this->files          = $files;
    }

    /**
     * page
     * [Получаем список страниц и разделов]
     * @queryParam $slug
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function page($slug)
    {
        $page = Pages::whereSlug($slug)->published()->get()->first();

        if ($page) {
            $urls = [];

            if (isset($page->children)) {
                foreach ($page->children as $row) {
                    $urls[] = ['url' => url($row->urlPath), 'title' => $row->title];
                }
            }

            return ResponseHelpers::jsonResponse([
                'id' => $page->id,
                'meta_description' => $page->meta_description(),
                'meta_keywords' => $page->meta_keywords(),
                'meta_title' => $page->meta_title(),
                'title' => $page->title_page(),
                'content' => $page->content(),
                'urls' => $urls,
                'page_path' => $page->page_path,
                'parent_id' => $page->parent_id,
                'created_at' => $page->created_at,
                'updated_at' => $page->updated_at,

            ], 200, true);
        }

        return ResponseHelpers::jsonResponse(['error' => 'page not found'], 404);
    }

    /**
     * menu
     * [Меню]
     * @queryParam int $parent_id
     * @return mixed
     */
    public function menu($parent_id = 0)
    {
        $items = ShowTreeMenus($parent_id);

        return ResponseHelpers::jsonResponse([
            'items' => $items
        ], 200, true);
    }

    /**
     * orders
     * [Список заказов]
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function orders()
    {
        if (\Auth::check() === false) {
            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        $user = \Auth::user('web');
        $userId = $user->userId;

        $items = [];
        $orderItems = [];

        $orders = Orders::orderBy('id', 'DESC')
            ->where('userId', $userId)
            ->get();

        foreach ($orders as $order) {

            if (isset($order->orderItems) && $order->orderItems) {

                foreach ($order->orderItems as $row) {
                    if (isset($row->type) && $row->type == 'railway') {
                        $orderRailway = OrdersRailway::where('orderId', $row->id)->first();
                        $orderDetails = StringHelpers::ObjectToArray($orderRailway->orderData->result);

                        $ordersInfo = [];

                        if ($orderDetails['ReservationResults']) {
                            foreach ($orderDetails['ReservationResults'] as $detail) {
                                $ordersInfo[] = [
                                    'OriginStation' => isset($detail['OriginStation']) ? $detail['OriginStation'] : null,
                                    'DestinationStation' => isset($detail['DestinationStation']) ? $detail['DestinationStation'] : null,
                                    'OriginStationCode' => isset($detail['OriginStationCode']) ? $detail['OriginStationCode'] : null,
                                    'DestinationStationCode' => isset($detail['DestinationStationCode']) ? $detail['DestinationStationCode'] : null,
                                    'DepartureDateTime' => isset($detail['DepartureDateTime']) ? DateTimeHelpers::dateFormat(DateTimeHelpers::convertDate($detail['DepartureDateTime'])) : null,
                                    'ArrivalDateTime' => isset($detail['ArrivalDateTime']) ? DateTimeHelpers::dateFormat(DateTimeHelpers::convertDate($detail['ArrivalDateTime'])) : null,
                                    'LocalArrivalDateTime' => isset($detail['LocalArrivalDateTime']) ? $detail['LocalArrivalDateTime'] : null,
                                    'LocalDepartureDateTime' => isset($detail['LocalDepartureDateTime']) ? $detail['LocalDepartureDateTime'] : null,
                                    'ServiceClass' => isset($detail['ServiceClass']) ? $detail['ServiceClass'] : null,
                                    'CarDescription' => isset($detail['CarDescription']) ? $detail['CarDescription'] : null,
                                ];
                            }
                        }

                        $orderItems[] = [
                            'orderId' => $row->id,
                            'type' => $row->type,
                            'amount' => isset($orderDetails['Amount']) ? $orderDetails['Amount'] : 0.00,
                            'orderStatus' => OrdersRailway::$status_name[$orderRailway->orderStatus],
                            'orderDetails' => $ordersInfo
                        ];
                    }
                }
            }

            $items[] = [
                'id' => $order->id,
                'userId' => $userId,
                'userContacts' => isset($order->user->contacts) ? $order->user->contacts : null,
                'created_at' => $order->created_at,
                'created' => DateTimeHelpers::dateFormat(DateTimeHelpers::convertDate($order->created_at)),
                'orderItems' => $orderItems
            ];
        }

        return ResponseHelpers::jsonResponse([
            'items' => $items
        ], 200, true);
    }

    /**
     * orderInfo
     * [Инфрмация по заказу]
     * @queryParam $id ID заказа
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function orderInfo($id)
    {
        if (\Auth::check() === false) {
            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        $user = \Auth::user('web');
        $userId = $user->userId;

        $order = Orders::where('id', $id)->where('userId', $userId)->first();

        if (!$order) {
            return ResponseHelpers::jsonResponse([
                'error' => LangHelper::trans('errors.order_not_found')
            ], 404);
        }

        $items = [];

        if (isset($order->orderItems)) {
            foreach ($order->orderItems as $row) {

                switch ($row->type) {
                    case 'railway':
                        $orderRailway = OrdersRailway::where('orderId', $row->id)->where('userId', $userId)->first();

                        if ($orderRailway) {

                            $items[] = [
                                'holdingId' => $orderRailway->holdingId,
                                'complexOrderId' => $orderRailway->complexOrderId,
                                'orderStatus' => $orderRailway->orderStatus,
                                'status' => OrdersRailway::$status_name[$orderRailway->orderStatus],
                                'passengersData' => $orderRailway->passengersData,
                                'orderData' => isset($orderRailway->orderData->result) ? $orderRailway->orderData->result : null,
                                'provider' => $orderRailway->provider,
                                'orderDocuments' => $orderRailway->orderDocuments,
                                'Amount' => $orderRailway->Amount,
                                'created_at' => $orderRailway->created_at,
                                'created' => DateTimeHelpers::dateFormat(DateTimeHelpers::convertDate($orderRailway->created_at)),
                            ];
                        }

                        break;
                }
            }
        }

        return ResponseHelpers::jsonResponse([
            'items' => $items
        ], 200, true);
    }

    /**
     * messageSend
     * [Отпавить сообщение к заказу]
     * @bodyParam order_id integer required
     * @bodyParam order_item_id integer required
     * @bodyParam message string required nullable
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function messageSend(Request $request)
    {
        if (\Auth::check() === false) {
            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        $user = \Auth::user('web');
        $userId = $user->userId;

        $rules = [
            'order_id' => 'required|numeric',
            'order_item_id' => 'numeric|nullable',
            'message' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        } else {
            $id = OrderMessages::create(array_merge($request->all(), ['sender_id' => $userId, 'receiver_id' => 0, 'status' => 0]))->id;

            return ResponseHelpers::jsonResponse([
                'result' => true,
                'id' => $id
            ], 200);
        }
    }

    /**
     * OrderMessages
     * [получаем список сообщений к заказу]
     * @queryParam $order_id integer
     * @queryParam null $order_item_id integer
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getOrderMessages($order_id, $order_item_id = null)
    {
        if (\Auth::check() === false) {
            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        $user = \Auth::user('web');
        $userId = $user->userId;

        if ($order_item_id) {
            $messages = OrderMessages::whereIn('status', [0, 1, 3])->where('sender_id', $userId)->where('order_id', $order_id)->where('order_item_id', $order_item_id)->get();
        } else {
            $messages = OrderMessages::whereIn('status', [0, 1, 3])->where('sender_id', $userId)->where('order_id', $order_id)->get();
        }

        $items = [];

        foreach ($messages as $message) {
            $items[] = [
                'id' => $message->id,
                'order_id' => $message->order_id,
                'receiver' => isset($message->receiver->name) ? $message->receiver->name : null,
                'message' => $message->message,
                'created' => DateTimeHelpers::dateFormat($message->created_at),
            ];
        }

        return ResponseHelpers::jsonResponse([
            'result' => true,
            'messages' => $items
        ], 200);
    }

    /**
     * lang
     * [переключение языков]
     * @queryParam $locale string
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function lang($locale)
    {
        if (in_array($locale, \Config::get('app.locales'))) {
            Cookie::queue(
                Cookie::forever('lang', $locale));

            return ResponseHelpers::jsonResponse(['lang' => $locale, 'result' => true]);
        }

        return ResponseHelpers::jsonResponse([
            'result' => false, 'error' => 'Локализация не найдена'
        ], 404);
    }

    /**
     * PDFBlanks
     * [Получение бланков билетов]
     * @queryParam $order_id required Идентификатор заказа
     * @queryParam $order_item_id Идентификатор позиции (OrderItemId)
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function getPDFBlanks($order_id, $order_item_id = null)
    {
        if (\Auth::check() === false) {
            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        $user = \Auth::user('web');
        $userId = $user->userId;

        $orders = Orders::GetById($order_id)->first();

        if (!$orders) {
            return ResponseHelpers::jsonResponse([
                'error' => LangHelper::trans('errors.order_not_found')
            ], 404);
        }

        if ($userId != $orders->userId) {
            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Access is denied'], 403);
        }

        if ($orders->id == $order_id) {

            $headers = ["Content-Type" => "application/zip"];

            if (!Storage::disk('local')->exists('blanks/' . $order_id . '.zip')) {

                $zip = new \ZipArchive();

                $zip->open(Storage::disk('local')->path('blanks/' . $order_id . '.zip'), \ZipArchive::CREATE);

                foreach ($orders["orderItems"] as $order) {
                    $file_path = 'blanks/' . $order->type . '/blank_' . $order->id . '.pdf';

                    if (Storage::disk('local')->exists($file_path)) {
                        $zip->addFromString($order->type . '_' . $order->id . '.pdf', file_get_contents(Storage::disk('local')->path($file_path)));
                    } else {

                        switch ($order->type) {
                            case 'railway':

                                $response = OrderReservation::doBlank(
                                    [
                                        "OrderId" => $orders->getItemById($order->id)->orderData->orderId,
                                        'OrderItemId' => 0,
                                        "RetrieveMainServices" => true,
                                        "RetrieveUpsales" => true
                                    ]
                                );

                                if (!$response) {
                                    return ResponseHelpers::jsonResponse([
                                        'error' => OrderReservation::getLastError()
                                    ], 500);
                                }

                                $file_path = 'blanks/' . $order->type . '/blank_' . $order->id . '.pdf';

                                Storage::disk('local')->put($file_path, $response);

                                $zip->addFromString($order->type . '_' . $order->id . '.pdf', file_get_contents(Storage::disk('local')->path($file_path)));

                                break;
                        }
                    }
                }

                $zip->close();
            }

            return response()->download(Storage::disk('local')->path('blanks/' . $order_id . '.zip'), $order_id . '.zip', $headers);


        } else {

            $headers = ["Content-Type" => "application/pdf"];

            switch ($orders["orderItems"][0]->type) {
                case 'railway':

                    $ordersRailway = OrdersRailway::where('orderId', $order_id)->first();

                    if (!$ordersRailway) {
                        return ResponseHelpers::jsonResponse([
                            'error' => LangHelper::trans('errors.order_not_found')
                        ], 404);
                    }

                    $orderId = $ordersRailway->orderId;

                    $file_path = $order_item_id ? 'blanks/' . $orders::ORDER_TYPE_RAILWAY . '/blank_' . $orderId . '_' . $order_item_id . '.pdf' : 'blanks/' . $orders::ORDER_TYPE_RAILWAY . '/blank_' . $orderId . '.pdf';

                    if (!Storage::disk('local')->exists($file_path)) {

                        $response = OrderReservation::doBlank(
                            [
                                "OrderId" => $ordersRailway->orderData->orderId,
                                'OrderItemId' => $order_item_id,
                                "RetrieveMainServices" => true,
                                "RetrieveUpsales" => true
                            ]
                        );

                        if (!$response) {
                            return ResponseHelpers::jsonResponse([
                                'error' => OrderReservation::getLastError()
                            ], 500);
                        }

                        Storage::disk('local')->put($file_path, $response);
                    }

                    break;
                case 'aeroexpress':

                    $ordersAeroexpress = OrdersAeroexpress::where('orderId', $order_id)->first();

                    if (!$ordersAeroexpress) {
                        return ResponseHelpers::jsonResponse([
                            'error' => trans('errors.order_not_found')
                        ], 404);
                    }

                    $orderId = $ordersAeroexpress->orderId;

                    $file_path = $order_item_id ? 'blanks/' . $orders::ORDER_TYPE_AEROEXPRESS . '/blank_' . $orderId . '_' . $order_item_id . '.pdf' : 'blanks/' . $orders::ORDER_TYPE_AEROEXPRESS . '/blank_' . $orderId . '.pdf';

                    if (!Storage::disk('local')->exists($file_path)) {

                        $response = OrderReservation::doBlank(
                            [
                                "OrderId" => $ordersAeroexpress->orderData->orderId,
                                'OrderItemId' => $order_item_id,
                                "RetrieveMainServices" => true,
                                "RetrieveUpsales" => true
                            ]
                        );

                        if (!$response) {
                            return ResponseHelpers::jsonResponse([
                                'error' => OrderReservation::getLastError()
                            ], 500);
                        }

                        Storage::disk('local')->put($file_path, $response);
                    }

                    break;
            }

            return response()->download(Storage::disk('local')->path($file_path), basename($file_path), $headers);
        }
    }

    /**
     * References
     * [Получение спарвочной информации]
     * @queryParam section Получить только определенную секцию справочника
     * @param bool $section
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getReferences()
    {
        $base = $this->app['path.lang'];
        $references = [];

        foreach ($this->files->directories($base) as $row) {
            $locale = basename($row);

            if ($locale == config('app.locale')) {

                $references = self::dirToArray($row);

                break;
            }
        }

        return ResponseHelpers::jsonResponse($references);
    }

    /**
     * @param $dir
     * @param null $path
     * @return array
     */
    protected static function dirToArray($dir, $path = null) {

        $references = [];

        $rows = scandir($dir);

        foreach ($rows as $row) {
            if (!in_array($row, array(".", ".."))) {

                if (is_dir($dir . DIRECTORY_SEPARATOR . $row)) {
                    $references[$row] = self::dirToArray($dir . DIRECTORY_SEPARATOR . $row, $row);
                } else {

                    if ($path)
                        $list = trans($path . '/'. pathinfo($row)['filename']);
                    else
                        $list = trans(pathinfo($row)['filename']);

                    if ($list) $references[pathinfo($row)['filename']] = $list;
                }
            }
        }

        return $references;
    }
}