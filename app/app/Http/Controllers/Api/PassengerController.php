<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Passengers;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelpers;
use App\Http\Requests\PassengerRequest;

/**
 * @group Passengers
 * Class PassengerController
 * @package App\Http\Controllers\Api
 */
class PassengerController extends Controller
{
    /**
     * @var User
     */
    private $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = \Auth::user('web');
            if (!$this->user) {
                return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
            }
            return $next($request);
        });


        parent::__construct();
    }

    /**
     * List passengers
     * [Получить список пассажиров]
     * @queryParam $page Номер страницы, по умолчанию выводится 1-я страница, выводится 20 пассажиров на страницу
     *
     * @response {
     * "passengers": [
     * {
     * "passengerId": 5,
     * "nameRu": {
     * "firstName": "Иван",
     * "middleName": "Иванович",
     * "lastName": "Иванов"
     * },
     * "nameEn": {
     * "firstName": "Ivan",
     * "middleName": "Ivanovich",
     * "lastName": "Ivanov"
     * },
     * "contacts": {
     * "email": "ivan@mail.ru"
     * },
     * "documents": [
     * {
     * "documentType": "RussianPassport",
     * "documentNumber": "1223443234"
     * }
     * ],
     * "cards": [
     * {
     * "cardType": "railway",
     * "cardName": "RzhdBonus",
     * "cardNumber": 123344533
     * }
     * ]
     * }
     * ],
     * "pagesCount": 1
     * }
     * @response 404{
     *  "passengers":[]
     * }
     * @response 401 {
     *  "error": true,
     *  "message": "Auth required"
     * }
     *
     * @param int $page
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function list($page = 1)
    {
        $limit = 20;
        $offset = $limit * ($page - 1);
        $passengersRequest = $this->user->passengers();

        $passengersCount = $passengersRequest->count();
        $pagesCount = ceil($passengersCount / $limit);
        $passengers = $passengersRequest->offset($offset)->limit($limit)->get();

        if ($passengers->count() < 1) {
            return ResponseHelpers::jsonResponse(['passengers' => []], 200);
        }

        $passengersList = [];
        foreach ($passengers as $id => $passenger) {
            $passengersList[$id] = [
                "passengerId" => $passenger->passengerId,
                "nameRu" => $passenger->nameRu,
                "nameEn" => $passenger->nameEn,
                "contacts" => $passenger->contacts,
                "documents" => $passenger->documents,
                "cards" => $passenger->cards
            ];
            if ($this->user->access('passengers', User::$can['r']) !== 'self') {
                $passengersList[$id]['user'] = [
                    'userId' => $passenger->user->userId,
                    'contacts' => $passenger->user->contacts
                ];
            }
        }

        return ResponseHelpers::jsonResponse(['passengers' => $passengersList, 'pagesCount' => $pagesCount], 200);
    }

    /**
     * Get passenger data
     * [Получение данных пассажира]
     * @queryParam passengerId required Id пассажира
     * @response {
     * "passenger": {
     * "passengerId": 5,
     * "contacts": {
     * "email": "ivan@mail.ru"
     * },
     * "documents": [
     * {
     * "documentType": "RussianPassport",
     * "documentNumber": "0000000000"
     * }
     * ],
     * "cards": [
     * {
     * "cardType": "railway",
     * "cardName": "RzhdBonus",
     * "cardNumber": "0987654321"
     * }
     * ],
     * "nameRu": {
     * "firstName": "Иван",
     * "middleName": "Иванович",
     * "lastName": "Иванов"
     * },
     * "nameEn": {
     * "firstName": "Ivan",
     * "middleName": "Ivanovich",
     * "lastName": "Ivanov"
     * }
     * }
     * }
     * @response 401 {
     *  "error": true,
     *  "message": "Auth required"
     * }
     * @response 404 {
     *  "error": true,
     *  "message": "Пассажир с таким ID не найден."
     * }
     * @param $passengerId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getPassenger($passengerId)
    {
        $passenger = $this->user->passengers()->find($passengerId);

        if (!$passenger) {
            return ResponseHelpers::jsonResponse(['passenger' => []], 404);
        }

        $response = ['passenger' => $passenger->toArray()];
        if ($this->user->access('passengers', User::$can['r']) !== 'self') {
            $response['user'] = [
                'userId' => $passenger->user->userId,
                'firstName' => $passenger->user->contacts->firstName,
                'lastName' => $passenger->user->contacts->lastName,
                'middleName' => $passenger->user->contacts->middleName
            ];
        }

        return ResponseHelpers::jsonResponse($response);
    }

    /**
     * Create passenger
     * [Создание нового пассажира. По входящим параметрам смотрите ответ метода получения пассажира]
     * @response {
     * "result": true,
     * "passenger": {
     * "passengerId": 5,
     * "contacts": {
     * "email": "ivan@mail.ru"
     * },
     * "documents": [
     * {
     * "documentType": "RussianPassport",
     * "documentNumber": "0000000000"
     * }
     * ],
     * "cards": [
     * {
     * "cardType": "railway",
     * "cardName": "RzhdBonus",
     * "cardNumber": "0987654321"
     * }
     * ],
     * "nameRu": {
     * "firstName": "Иван",
     * "middleName": "Иванович",
     * "lastName": "Иванов"
     * },
     * "nameEn": {
     * "firstName": "Ivan",
     * "middleName": "Ivanovich",
     * "lastName": "Ivanov"
     * }
     * }
     * }
     * @response 400 {
     * "error": true,
     * "messages": [
     * {
     * "contacts.email": [
     * "Поле contacts.email должно быть действительным электронным адресом."
     * ]
     * }
     * ]
     * }
     * @response 401 {
     *  "error": true,
     *  "message": "Auth required"
     * }
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function store(Request $request)
    {
        $passenger = new PassengerRequest($request->all());
        $userId = $request->get('userId', $this->user->userId);
        $holdingId = $request->get('holdingId', $this->user->holdingId);
        $clientId = $request->get('clientId', $this->user->clientId);

        if (!$passenger->validate()) {
            return ResponseHelpers::jsonResponse([
                'error' => true,
                'messages' => [$passenger->getValidationErrors()]
            ], 400);
        }

        $newPassenger = new Passengers($request->all());

        $newPassenger->clientId = $clientId;
        $newPassenger->holdingId = $holdingId;
        $newPassenger->userId = $userId;
        $newPassenger->save();


        return ResponseHelpers::jsonResponse([
            'result' => true,
            'passenger' => $newPassenger->toArray()
        ], 200);
    }

    /**
     * Update passenger data
     * [Обновление данных пассажира. По входящим параметрам смотрите ответ метода получения пассажира]
     * @queryParam passengerId required Id пассажира
     *
     * @response {
     * "result": true,
     * "passenger": {
     * "passengerId": 5,
     * "contacts": {
     * "email": "ivan@mail.ru"
     * },
     * "documents": [
     * {
     * "documentType": "RussianPassport",
     * "documentNumber": "0000000000"
     * }
     * ],
     * "cards": [
     * {
     * "cardType": "railway",
     * "cardName": "RzhdBonus",
     * "cardNumber": "0987654321"
     * }
     * ],
     * "nameRu": {
     * "firstName": "Иван",
     * "middleName": "Иванович",
     * "lastName": "Иванов"
     * },
     * "nameEn": {
     * "firstName": "Ivan",
     * "middleName": "Ivanovich",
     * "lastName": "Ivanov"
     * }
     * }
     * }
     * @response 400 {
     * "error": true,
     * "messages": [
     * {
     * "contacts.email": [
     * "Поле contacts.email должно быть действительным электронным адресом."
     * ]
     * }
     * ]
     * }
     * @response 401 {
     *  "error": true,
     *  "message": "Auth required"
     * }
     * @response 404 {
     *  "error": true,
     *  "message": "Пассажир с таким ID не найден."
     * }
     *
     * @param Request $request
     * @param int $passengerId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function update(Request $request, $passengerId)
    {

        $result = new PassengerRequest($request->all());

        if (!$result->validate()) {
            return ResponseHelpers::jsonResponse([
                'error' => true,
                'messages' => [$result->getValidationErrors()]
            ], 400);
        }

        $passenger = $this->user->passengers(User::$can['w'])->find($passengerId);

        if (!$passenger) {
            return ResponseHelpers::jsonResponse([
                'error' => true,
                'message' => 'Пассажир с таким ID не найден.'
            ], 404);
        }

        $passenger->update($request->all());
        return ResponseHelpers::jsonResponse([
            'result' => true,
            'passenger' => $passenger->toArray()
        ], 200);

    }

    /**
     * Delete passenger
     * [Удаление пассажира]
     *
     * @queryParam passengerId required Id пассажира
     *
     * @response {
     * "result": true
     * }
     * @response 401 {
     *  "error": true,
     *  "message": "Auth required"
     * }
     * @response 404 {
     *  "error": true,
     *  "message": "Пассажир с таким ID не найден."
     * }
     *
     * @param int $passengerId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function destroy($passengerId)
    {

        $passenger = $this->user->passengers(User::$can['d'])->find($passengerId);

        if (!$passenger) {
            return ResponseHelpers::jsonResponse([
                'result' => false,
                'error' => 'Нет пассажира с таким passengerId'
            ], 404);
        }

        $passenger->delete();

        return ResponseHelpers::jsonResponse([
            'result' => true
        ], 200);


    }
}