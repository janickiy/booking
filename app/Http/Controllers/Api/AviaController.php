<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TriPartyAgreementsHelper;
use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelpers as AppResponseHelpers;
use App\Helpers\StringHelpers;

use App\Models\Orders;
use App\Models\OrdersAvia;
use App\Models\References\Airport;
use App\Models\References\City;
use App\Models\References\Trains;
use App\Models\References\TrainsCar;
use App\Models\References\RailwayStation;
use App\Services\External\InnovateMobility\v1\RailwaySearch;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

use ReservationKit\src\Modules\Avia\Model\Helper\BookingResultHelper;
use ReservationKit\src\Modules\Avia\Model\Helper\BrandsResultsHelper;
use ReservationKit\src\Modules\Avia\Model\Helper\FareFamilies;
use ReservationKit\src\Modules\Avia\Model\Helper\PricingResultHelper;
use ReservationKit\src\Modules\Avia\Model\Helper\SearchRequestOrBookingChecker;
use ReservationKit\src\Modules\Avia\Model\Helper\SearchResultsHelper;
use ReservationKit\src\Modules\Avia\Model\Helper\ToRkConverter;
use ReservationKit\src\RK;

/**
 * @group Avia v0.2
 *
 * [Api для работы с avia]
 */
class AviaController extends Controller
{
    /** @var \ReservationKit\src\Bundle\AviaBundle\AviaBundle */
    public $aviaBundle;

    public function __construct(Request $request)
    {
        // Путь файла с настройками модуля RK
        $bootRK = app_path() . '/Services/External/ReservationKit/src/Bootstrap.php';

        // Подключение и нициализация библиотеки бронирования ReservationKit
        if (file_exists($bootRK)) {
            $siteHost   = $request->getHttpHost();
            $siteScheme = $request->getScheme();

            $dbHost = config('database.connections.pgsql.host');
            $dbPort = config('database.connections.pgsql.port');
            $dbName = config('database.connections.pgsql.database');
            $dbUser = config('database.connections.pgsql.username');
            $dbPass = config('database.connections.pgsql.password');

            require_once app_path() . '/Services/External/ReservationKit/src/Bootstrap.php';
        }

        // Определение авиа сервиса
        $this->aviaBundle = RK::getContainer()->getBundle('Avia');

        parent::__construct();
    }

