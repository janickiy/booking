<?php

namespace App\Helpers;

use App\Services\Messages\Email;
use App\Jobs\{EmailNotification, SmsNotification};
use App\Services\QueueBalanced;
use Illuminate\Support\Facades\Storage;
use App\Services\Messages\Sms;
use App\Services\External\InnovateMobility\v1\OrderReservation;
use App\Models\{Orders, OrdersAeroexpress, OrdersRailway, User, PasswordReset};
use App\Services\Settings;
use URL;

class NotificationHelpers
{
    /**
     * @param $id
     * @return int
     */
    public static function PayAllOrders($id, $email)
    {
        $orders = Orders::where('id', $id)->first();

        $items = [];
        $amount = 0.00;
        $blanks = [];

        foreach ($orders->orderItems as $row) {
            switch ($row->type) {
                case 'railway':
                    $ordersRailway = OrdersRailway::where('orderId', $row->id)->first();
                    $result = isset($ordersRailway->orderData->result) ? StringHelpers::ObjectToArray($ordersRailway->orderData->result) : null;
                    $items[] = $result['ReservationResults'];
                    $amount += $ordersRailway->Amount;

                    $blanks[] = self::getBlanks($result['OrderId'], $id, $row->type);

                    break;

                case 'aeroexpress':
                    $ordersRailway = OrdersAeroexpress::where('orderId', $id)->first();
                    $result = isset($ordersRailway->orderData->result) ? StringHelpers::ObjectToArray($ordersRailway->orderData->result) : null;
                    $items[] = $result['ReservationResults'];
                    $amount += $ordersRailway->Amount;

                    $blanks[] = self::getBlanks($result['OrderId'], $id, $row->type);

                    break;
            }
        }

        $settings = new Settings();
        $totalTax = (float)$settings->get('taxRailwayImRzdPurchase') + (float)$settings->get('taxRailwayImTrivagoPurchase');

        $orderData = [
            'email' => $email,
            'items' => $items,
            'complexOrderId' => $id,
            'orderId' => $id,
            'Amount' => $amount,
            'Tax' => $totalTax,
            'Сommission' => 0,
            'blanks' => $blanks,
        ];

        return QueueBalanced::balance(
            new EmailNotification(
                new Email(
                    $orderData['email'],
                    'web@trivago.ru',
                    'Ваш заказ оформлен',
                    ['orders' => $orderData],
                    'email.notification.pay',
                    $orderData['blanks'] ? $orderData['blanks'] : []
                )
            ),
            'emails');
    }

    /**
     * @param $id
     * @param $type
     * @param $email
     * @return int
     */
    public static function CreateOrder($id, $type, $email)
    {
        $items = [];
        $amount = 0.00;

        switch ($type) {
            case 'railway':
                $ordersRailway = OrdersRailway::where('orderId', $id)->first();
                $complexOrderId = StringHelpers::ObjectToArray($ordersRailway->complexOrderId);
                $result = isset($ordersRailway->orderData->result) ? StringHelpers::ObjectToArray($ordersRailway->orderData->result) : null;
                $items[] = $result['ReservationResults'];
                $amount += $ordersRailway->Amount;

                break;

            case 'aeroexpress':
                $ordersRailway = OrdersAeroexpress::where('orderId', $id)->first();
                $complexOrderId = StringHelpers::ObjectToArray($ordersRailway->complexOrderId);
                $result = isset($ordersRailway->orderData->result) ? StringHelpers::ObjectToArray($ordersRailway->orderData->result) : null;
                $items[] = $result['ReservationResults'];
                $amount += $ordersRailway->Amount;

                break;
        }

        $settings = new Settings();
        $totalTax = (float)$settings->get('taxRailwayImRzdPurchase') + (float)$settings->get('taxRailwayImTrivagoPurchase');

        $orderData = [
            'email' => $email,
            'items' => $items,
            'complexOrderId' => $complexOrderId,
            'orderId' => $complexOrderId,
            'Amount' => $amount,
            'Tax' => $totalTax,
            'Сommission' => 0,
            'blanks' => [],
        ];

        return QueueBalanced::balance(
            new EmailNotification(
                new Email(
                    $orderData['email'],
                    'web@trivago.ru',
                    'Ваш заказ оформлен',
                    ['orders' => $orderData],
                    'email.notification.create_order',
                    $orderData['blanks'] ? $orderData['blanks'] : []
                )
            ),
            'emails');
    }

