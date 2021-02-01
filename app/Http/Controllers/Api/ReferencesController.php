<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelpers;
use App\Http\Controllers\Controller;
use App\Models\References\{
    Airport, BusStop, City, Country, RailwayStation, Region
};
use App\Services\SessionLog;
use Illuminate\Http\Request;
use App\Helpers\LangHelper;

/**
 * @group References
 *
 * API для получения справочной информации
 */
class ReferencesController extends Controller
{

    protected $models = [
        'city' => City::class,
        'country' => Country::class,
        'region' => Region::class,
        'station' => RailwayStation::class,
        'airport' => Airport::class,
        'busStop' => BusStop::class,
    ];

    /**
     * Reference query
     * [Получение следующих моделей с групировкой и фильтрами city, country, region, station, airport]
     * @queryParam model required Название модели данных, на данный момент доступны (city, country, region, station, airport)
     * @bodyParam keyword string Строка для поиска по полям nameRu(En)
     * @bodyParam limit array Лимит и оффсет [limit, offset?]
     * @bodyParam sort array Сотриовка [field, direction]
     * @bodyParam with array Присоединить связананные модели
     * @param Request $request
     * @param $model
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function query(Request $request, $model)
    {
        $union = json_decode($model);
        if (!$union || !is_array($union)) {
            $models = [$model];
        } else {
            $models = $union;
        }

        $responseData = [];
        foreach ($models as $model) {
            if (!isset($this->models[$model])) {
                return ResponseHelpers::jsonResponse(['message' => "Model '{$model}' not found"], 404);
            }

            $params = $request->input();
            $query = $this->models[$model]::queryFromRequest($params);

            $data = SessionLog::logQuery($this->models[$model], function () use ($query) {
                return $query->get();
            });

            if (
                $model == 'city' &&
                (isset($params['keyword']) && strlen($params['keyword']) == 3) &&
                (isset($params['with']) && in_array('airports', $params['with']))
            ) {
                $filtredData = [];
                foreach ($data as $id => $item) {

                    $filtredData[$id] = $item->toArray();

                    if (in_array(strtoupper($params['keyword']), $item->airports->pluck('code')->toArray())) {
                        $filtredData[$id]['airports'] = $item->airports->firstWhere('code',
                            strtoupper($params['keyword']));
                    }
                }
            } elseif (
                $model == 'city' &&
                isset($params['with']) && in_array('stations', $params['with']) &&
                isset($params['filters']) && isset($params['filters']['stations'])
            ) {

                switch ($params['filters']['stations']) {
                    case 'forSearch':
                        $filtredData = [];
                        foreach ($data as $id => $item) {

                            $filtredData[$id] = $item->toArray();

                            $filtredData[$id]['stations'] = $item->stations->filter(function ($value, $key) use ($item
                            ) {
                                return mb_convert_case($value->nameRu, MB_CASE_LOWER) !== mb_convert_case($item->nameRu,
                                        MB_CASE_LOWER) &&
                                    mb_strpos(mb_convert_case($value->nameRu, MB_CASE_LOWER),
                                        'все вокзалы') === false &&
                                    mb_strpos(mb_convert_case($value->nameRu, MB_CASE_LOWER), 'тов') === false &&
                                    $value->info->popularity > 1;
                            })->toArray();
                            sort($filtredData[$id]['stations']);
                        }
                        break;
                }
            }

            $responseData[$model] = $filtredData ?? $data;

            unset($data);
            if (isset($filtredData)) {
                unset($filtredData);
            }
        }

        return ResponseHelpers::jsonResponse($responseData);

    }

    /**
     * Static references
     * [Справочники не требующие постоянного обновления]
     * @queryParam section Получить только определенную секцию справочника
     * @param bool $section
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function staticReferences($section = false)
    {

        if ($section){
           // return ResponseHelpers::jsonResponse(StaticReferences::REFERENCES[$section]);
            return ResponseHelpers::jsonResponse(LangHelper::trans('references/im'));
        }

     //  return ResponseHelpers::jsonResponse(StaticReferences::REFERENCES);
        return ResponseHelpers::jsonResponse(LangHelper::trans('references/im'));
    }
}