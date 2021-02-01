<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 11.04.2018
 * Time: 16:15
 */

namespace App\Http\Controllers\Api;

use App\Contracts\Api\ErrorCodesEnv;
use App\Exceptions\AppException;
use App\Http\Requests\Api\V1\Aeroexpress\InfoRequest;
use App\Http\Requests\Api\V1\Aeroexpress\SearchRequest;
use App\Services\External\InnovateMobility\v1\AeroexpressSearch;
use Carbon\Carbon;
use Throwable;

/**
 * Class AeroexpressController
 * [Методы для получения информации по заказам Аэроэкспресса]
 * @group Aeroexpress info
 * @package App\Http\Controllers\Api
 */
class AeroexpressController extends BaseController
{
    const TARIFF_TYPE = "Business";

    /**
     * Search
     * [Поиск аэроэкспрассов отправляемых в указанную дату]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Order-V1-Info-OrderInfo
     * @bodyParam DepartureDate date required Дата отправления
     * @param SearchRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(SearchRequest $request)
    {
        try {
            $options = [
                "DepartureDate" => $request->input('departureDate').'T'.Carbon::now()->format('H:00:00'),
            ];
            $result = AeroexpressSearch::getTariffPricing($options, true);
            if (!$result) {
                throw new AppException(AeroexpressSearch::getLastError()->Message);
            }

            return $this->successResponse($result);
        } catch (AppException $e) {
            return $this->appExceptionResponse($e);
        } catch (Throwable $e) {
            return $this->clientFriendlyExceptionResponse($e);
        }
    }

    /**
     * Info
     * [Получение информации о аэроэкспрессе]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Order-V1-Info-OrderInfo
     * @bodyParam DepartureDate date required Дата отправления
     * @bodyParam TariffId integer required Номер поезда
     * @param InfoRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(InfoRequest $request)
    {
        try {
            $options = [
                "DepartureDate" => $request
                        ->input('departureDate').'T'.Carbon::now()->format('H:00:00'),
                "TariffId" => $request->input('tariffId'),
            ];
            $result = AeroexpressSearch::getTariffPriceInfo($options, true);

            if (!$result) {
                throw new AppException(AeroexpressSearch::getLastError()->Message);
            }
            if ($result["TariffType"] == self::TARIFF_TYPE) {
                $raceId = $request->input('raceId');
                if (!$result['RouteName'][0]['Races'][$raceId]) {
                    throw new AppException(
                        'Нет такого рейса',
                        ErrorCodesEnv::ERROR_NOT_FOUND_RACE
                    );
                }
                if (!$result['RouteName'][0]['Races'][$raceId]->FreePlaceQuantity) {
                    throw new AppException(
                        'Не осталось билетов на выбранный рейс',
                        ErrorCodesEnv::ERROR_NO_TICKETS
                    );
                }
            }

            return $this->successResponse($result);
        } catch (AppException $e) {
            return $this->appExceptionResponse($e);
        } catch (Throwable $e) {
            return $this->clientFriendlyExceptionResponse($e);
        }
    }
}
