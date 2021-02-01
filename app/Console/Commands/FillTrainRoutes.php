<?php

namespace App\Console\Commands;

use App\Models\References\Trains;
use App\Services\External\InnovateMobility\v1\RailwaySearch;
use Illuminate\Console\Command;

class FillTrainRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:fill-routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Заполняем информацию маршрутов если она отсуствует';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $trains = Trains::where('originStationCode','')->get();
        foreach ($trains as $train){
            $dash = '2000000';
            if(in_array($train->trainNumber,['032А','781М','783М','785М','787М','782З','784З','786З','031А','788З']))$dash = '2004000';

            $options = [
                "TrainNumber" => $train->trainNumberToGetRoute,
                "Origin" => $dash,
                "Destination" => $dash,
                "DepartureDate" => date('Y-m-d\T00:00:00'),
            ];

            $result = RailwaySearch::getTrainRoute($options);
            $data=[];

            if($result){
                $stops = collect($result->Routes[0]->RouteStops);
                $data['routeStops'] = $stops->pluck('StationCode')->toArray();
                $data['originStationCode'] = $stops->pluck('StationCode')->first();
                $data['destinationStationCode'] = $stops->pluck('StationCode')->last();
            }

            $train->fill($data);
            $train->save();
        }
    }
}