    public function RKWorker(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search_id'     => 'required|numeric',  // Идентификатор поискового запроса
                                                    // TODO заменить слово search_id на request_id в объектах и таблице. Т.к. подразумевается, что обработчик работает не только с поиском, но и другими задачами
            'requisites_id' => 'numeric',           // Идентификатор реквизита, по которому делается запрос поиск (может отсутствовать, напр. для S7agent)
            'wn'            => 'required|string',   // WorkerName (e.g.: 'avia.search'), название скрипта-исполнителя задачи
            'jid'           => 'required|numeric',  // JobId, идентификатор задачи для скрипта-исполнителя
        ], [
                'wn.required'   => 'Не указан идентификатор позиции',
                'wn.string'    => 'Неверно указан идентификатор позиции'
            ]
        );

        if ($validator->fails()) {
            return AppResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);

        } else {
            // Запуск скрипта-исполнителя wn
            RK::executeWorker($request->input('wn'));
        }
    }

    /**
     * Search
     *
     * [Поиск предложений авиа]
     *
     * @bodyParam class string required Класс перелета
     * @bodyParam type string required Тип перелета (OW - one way, в одну сторону; RT - round trip, туда-обратно; MW - multy way, сложный маршрут)
     * @bodyParam adt int required Количество взрослых пассажиров
     * @bodyParam chd int required Количество детей
     * @bodyParam inf int required Количество младенцев
     * @bodyParam isDirect bool optional Прямой перелет (по умолчанию false)
     * @bodyParam itinerary[0][origin] string required Код аэропорта/города отправления
     * @bodyParam itinerary[0][destination] string required Код аэропорта/города прибытия
     * @bodyParam itinerary[0][departureDate] string required Дата отправления (формат YYYY-MM-DD)
     * @bodyParam itinerary[0][departureTime] string optional Время отправления (формат HH:mm)
     *
     * @response {
     *  "meta": {
     *      "request": {
     *          "hash": "555fff48f64dcd5285f6f8166b9c8b41",
     *          "class": "economy",
     *          "type": "MW",
     *          "adt": 2,
     *          "chd": 1,
     *          "inf": 1,
     *          "isDirect": true,
     *          "segments": [[{
     *              "origin": {
     *                  "code": "DME",
     *                  "nameRu": "Москва(Домодедово)",
     *                  "name": "Москва(Домодедово)"
     *              },
     *              "destination": {
     *                  "code": "LED",
     *                  "nameRu": "Санкт-Петербург(Пулково)",
     *                  "name": "Санкт-Петербург(Пулково)"
     *              },
     *              "departureDate": {
     *                  "date": "2019-09-21",
     *                  "time": "07:11"
     *              },
     *              "departureDateFormatted": {
     *                  "date": "Сб, сен. 21",
     *                  "time": "07:11"
     *              }}]
     *          ]
     *      }
     *  },
     *  "data": [{
     *      "type": "offer",
     *      "attributes": {
     *          "system": "galileo",
     *          "validationAirline": "HR",
     *          "segments": [[{
     *              "origin": {
     *                  "countryId": 177,
     *                  "regionId": 7012,
     *                  "cityId": 6256,
     *                  "sourceId": 4904,
     *                  "code": "VKO",
     *                  "nameRu": "Москва(Внуково)",
     *                  "nameEn": "Moscow(Vnukovo)",
     *                  "info": {
     *                      "popularity": 232,
     *                      "utcTimeOffset": 3,
     *                      "description": "",
     *                      "location": {
     *                          "lat": 55.59153,
     *                          "lon": 37.26149
     *                      }
     *                  },
     *                  "name": "Москва(Внуково)",
     *                  "city": {
     *                      "countryId": 177,
     *                      "regionId": 7012,
     *                      "sourceId": 6256,
     *                      "code": "MOW",
     *                      "nameRu": "Москва",
     *                      "nameEn": "Moscow",
     *                      "info": {
     *                          "sysCode": "5a323c29340c7441a0a556bb",
     *                          "popularity": 254,
     *                          "expressCode": "2000000"
     *                      },
     *                      "name": "Москва"
     *                  },
     *                  "country": {
     *                      "sourceId": 177,
     *                      "code": "RU",
     *                      "nameRu": "Российская Федерация",
     *                      "nameEn": "Russian Federation",
     *                      "name": "Российская Федерация"
     *                  }
     *              },
     *              "destination": {
     *                  "countryId": 177,
     *                  "regionId": 0,
     *                  "cityId": 8480,
     *                  "sourceId": 9116,
     *                  "code": "LED",
     *                  "nameRu": "Санкт-Петербург(Пулково)",
     *                  "nameEn": "St Petersburg(Pulkovo)",
     *                  "info": {
     *                      "popularity": 232,
     *                      "utcTimeOffset": 3,
     *                      "description": "",
     *                      "location": {
     *                          "lat": 59.80029,
     *                          "lon": 30.2625
     *                      }
     *                  },
     *                  "name": "Санкт-Петербург(Пулково)",
     *                  "city": {
     *                      "countryId": 177,
     *                      "regionId": 6592,
     *                      "sourceId": 8480,
     *                      "code": "LED",
     *                      "nameRu": "Санкт-Петербург",
     *                      "nameEn": "Saint Petersburg",
     *                      "info": {
     *                          "sysCode": "5a3244bc340c7441a0a556ca",
     *                          "popularity": 217,
     *                          "expressCode": "2004000"
     *                      },
     *                      "name": "Санкт-Петербург"
     *                  },
     *                  "country": {
     *                      "sourceId": 177,
     *                      "code": "RU",
     *                      "nameRu": "Российская Федерация",
     *                      "nameEn": "Russian Federation",
     *                      "name": "Российская Федерация"
     *                  }
     *              },
     *              "originTerminal": "",
     *              "destinationTerminal": "",
     *              "departureDate": {
     *                  "date": "2019-09-21",
     *                  "time": "09:10"
     *              },
     *              "arrivalDate": {
     *                  "date": "2019-09-21",
     *                  "time": "10:25"
     *              },
     *              "departureDateFormatted": {
     *                  "date": "21 Сентябрь 2019, Сб",
     *                  "time": "09:10"
     *              },
     *              "arrivalDateFormatted": {
     *                  "date": "21 Сентябрь 2019, Сб",
     *                  "time": "10:25"
     *              },
     *              "flightNumber": "1414",
     *              "equipment": "737",
     *              "flightTime": "75",
     *              "flightTimeFormatted": "1 час 15 минут",
     *              "airline": "DP",
     *              "classOfService": "K",
     *              "cabinClass": "Y",
     *              "typeClass": "ECONOMY",
     *              "fareBasis": "KOWDP",
     *              "fareName": null,
     *              "refundable": false,
     *              "baggage": false,
     *              "baggageMeasure": "K",
     *              "carryOn": false,
     *              "seats": "4"
     *          }]],
     *          "prices": [{
     *              "offerId": 0,
     *              "totalAmount": 7800,
     *              "classOfService": [
     *                  "K"
     *              ],
     *              "cabinClass": [
     *                  "Y"
     *              ],
     *              "typeClass": [
     *                  "ECONOMY"
     *              ],
     *              "fareBasis": [
     *                  "KOWDP"
     *              ],
     *              "fareName": [
     *                  ""
     *              ],
     *              "refundable": [
     *                  ""
     *              ],
     *              "baggage": [
     *                  ""
     *              ],
     *              "baggageMeasure": [
     *                  "K"
     *              ],
     *              "carryOn": [
     *                  ""
     *              ]
     *          }],
     *          "ruid": 1
     *      }
     *  }]
     * }
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \RK_Core_Exception
     */
    public function search(Request $request)
    {
        $dataRequest = $request->all();

        $validator = Validator::make($request->all(), [
            //'class' => 'string',
        ], [
                'class.required' => 'Не указан идентификатор позиции',
                'class.numeric' => 'Неверно указан идентификатор позиции'
            ]
        );

        if ($validator->fails()) {
            return AppResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);

        } else {
            // Объект для поискового запроса
            $searchRequest = new \RK_Avia_Entity_Search_Request();
            $searchRequest->fill($dataRequest);

            // Установка 3D договора
            if (SearchRequestOrBookingChecker::isAdultOnly($searchRequest)) {
                $agreements = TriPartyAgreementsHelper::getAgreementBy();

                $triPartyAgreements = ToRkConverter::getRkTriPartyAgreements($agreements, $searchRequest->getClassType());
                $searchRequest->setTriPartyAgreements($triPartyAgreements);
            }

            // Получение авиа сервиса
            $aviaService = $this->aviaBundle->getSearchService();

            $aviaService->saveRequest($searchRequest);  // TODO перенести это действие в метод search()

            // Поиск
            $aviaService->search();

            try {
                // Преобразование результата в json
                $jsonResults = SearchResultsHelper::searchResponseToJSON($searchRequest, $aviaService->getResults());

                // Группировка и переработка результата
                $jsonResults = SearchResultsHelper::groupOffersBy($jsonResults);

            } catch (\Exception $e) {
                return AppResponseHelpers::jsonResponse([
                    'message' => 'Ошибка приведения ответа к формату json',
                    'error' => '[' . $e->getCode() . '] ' . $e->getMessage(),
                ]);
            }

            return AppResponseHelpers::jsonResponse(
                $jsonResults
            );
        }
    }

    /**
     * Brands
     *
     * Брендированные тарифы предложения.
     *
     * @bodyParam request_hash string required Хеш запроса
     * @bodyParam offer_id int required Id предложения
     *
     * @response {
     *  "data":
     *    {
     *      "type": "brands",
     *      "attributes": {
     *        "prices": [
     *          {
     *            "offerId": 0,
     *            "totalAmount": "3515",
     *            "classOfService": [
     *              "O"
     *            ],
     *            "cabinClass": [
     *              "Y"
     *            ],
     *            "typeClass": [
     *              "economy"
     *            ],
     *            "fareBasis": [
     *              "OBSOW"
     *            ],
     *            "fareName": [
     *              "Эконом Базовый"
     *            ],
     *            "refundable": [
     *              false
     *            ],
     *            "baggage": [
     *              false
     *            ],
     *            "baggageMeasure": [
     *              "K"
     *            ],
     *            "carryOn": [
     *              true
     *            ]
     *          },
     *          {
     *            "offerId": 0,
     *            "totalAmount": "4215",
     *            "classOfService": [
     *              "O"
     *            ],
     *            "cabinClass": [
     *              "Y"
     *            ],
     *            "typeClass": [
     *              "economy"
     *            ],
     *            "fareBasis": [
     *              "OFLOW"
     *            ],
     *            "fareName": [
     *              "Эконом Гибкий"
     *            ],
     *            "refundable": [
     *              false
     *            ],
     *            "baggage": [
     *              true
     *            ],
     *            "baggageMeasure": [
     *              "K"
     *            ],
     *            "carryOn": [
     *              true
     *            ]
     *          }
     *        ]
     *      }
     *    }
     *}
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function brands(Request $request)
    {
        $dataRequest = $request->all();

        $validator = Validator::make($request->all(), [
            // 'request_hash' => 'required|string',
            // 'offer_id' => 'required|numeric',
            // 'system' => 'required|string',
        ], [
                'system.required' => 'Не указан идентификатор системы бронирования',
                'system.string'   => 'Неверно указан идентификатор системы бронирования'
            ]
        );

        try {
            // Получение авиа сервиса
            $aviaService = $this->aviaBundle->getSearchService();

            // Получение выбранного предложения о перелете из результатов поиска
            $searchOffer = $aviaService->getSearchOfferByHash($dataRequest['request_hash'], $dataRequest['offer_id']);

            if ($searchOffer) {
                //
                $searchOffer->setId($dataRequest['offer_id']);

                // Установка реквизитов системы, из которой было получено предложение, в запрос для прайсинга
                $aviaService->getRequest()->setRequisiteId($searchOffer->getRequisiteId());

                // Замена сегментов
                $aviaService->getRequest()->setSegments($searchOffer->getSegments());

                // Прайсинг
                $priceSolutions = $aviaService::pricing($aviaService->getRequest());

                return AppResponseHelpers::jsonResponse(
                    BrandsResultsHelper::brandsResponseToJSON($aviaService->getRequest(), $searchOffer, $priceSolutions)
                );

            } else {
                return AppResponseHelpers::jsonResponse([
                    'message' => 'Не удалось найти предложение',
                    'error' => '[410] Offer not found',
                ]);

            }

        } catch (\Exception $e) {

            return AppResponseHelpers::jsonResponse([
                'message' => 'Ошибка приведения ответа к формату json',
                'error' => '[' . $e->getCode() . '] ' . $e->getMessage(),
            ]);

        }
    }

    /**
     * Pricing
     *
     * [Прайсинг предложение. Получение брендированных тарифов
     * Параметры ответа дополняются ...]
     *
     * @bodyParam request_hash string required Хеш запроса
     * @bodyParam offer_id int required Id предложения
     *
     * @response {
     *  "data": [{
     *      "priceId": 0,
     *      "attributes": {
     *          "totalAmount": "8928RUB",
     *          "priceDetails": {
     *              "INF": {
     *                  "typePassenger": "INF",
     *                  "baseAmount": "0RUB",
     *                  "totalTaxesAmount": "0RUB"
     *              },
     *              "ADT": {
     *                  "typePassenger": "ADT",
     *                  "baseAmount": "3660RUB",
     *                  "totalTaxesAmount": "1315RUB"
     *              },
     *              "CHD": {
     *                  "typePassenger": "CHD",
     *                  "baseAmount": "2745RUB",
     *                  "totalTaxesAmount": "1208RUB"
     *              }
     *          }
     *      }
     *  }]
     * }
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function pricing(Request $request)
    {
        $dataRequest = $request->all();

        $validator = Validator::make($request->all(), [
            // 'request_hash' => 'required|string',
            // 'offer_id' => 'required|numeric',
            // 'system' => 'required|string',
        ], [
                'system.required' => 'Не указан идентификатор системы бронирования',
                'system.string'   => 'Неверно указан идентификатор системы бронирования'
            ]
        );

        try {
            // Получение авиа сервиса
            $aviaService = $this->aviaBundle->getSearchService();

            // Получение выбранного предложения о перелете из результатов поиска
            $searchOffer = $aviaService->getSearchOfferByHash($dataRequest['request_hash'], $dataRequest['offer_id']);

            // Установка реквизитов системы, из которой было получено предложение, в запрос для прайсинга
            $aviaService->getRequest()->setRequisiteId($searchOffer->getRequisiteId());

            // Замена сегментов
            $aviaService->getRequest()->setSegments($searchOffer->getSegments());

            // Прайсинг
            $priceSolutions = $aviaService::pricing($aviaService->getRequest());

            return AppResponseHelpers::jsonResponse(
                PricingResultHelper::pricingResponseToJSON($priceSolutions)
            );

        } catch (\Exception $e) {

            return AppResponseHelpers::jsonResponse([
                'message' => 'Ошибка приведения ответа к формату json',
                'error' => '[' . $e->getCode() . '] ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Create order
     *
     * [Создание заказа с авиа бронированием]
     *
     * @bodyParam request_hash string required Хеш запроса
     * @bodyParam offer_id int required Id предложения
     * @bodyParam price_id int required Id тарифа
     *
     * @response {
     *  orderId: 0
     * }
     *
     * TODO переименовать в OrderCreate
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \RK_Core_Exception
     * @throws \ReservationKit\src\Modules\Galileo\Model\GalileoException
     */
    public function createOrder(Request $request)
    {
        $user = \Auth::user('web');
        $userId = isset($user->userId) ? $user->userId : 0;

        $validator = Validator::make($request->all(), [
            //'system' => 'required|string',
        ], [
                'system.required' => 'Не указан идентификатор системы бронирования',
                'system.string'   => 'Неверно указан идентификатор системы бронирования'
            ]
        );

        // Заглушка-пример формата json в запросе
        $dataRequest = [
            'system' => 's7agent',
            'itinerary' => [
                [
                    'origin' => 'MOW',
                    'destination' => 'LED',
                    'departureDate' => '2019-05-21',
                    'departureTime' => '17:11',
                ]
            ],
            'passengers' => [
                [
                    'type' => 'adt',
                    'firstName' => 'IVAN',
                    'lastName' => 'BOGDANOV',
                    'birthday' => '1980-05-03',
                    'sex' => 'M',
                    'nationality' => 'RU',
                    'docType' => 'F',
                    'docCountry' => 'RU',
                    'docNumber' => '6363123456',
                    'docExpired' => '2025-05-20',
                ],
            ]
        ];

        if ($validator->fails()) {
            return AppResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);

        } else {

            // Проверка оплаты для частных лиц
            // ... TODO

            try {
                // Создание заказа
                $complexOrderData = [
                    'userId' => $userId
                ];

                $complexOrderId = Orders::create($complexOrderData)->id;

                // Получение авиа сервиса
                $aviaService = $this->aviaBundle->getSearchService();

                // Получение объекта поиска из БД
                $searchOffer = $aviaService->getSearchOfferByHash($dataRequest['request_hash'], $dataRequest['offer_id']);

                // Замена сегментов
                $aviaService->getRequest()->setSegments($searchOffer->getSegments());

                // Прайсинг
                $priceSolutions = $aviaService::pricing($aviaService->getRequest());

                // Проверка актуальности прайсов
                // ... TODO

                // Создание объекта бронирования
                $bookingRequest = new \RK_Avia_Entity_Booking();
                $bookingRequest->setSegments($searchOffer->getSegments());
                $bookingRequest->fillPassengers($dataRequest['passengers']);
                $bookingRequest->setPrices($priceSolutions[$dataRequest['price_id']]);

                $bookingRequest->setRequisiteId($aviaService->getRequest()->getRequisiteId());

                // Бронирование
                $result = $aviaService::booking($bookingRequest);

                if ($result) {

                    $aviaOrder = new OrdersAvia();
                    $aviaOrder->setStatusAttribute(OrdersAvia::ORDER_STATUS_CREATED);

                    if ($bookingRequest->getLocator() && $bookingRequest->getStatus() === \RK_Avia_Entity_Booking::STATUS_BOOKED) {
                        $aviaOrder->setStatusAttribute(OrdersAvia::ORDER_STATUS_RESERVED);
                        $aviaOrder->setBookingDataAttribute($bookingRequest);

                        return AppResponseHelpers::jsonResponse(
                            BookingResultHelper::bookingResponseToJSON($bookingRequest)
                        );

                    } else {
                        return AppResponseHelpers::jsonResponse([
                            'message' => 'Не удалось создать броинрование'
                        ]);
                    }

                    // Сохранение части авиа в БД
                    $aviaOrder->save();

                    if ($aviaOrder->id) {

                        // Добавление части авиа в комплексный заказ
                        $orderItems[] = [
                            'id'   => $aviaOrder->id,
                            'type' => Orders::ORDER_TYPE_AVIA
                        ];

                        Orders::where('id', $complexOrderId)->update([
                            'orderItems' => json_encode($orderItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                        ]);

                    }
                }

            } catch (\Exception $e) {

                return AppResponseHelpers::jsonResponse([
                    'message' => 'Ошибка приведения ответа к формату json',
                    'error' => '[' . $e->getCode() . '] ' . $e->getMessage(),
                ]);
            }

        }
    }

    /**
     * Retrieve
     *
     * [Чтение заказа с обновлением данных]
     *
     * @bodyParam order_id int required Id заказа
     *
     * @response { }
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \ReservationKit\src\Modules\Galileo\Model\Service\Exception
     */
    public function retrieve(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'orderId' => 'required|numeric',
        ], [
                'orderId.required' => 'Не указан идентификатор системы бронирования',
                'orderId.string'   => 'Неверно указан идентификатор системы бронирования'
            ]
        );

        if ($validator->fails()) {
            return AppResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);

        } else {
            $aviaOrder = OrdersAvia::find($request->input('system'));
        }

        try {
            /** @var \RK_Avia_Entity_Booking $booking */
            $booking = $aviaOrder->setBookingDataAttribute();
            $booking->wakeupRequisites();

            RK::getContainer()->getModule($booking->getSystem())
                ->getSearchService()
                ->read($booking);

            return AppResponseHelpers::jsonResponse([
                $this->readBookingResponseToJSON($booking)
            ]);

        } catch (\Exception $e) {
            return AppResponseHelpers::jsonResponse([
                'message' => 'Не удалось прочитать бронирование',
                'error'   => '[' . $e->getCode() . '] ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Cancel order
     *
     * [Отмена заказа]
     *
     * @bodyParam order_id int required Id заказа
     *
     * @response { }
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function cancel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'orderId' => 'required|numeric',
        ], [
                'orderId.required' => 'Не указан идентификатор системы бронирования',
                'orderId.string'   => 'Неверно указан идентификатор системы бронирования'
            ]
        );

        if ($validator->fails()) {
            return AppResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);

        } else {
            $aviaOrder = OrdersAvia::find($request->input('system'));
        }

        try {
            /** @var \RK_Avia_Entity_Booking $booking */
            $booking = $aviaOrder->setBookingDataAttribute();
            $booking->wakeupRequisites();

            // Отмена бронирования
            RK::getContainer()->getModule($booking->getSystem())
                ->getSearchService()
                ->cancel($booking);

            // Изменение статуса авиа заказа
            if ($booking->getStatus() === \RK_Avia_Entity_Booking::STATUS_CANCEL) {
                $aviaOrder->setStatusAttribute(OrdersAvia::ORDER_STATUS_CANCELED);
            }

            return AppResponseHelpers::jsonResponse([
                $this->readBookingResponseToJSON($booking)
            ]);

        } catch (\Exception $e) {
            return AppResponseHelpers::jsonResponse([
                'message' => 'Не удалось отменить бронирование',
                'error'   => '[' . $e->getCode() . '] ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Ticketing order
     *
     * [Выписка билетов]
     *
     * @bodyParam order_id int required Id заказа
     *
     * @response { }
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function ticketing(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'orderId' => 'required|numeric',
        ], [
                'orderId.required' => 'Не указан идентификатор системы бронирования',
                'orderId.string'   => 'Неверно указан идентификатор системы бронирования'
            ]
        );

        if ($validator->fails()) {
            return AppResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);

        } else {
            $aviaOrder = OrdersAvia::find($request->input('system'));
        }

        try {
            /** @var \RK_Avia_Entity_Booking $booking */
            $booking = $aviaOrder->getBookingDataAttribute();
            $booking->wakeupRequisites();

            // Выписка
            RK::getContainer()->getModule($booking->getSystem())
                ->getSearchService()
                ->ticket($booking);

            return AppResponseHelpers::jsonResponse([
                $this->readBookingResponseToJSON($booking)
            ]);

        } catch (\Exception $e) {
            return AppResponseHelpers::jsonResponse([
                'message' => 'Не удалось выписать билеты',
                'error'   => '[' . $e->getCode() . '] ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Refund
     *
     * [Возврат билетов. WIP]
     *
     * @bodyParam order_id int required Id заказа
     *
     * @response { }
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function refund(Request $request)
    {
        // TODO WIP
        return AppResponseHelpers::jsonResponse([  ]);
    }

    /**
     * Get rules
     *
     * [Получение правил тарифа]
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getRules(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'orderId' => 'required|numeric',
        ], [
                'orderId.required' => 'Не указан идентификатор системы бронирования',
                'orderId.string'   => 'Неверно указан идентификатор системы бронирования'
            ]
        );

        if ($validator->fails()) {
            return AppResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);

        } else {
            $aviaOrder = OrdersAvia::find($request->input('system'));
        }

        try {
            /** @var \RK_Avia_Entity_Booking $booking */
            $booking = $aviaOrder->setBookingDataAttribute();
            $booking->wakeupRequisites();

            // Правила тарифов
            $rules = RK::getContainer()->getModule($booking->getSystem())
                ->getSearchService()
                ->getRules($booking);

            return AppResponseHelpers::jsonResponse($rules);

        } catch (\Exception $e) {
            return AppResponseHelpers::jsonResponse([
                'message' => 'Не удалось получить правила',
                'error'   => '[' . $e->getCode() . '] ' . $e->getMessage()
            ]);
        }
    }

    /**
     * @param \RK_Avia_Entity_Booking $booking
     * @return array
     */
    public function readBookingResponseToJSON($booking)
    {
        return [
            'pnr' => $booking->getLocator(),
            'status' => $booking->getStatus()
        ];
    }
}