<?php

namespace App\Jobs;

use App\Models\References\Trains;
use App\Models\References\TrainsCar;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\External\InnovateMobility\v1\RailwaySearch;

class AddTrainsFromSearch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $trains;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($trains)
    {
        $this->trains = $trains;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $trains = $this->trains;
        foreach ($trains as $k => $v) {
            $data=[];
            $train = Trains::where('trainNumber', 'like', $v['trainNumber'])->first();

            $data['trainNumber'] = $v['trainNumber'];
            $data['trainName'] = $v['trainName'];
            $data['trainDescription'] = $v['trainDescription'];
            $data['trainNumberToGetRoute'] = isset($v['trainNumberToGetRoute']) ? $v['trainNumberToGetRoute'] : '';
            $data['carriers'] = $v['carriers'];

            if (!$train) {
                $options = [
                    "TrainNumber" => $v['trainNumberToGetRoute'],
                    "Origin" => $v['departure']['stationCode'],
                    "Destination" => $v['arrival']['stationCode'],
                    "DepartureDate" => $v['departure']['dateEn'].'T00:00:00',
                ];

                $result = RailwaySearch::getTrainRoute($options);
                if($result){
                    $stops = collect($result->Routes[0]->RouteStops);
                    $data['routeStops'] = $stops->pluck('StationCode')->toArray();
                    $data['originStationCode'] = $stops->pluck('StationCode')->first();
                    $data['destinationStationCode'] = $stops->pluck('StationCode')->last();
                }
                $trainId = Trains::create($data)->id;
            } else {
                if($train->originStationCode==''){
                    $options = [
                        "TrainNumber" => $train->trainNumberToGetRoute,
                        "Origin" => $v['departure']['stationCode'],
                        "Destination" => $v['arrival']['stationCode'],
                        "DepartureDate" => $v['departure']['dateEn'].'T00:00:00',
                    ];

                    $result = RailwaySearch::getTrainRoute($options);
                    if($result){
                        $stops = collect($result->Routes[0]->RouteStops);
                        $data['routeStops'] = $stops->pluck('StationCode')->toArray();
                        $data['originStationCode'] = $stops->pluck('StationCode')->first();
                        $data['destinationStationCode'] = $stops->pluck('StationCode')->last();
                    }
                }

                if($v['trainName']=='') unset($data['trainName']);
                $train->fill($data);
                $train->save();
                $trainId = $train->id;
            }

            if ($trainId && isset($v['places'])) {
                foreach ($v['places'] as $place) {
                    $car = TrainsCar::where('train_id', $trainId)
                        ->where('typeEn', 'like', $place['typeEn'])
                        ->where('typeScheme', $place['typeScheme']);

                    if ($car->count() == 0) {
                        $data_p['typeRu'] = $place['typeRu'];
                        $data_p['typeEn'] = $place['typeEn'];
                        $data_p['typeScheme'] = $place['typeScheme'];
                        $data_p['train_id'] = $trainId;

                        TrainsCar::create($data_p);
                    }
                }
            }
        }
    }
}
