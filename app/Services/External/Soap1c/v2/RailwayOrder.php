<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 07.02.2019
 * Time: 11:58
 */

namespace App\Services\External\Soap1c\v2;


use App\Helpers\XMLHelpers;
use App\Models\OrdersRailway;
use App\Services\External\InnovateMobility\v1\OrderInfo;
use App\Services\External\Soap1c\Request;
use App\Services\Settings;

class RailwayOrder extends Request
{
    protected static $basePath = 'trivago.rgd_old';
    protected static $version = 'monakhov_2019';

    protected static $methods = [
        'Request',
    ];

    public static function prepareRefundRequest(OrdersRailway $order, $orderVoidedBlanks)
    {
        $settings = new Settings();
        $blanks = [];

        foreach ($order->orderDocuments as $blank){
            $blankId = $blank->OrderItemBlankId;
            $blanks[$blankId] = $blank;
        }

        $parts = [];
        $taxCounts = 0;
        $refundSum = 0;

        foreach ($order->orderData->result->ReservationResults as $reservationResult) {
            $quotes = [];
            foreach ($reservationResult->Passengers as $passenger) {
                if($orderVoidedBlanks==null || (in_array($passenger->OrderItemBlankId, $orderVoidedBlanks))) {
                    $refundSum += ($passenger->Amount - ($settings->get('taxRailwayImTrivagoRefund') + $settings->get('taxRailwayImRzdRefund') + $settings->get('taxRailwayImTrivagoPurchase') + $settings->get('taxRailwayImRzdPurchase')));
                    $quotes[] = [
                        'passenger' => [
                            'lastname' => $passenger->LastName,
                            //используется для определения сотрудника контрагента
                            'firstname' => $passenger->FirstName,
                            //используется для определения сотрудника контрагента
                            'middlename' => $passenger->MiddleName,
                            //используется для определения сотрудника контрагента
                            'document_number' => "",
                            //не используется. но можно оставить
                            'nation' => "",
                            //не используется
                            'phone_sms' => "",
                            //не используется
                            'sex' => "",
                            //не используется
                            'birthday' => "",
                            // не используется
                            'milecard_number' => "",
                            //не используется
                            'code' => "",
                            //используется для определения сотрудника контрагента
                            'grade' => "",
                            //используется для определения сотрудника контрагента
                            'table_number' => "",
                            //используется для определения сотрудника контрагента
                            'limit' => "",
                            //используется для определения сотрудника контрагента
                            'secondment' => "",
                            //используется для определения сотрудника контрагента
                            'order_date' => "",
                            //используется для определения сотрудника контрагента
                        ],
                        'ticket_numbers' => [
                            'ticket_number' => $blanks[$passenger->OrderItemBlankId]->BlankNumber ?? $blanks[$passenger->OrderItemBlankId]->Number,
                            //используется для сравнения загруженных номеров бланков
                        ],
                        'return_ids' => [
                            'return_id' => $passenger->OrderCustomerId //$reservationResult->OrderItemId
                        ],
                        'refund' => [
                            'amount' => -($passenger->Amount - ($settings->get('taxRailwayImTrivagoRefund') + $settings->get('taxRailwayImRzdRefund') + $settings->get('taxRailwayImTrivagoPurchase') + $settings->get('taxRailwayImRzdPurchase'))),
                            'currency' => 'RUB'
                        ],
                        'fee' => [ //Наш сбор
                            'amount' => $settings->get('taxRailwayImTrivagoPurchase'), //100 оплата, 50 возврат
                            'currency' => "RUB",
                        ],
                        'system_fee' => [ //Сбор системы бронирования
                            'amount' => $settings->get('taxRailwayImRzdPurchase'),
                            'currency' => "RUB",
                        ],
                        'refund_fee' => [
                            'amount' => $settings->get('taxRailwayImTrivagoRefund'), //100 оплата, 50 возврат
                            'currency' => "RUB",
                        ],
                        'system_refund_fee' => [
                            'amount' => $settings->get('taxRailwayImRzdRefund'), //100 оплата, 50 возврат
                            'currency' => "RUB",
                        ]

                    ];
                }else{
                    $taxCounts++;
                }
            }

            if(count($quotes) > 0) {
                $parts[] = [
                    'system' => "im",
                    //не используется (im)
                    'PNR' => $order->orderData->orderId,
                    //номер транзакции для запроса в систему бронирования. обязательный параметр
                    'quotes' => $quotes
                ];
            }
        }

        $defaultCustomer = $order->orderData->result->Customers[0];

        $firstName = $defaultCustomer->FirstName;
        $lastName = $defaultCustomer->LastName;
        $middleName = $defaultCustomer->MiddleName;
        $documentNumber = $defaultCustomer->DocumentNumber;
        $userId=0;

        $user = $order->user;
        if($user){
            $userId = $user->userId;
            if($user->passenger){
                $firstName = $user->passenger->nameRu->firstName;
                $lastName = $user->passenger->nameRu->lastName;
                $middleName = $user->passenger->nameRu->middleName;
                $doc = '0000000000';
                foreach ($user->passenger->documents as $document){
                    if($document->documentType==='RussianPassport'){
                        $doc = $document->documentNumber;
                    }
                }
                $documentNumber = $doc;
            }
        }



        $data = [
            'request' => [
                'request_name' => "claim_action",
                'action_type' => 'refund', //определяю тип операции.ticket - продажа, refund возврат
                'action_user_id' => "", //не нужно
                'action_time' => date("Y-m-d H:i:s"), //не нужно
                'claim' => [
                    'id' => $order->orderId, //номер заявки для определения номера заявки
                    'user_id' => "", //не юзаю
                    'client_id' => "", //код контрагента для определения контрагента
                    'client_department' => "", //департамент контрагента
                    'trivago_department' => "000000098", //подразделение ТАЛАРИИ //Для Физиков ЦО = 000000098
                    'date' => date("Y-m-d H:i:s", strtotime($order->updated_at)), //не юзаю
                    'type' => "railway", //не юзаю, но можно отставить
                    'is_divided_accounts' => "N", //используется. признак разделения заявки по билетам. если Y, на каждый билет делается отдельный счет
                    'payment_form' => "payture", //форма оплаты. используется. Для физиков у которых НЕТ ФИО И ПАСПОРТА - (Нет его в пассажирах)ФИО 3 поля = ФизЛицо паспорт = 0000000000
                    'payment_lastname' => $lastName, //создается контрагент, если cash или не указан  client_id
                    'payment_firstname' => $firstName, //создается контрагент, если cash или не указан  client_id
                    'payment_middlename' => $middleName, //создается контрагент, если cash или не указан client_id
                    'payment_document_type' => "PS", //тип не используется, считается, что всегда паспорт
                    'payment_document_number' => $documentNumber, //создается контрагент, если cash или не указан client_id
                    'parts' => $parts,
                ],
                'operator' => "N", //Определяется выписано ли оператором. важно.
                'total_price' => [ //НАДО
                    'part' => [
                        'type' => "railway",
                        'prices' => [
                            'price' => [
                                'amount' => ($order->Amount + ($settings->get('taxRailwayImTrivagoRefund')*$taxCounts) + ($settings->get('taxRailwayImRzdRefund')*$taxCounts)) - $refundSum,
                                'currency' => "RUB",
                            ],
                        ],
                    ],
                ],
            ]
        ];

        return $data;

    }

