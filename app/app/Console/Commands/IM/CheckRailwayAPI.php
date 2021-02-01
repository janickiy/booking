<?php

namespace App\Console\Commands\IM;

use Illuminate\Console\Command;
use App\Services\External\InnovateMobility\v1\RailwaySearch;
use App\Models\References\StatusApi;
use Carbon\Carbon;
use App\Helpers\StringHelpers;

class CheckRailwayAPI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:im:checkRailwayAPI
    {sections* : Обновить указанные секции (all,search,carpricing,schedule,trainroute,routes)}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестирование работоспособности IM API Railway/V1/Search';

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
        // проверяем метод  Railway/V1/Search/TrainPricing
        if (in_array('all', $this->argument('sections')) || in_array('search', $this->argument('sections'))) {

            $options = [
                "Origin" => 2000000,
                "Destination" => 2004000,
                "DepartureDate" => date("Y-m-d", strtotime("+8 days")) . 'T00:00:00',
                "TimeFrom" => "0",
                "TimeTo" => "12",
                "CarGrouping" => "Group"
            ];

            $result = RailwaySearch::getTrainPricing(
                $options, true,
                ['allowZeroPlace' => 0]
            );

            if ($result) {
                self::setStatus('railway.v1.search.trainpricing', true);

                $this->info('Сервис Railway/V1/Search доступен');
            } else {

                $response = StringHelpers::ObjectToArray(RailwaySearch::getLastError());

                self::setStatus('railway.v1.search.trainpricing', false, $response["Message"]);

                $this->error($response["Message"]);
            }
        }

        // проверяем метод Railway/V1/Search/CarPricing
        if (in_array('all', $this->argument('sections')) || in_array('carpricing', $this->argument('sections'))) {

            $options = [
                "OriginCode" => "2000000",
                "DestinationCode" => "2004000",
                "DepartureDate" => date("Y-m-d", strtotime("+3 days")) . 'T00:00:00',
                "TrainNumber" => "760А",
                "CarType" => null,
                "TariffType" => "Full"
            ];

            $result = RailwaySearch::getCarPricing(
                $options, true,
                ['allowZeroPlace' => 0]
            );

            if ($result) {
                self::setStatus('railway.v1.search.carpricing', true);

                $this->info('Сервис Railway/V1/CarPricing доступен');
            } else {

                $response = StringHelpers::ObjectToArray(RailwaySearch::getLastError());

                self::setStatus('railway.v1.search.carpricing', false, $response["Message"]);

                $this->error($response["Message"]);
            }
        }

        // проверяем метод Railway/V1/Search/Schedule
        if (in_array('all', $this->argument('sections')) || in_array('schedule', $this->argument('sections'))) {

            $options = [
                "Origin" => "2000000",
                "Destination" => "2004000",
                "DepartureDate" > null,
                "TimeFrom" => 12,
                "TimeTo" => 24
            ];

            $result = RailwaySearch::getSchedule(
                $options
            );

            if ($result) {
                self::setStatus('railway.v1.search.schedule', true);

                $this->info('Сервис Railway/V1/Schedule доступен');
            } else {

                $response = StringHelpers::ObjectToArray(RailwaySearch::getLastError());

                self::setStatus('railway.v1.search.schedule', false, $response["Message"]);

                $this->error($response["Message"]);
            }
        }

        // проверяем метод Railway/V1/Search/TrainRoute
        if (in_array('all', $this->argument('sections')) || in_array('trainroute', $this->argument('sections'))) {

            $options = [
                "TrainNumber" => '752А',
                "Origin" => 2000000,
                "Destination" => 2004000,
                "DepartureDate" => date("Y-m-d", strtotime("+3 days")) . 'T00:00:00',
            ];

            $result = RailwaySearch::getTrainRoute($options);

            if ($result) {
                self::setStatus('railway.v1.search.trainroute', true);
                $this->info('Сервис Railway/V1/Trainroute доступен');
            } else {

                $response = StringHelpers::ObjectToArray(RailwaySearch::getLastError());

                self::setStatus('railway.v1.search.trainroute', false, $response["Message"]);
                $this->error($response["Message"]);
            }
        }

        // проверяем метод Railway/V1/Search/Routes
        if (in_array('all', $this->argument('sections')) || in_array('routes', $this->argument('sections'))) {

            $options = [
                "Origin" => "2000000",
                "Destination" => "2004000",
                "DepartureDate" => date("Y-m-d", strtotime("+3 days")) . 'T00:00:00',
                "MinChangeTime" => 60,
                "MaxChangeTime" => 360,
                "FirstChangeOnly" => true,
                "RateToMinDistance" => 0.0,
                "RateToMinTime" => 0.0
            ];

            $result = RailwaySearch::getRoutes($options);

            if ($result) {
                self::setStatus('railway.v1.search.routes', true);
                $this->info('Сервис Railway/V1/Routes доступен');

            } else {

                $response = StringHelpers::ObjectToArray(RailwaySearch::getLastError());

                self::setStatus('railway.v1.search.routes', false, $response["Message"]);
                $this->error($response["Message"]);
            }
        }
    }

    /**
     * @param $api
     * @param $status
     * @param null $message
     */
    private static function setStatus($api, $status, $message = null)
    {
        if (StatusApi::where('api_name', $api)->count() > 0) {
            $statusApi = StatusApi::where('api_name', $api)->first();
            $statusApi->message = $message;
            $statusApi->status = $status;
            $statusApi->checkAt = Carbon::now();
            $statusApi->updated_at = Carbon::now();
            $statusApi->update();

        } else {
            $statusApi = new StatusApi;
            $statusApi->api_name = $api;
            $statusApi->message = $message;
            $statusApi->status = $status;
            $statusApi->checkAt = Carbon::now();
            $statusApi->created_at = Carbon::now();
            $statusApi->save();
        }
    }
}