    /**
     * @param $id
     * @param $type
     * @return int
     */
    public static function PayOneOrder($id, $type, $email)
    {
        $items = [];
        $amount = 0.00;
        $blanks = [];

        switch ($type) {
            case 'railway':
                $ordersRailway = OrdersRailway::where('orderId', $id)->first();
                $complexOrderId = StringHelpers::ObjectToArray($ordersRailway->complexOrderId);
                $result = isset($ordersRailway->orderData->result) ? StringHelpers::ObjectToArray($ordersRailway->orderData->result) : null;
                $items[] = $result['ReservationResults'];
                $amount += $ordersRailway->Amount;

                $blanks[] = self::getBlanks($result['OrderId'], $id, $type);

                break;

            case 'aeroexpress':
                $ordersRailway = OrdersAeroexpress::where('orderId', $id)->first();
                $complexOrderId = StringHelpers::ObjectToArray($ordersRailway->complexOrderId);
                $result = isset($ordersRailway->orderData->result) ? StringHelpers::ObjectToArray($ordersRailway->orderData->result) : null;
                $items[] = $result['ReservationResults'];
                $amount += $ordersRailway->Amount;

                $blanks[] = self::getBlanks($result['OrderId'], $id, $type);

                break;
        }

        $settings = new Settings();
        $totalTax = (float)$settings->get('taxRailwayImRzdPurchase') + (float)$settings->get('taxRailwayImTrivagoPurchase');

        $orderData = [
            'email' => $email,
            'items' => $items,
            'complexOrderId' => $complexOrderId,
            'orderId' => $complexOrderId,
            'Amount' => $amount,
            'Tax' => $totalTax,
            'Сommission' => 0,
            'blanks' => $blanks,
        ];

        return QueueBalanced::balance(
            new EmailNotification(
                new Email(
                    $orderData['email'],
                    'web@trivago.ru',
                    'Ваш заказ оплачен',
                    ['orders' => $orderData],
                    'email.notification.pay',
                    $orderData['blanks'] ? $orderData['blanks'] : []
                )
            ),
            'emails');
    }

    /**
     * @param $link
     * @param $email
     * @return int
     */
    public static function RegistrationViaOrder($userId)
    {
        $userData = User::find($userId);

        if ($userData && $userData->userTypeId == 1) {
            $token = str_random(20);
            $reset_password_link = URL::route('reset_password', ['token' => $token]);

            PasswordReset::where('userId', $userId)->delete();
            PasswordReset::create(['token' => $token, 'userId' => $userId]);

            return QueueBalanced::balance(
                new EmailNotification(
                    new Email(
                        $userData->email,
                        'web@trivago.ru',
                        'Добро пожаловать на портал Trivago.ru',
                        ['link' => $reset_password_link, 'email' => $userData->email],
                        'email.notification.registration_via_order'
                    )
                ),
                'emails');
        }
    }

    /**
     * @param $orderId
     * @param $id
     * @param $type
     * @return array
     */
    private static function getBlanks($orderId, $id, $type)
    {
        $blank = null;

        $response = OrderReservation::doBlank(
            [
                "OrderId" => $orderId,
                'OrderItemId' => 0,
                "RetrieveMainServices" => true,
                "RetrieveUpsales" => true
            ]
        );

        if ($response) {
            $file_path = 'blanks/' . $type . '/blank_' . $id . '.pdf';
            Storage::disk('local')->put($file_path, $response);

            if (Storage::disk('local')->exists($file_path)) $blank = Storage::disk('local')->path($file_path);
        }

        return $blank;
    }

    /**
     * @param $email
     * @param $data
     */
    public static function Email2FactorNotification($email, $data)
    {
        QueueBalanced::balance(
            new EmailNotification(
                new Email(
                    $email,
                    'web@trivago.ru',
                    'Ваш код подтверждения Trivago.ru',
                    $data,
                    'email.notification.twofactor'
                )
            ),
            'emails');
    }

    /**
     * @param $phone
     * @param $code
     */
    public static function Sms2FactorNotification($phone, $code)
    {
        QueueBalanced::balance(
            new SmsNotification(
                new Sms($phone, 'Ваш код подтверждения: ' . $code . ' Наберите его в поле ввода.')
            ),
            'sms');
    }
}