    public static function prepareTicketRequest($order)
    {
        $settings = new Settings();
        $blanks = [];
        if (!isset($order->orderDocuments[0]) || $order->orderDocuments && $order->orderDocuments[0]->Number==null) {
        $orderInfo = OrderInfo::getOrderInfo(["OrderId"=> $order->orderData->orderId,
  "AgentReferenceId" => null]);

            foreach ($orderInfo->OrderItems as $item){
                foreach ($item->OrderItemBlanks as $blank){
                    $blankId = $blank->OrderItemBlankId;
                    $blanks[$blankId] = $blank;
                }
            }

        }else{
            foreach ($order->orderDocuments as $blank){
                $blankId = $blank->OrderItemBlankId;
                $blanks[$blankId] = $blank;
            }
        }

        $parts = [];
        $taxCounts = 0;

        foreach ($order->orderData->result->ReservationResults as $reservationResult) {
            $quotes = [];
            foreach ($reservationResult->Passengers as $passenger) {
                $taxCounts++;
                $quotes[] = [
                    'passenger' => [
                        'lastname' => $passenger->LastName, //используется для определения сотрудника контрагента
                        'firstname' => $passenger->FirstName, //используется для определения сотрудника контрагента
                        'middlename' => $passenger->MiddleName, //используется для определения сотрудника контрагента
                        'document_number' => "", //не используется. но можно оставить
                        'nation' => "", //не используется
                        'phone_sms' => "", //не используется
                        'sex' => "", //не используется
                        'birthday' => "", // не используется
                        'milecard_number' => "", //не используется
                        'code' => "", //используется для определения сотрудника контрагента
                        'grade' => "", //используется для определения сотрудника контрагента
                        'table_number' => "", //используется для определения сотрудника контрагента
                        'limit' => "", //используется для определения сотрудника контрагента
                        'secondment' => "", //используется для определения сотрудника контрагента
                        'order_date' => "",//используется для определения сотрудника контрагента
                    ],
                    'ticket_numbers' => [
                        'ticket_number' => $blanks[$passenger->OrderItemBlankId]->BlankNumber ?? $blanks[$passenger->OrderItemBlankId]->Number , //используется для сравнения загруженных номеров бланков
                    ],
                    'fee' => [ //Наш сбор
                        'amount' => $settings->get('taxRailwayImTrivagoPurchase'), //100 оплата, 50 возврат
                        'currency' => "RUB",
                    ],
                    'system_fee' => [ //Сбор системы бронирования
                        'amount' => $settings->get('taxRailwayImRzdPurchase'),
                        'currency' => "RUB",
                    ],
                ];
            }

            $parts[] = [
                'system' => "im", //не используется (im)
                'PNR' => $order->orderData->orderId, //номер транзакции для запроса в систему бронирования. обязательный параметр
                'quotes' => $quotes
            ];
        }

        $defaultCustomer = $order->orderData->result->Customers[0];

        $firstName = $defaultCustomer->FirstName;
        $lastName = $defaultCustomer->LastName;
        $middleName = $defaultCustomer->MiddleName;
        $documentNumber = $defaultCustomer->DocumentNumber;
        $userId=0;

        $user = $order->user;
        if($user){
            $userId = $user->userId;
            if($user->passenger){
                $firstName = $user->passenger->nameRu->firstName;
                $lastName = $user->passenger->nameRu->lastName;
                $middleName = $user->passenger->nameRu->middleName;
                $doc = '0000000000';
                foreach ($user->passenger->documents as $document){
                    if($document->documentType==='RussianPassport'){
                        $doc = $document->documentNumber;
                    }
                }
                $documentNumber = $doc;
            }
        }



        $data = [
            'request' => [
                'request_name' => "claim_action",
                'action_type' => 'ticket', //определяю тип операции.ticket - продажа, refund возврат
                'action_user_id' => "", //не нужно
                'action_time' => date("Y-m-d H:i:s"), //не нужно
                'claim' => [
                    'id' => $order->orderId, //номер заявки для определения номера заявки
                    'user_id' => "", //не юзаю
                    'client_id' => "", //код контрагента для определения контрагента
                    'client_department' => "", //департамент контрагента
                    'trivago_department' => "000000098", //подразделение ТАЛАРИИ //Для Физиков ЦО = 000000098
                    'date' => date("Y-m-d H:i:s", strtotime($order->updated_at)), //не юзаю
                    'type' => "railway", //не юзаю, но можно отставить
                    'is_divided_accounts' => "N", //используется. признак разделения заявки по билетам. если Y, на каждый билет делается отдельный счет
                    'payment_form' => "payture", //форма оплаты. используется. Для физиков у которых НЕТ ФИО И ПАСПОРТА - (Нет его в пассажирах)ФИО 3 поля = ФизЛицо паспорт = 0000000000
                    'payment_lastname' => $lastName, //создается контрагент, если cash или не указан  client_id
                    'payment_firstname' => $firstName, //создается контрагент, если cash или не указан  client_id
                    'payment_middlename' => $middleName, //создается контрагент, если cash или не указан client_id
                    'payment_document_type' => "PS", //тип не используется, считается, что всегда паспорт
                    'payment_document_number' => $documentNumber, //создается контрагент, если cash или не указан client_id
                    'parts' => $parts,
                ],
                'operator' => "N", //Определяется выписано ли оператором. важно.
                'total_price' => [ //НАДО
                    'part' => [
                        'type' => "railway",
                        'prices' => [
                            'price' => [
                                'amount' => $order->Amount + ($settings->get('taxRailwayImTrivagoPurchase')*$taxCounts) + ($settings->get('taxRailwayImRzdPurchase')*$taxCounts),
                                'currency' => "RUB",
                            ],
                        ],
                    ],
                ],
            ]
        ];

        return $data;
    }

    public static function Ticket($order, $prepareOnly=false)
    {
        $data = self::prepareTicketRequest($order);
        if($prepareOnly) return XMLHelpers::array2XML($data);
        return self::Request($data);
    }

    public static function Refund(OrdersRailway $order, $orderVoidedBlanks, $prepareOnly=false)
    {
        $data = self::prepareRefundRequest($order, $orderVoidedBlanks);
        if($prepareOnly) return XMLHelpers::array2XML($data);
        return self::Request($data);
    }
}