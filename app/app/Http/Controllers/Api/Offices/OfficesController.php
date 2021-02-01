<?php

namespace App\Http\Controllers\Api\Offices;

use App\Helpers\ResponseHelpers;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\V1\Offices\SearchRequest;
use App\Http\Requests\Request;
use App\Models\Office;
use App\Repositories\SettingsRepository;

/**
 * @group Offices
 * Class OfficesController
 * @package App\Http\Controllers\Api\Offices
 */
class OfficesController extends BaseController
{
    /**
     * List offices
     * @param SearchRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(SearchRequest $request)
    {
        $limit         = (int)    $request->input('limit', 20);
        $filter        = (array)  $request->input('filter', []);
        $relations     = (array)  $request->input('with', []);
        $sort          = (string) $request->input('sort', 'id');
        $sortDirection = (string) $request->input('sortDirection', 'asc');
        $appends       = (array)  $request->input('appends', []);

        $office = Office::select(['*'])
            ->filter($filter)
            ->orderBy($sort, $sortDirection)
            ->paginate($limit);

        return $this->successResponse(compact('office'));
    }

    /**
     * Get office
     * @param $officeId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getOffice($officeId)
    {
        $office = Office::find($officeId);

        if(!$office){
            ResponseHelpers::jsonResponse([],404);
        }

        return ResponseHelpers::jsonResponse($office,200);
    }

    /**
     * Get closest office
     * [Получить ближайший к пользователю офис. Если переданных координты, то по ним. Если не пререданны координаты и удалось определить координаты по IP. В противном случае возвращается Москва]
     * @queryParam lat float Широта
     * @queryParam lon float Долгота
     * @param int $lat
     * @param int $lon
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getClosest($lat=0, $lon=0)
    {
        if($lat > 0 && $lon > 0){
            $office = Office::closest($lat, $lon);
            return ResponseHelpers::jsonResponse($office, 200);
        }

        $geo = session()->get('geoData', false);
        if($geo){
            $office = Office::closest($geo->city->lat, $geo->city->lon);
            return ResponseHelpers::jsonResponse($office, 200);
        }

        $office = Office::find(1);
        return ResponseHelpers::jsonResponse($office, 200);
    }
}
