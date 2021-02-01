<?php

namespace App\Console\Commands\IM;

use App\Models\References\Airport;
use App\Models\References\BusStop;
use App\Models\References\City;
use App\Models\References\Country;
use App\Models\References\RailwayStation;
use App\Models\References\Region;
use App\Services\External\InnovateMobility\v1\References;
use Illuminate\Console\Command;

class UpdateReferences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:im:updateReferences 
    {sections* : Обнвить указанные секции (all, city, country, region, station, airport)}
    {--f|force : Принудительно перезаписать все данные справочников} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновление справочников от Инновационная Мобильность';

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
        $sourceCode = config('trivago.services.im.code');
        
        if(in_array('all', $this->argument('sections')) || in_array('country', $this->argument('sections'))) {
            
            if($this->option('force')){
                Country::truncate();
                Country::flushCache(Country::class);
            }

            $countryLastUpdate = Country::where('source', $sourceCode)->max('updated_at');
            $countryLastUpdateDate = $countryLastUpdate ? date('Y-m-d\TH:i:s',strtotime($countryLastUpdate)): null;
            
            $data = References::getCountries(['LastUpdated' => $countryLastUpdateDate]);
            $countriesInserted = 0;
            $countriesUpdated = 0;

            $bar = count($data->Countries) > 0 ? $this->output->createProgressBar(count($data->Countries)) : false;

            foreach ($data->Countries as $country) {

                $countryModel = Country::where('source', $sourceCode)->where('sourceId', $country->CountryId)->first();
                if ($countryModel) {
                    $countriesUpdated++;
                }else{
                    $countryModel = new Country();
                    $countriesInserted++;
                }

                $countryModel->fill([
                        'sourceId' => $country->CountryId,
                        'code' => $country->Alpha2Code,
                        'nameRu' => $country->NameRu,
                        'nameEn' => $country->NameEn,
                        'isActive' => $country->IsActive,
                        'source' => $sourceCode,
                        'sourceUpdatedAt' => date('Y-m-d H:i:s', strtotime($country->Updated))
                    ]);

                $countryModel->save();
                unset($countryModel);
                $bar->advance();
            }

            unset($data);

            if($bar){
                $bar->finish();
                $this->line('');
                $this->info('Стран добавлено: ' . $countriesInserted);
                $this->info('Стран обновлено: ' . $countriesUpdated);
                Country::flushCache(Country::class);
            }

        }
        
        if(in_array('all', $this->argument('sections')) || in_array('region', $this->argument('sections'))) {
            
            if($this->option('force')){
                Region::truncate();
                Region::flushCache(Region::class);
            }

            $regionLastUpdate = Region::where('source', $sourceCode)->max('updated_at');
            $regionLastUpdateDate = $regionLastUpdate ? date('Y-m-d\TH:i:s',strtotime($regionLastUpdate)): null;
            
            $data = References::getRegions(['LastUpdated' => $regionLastUpdateDate]);
            $regionsInserted = 0;
            $regionsUpdated = 0;

            $bar = count($data->Regions) > 0 ? $this->output->createProgressBar(count($data->Regions)) : false;

            foreach ($data->Regions as $region) {

                $regionModel = Region::where('source', $sourceCode)->where('sourceId', $region->RegionId)->first();
                if ($regionModel) {
                    $regionsUpdated++;
                }else{
                    $regionModel = new Region();
                    $regionsInserted++;
                }

                $regionModel->fill([
                    'sourceId' => $region->RegionId,
                    'countryId' => $region->CountryId,
                    'code' => $region->IsoCode,
                    'nameRu' => $region->NameRu,
                    'nameEn' => $region->NameEn,
                    'isActive' => $region->IsActive,
                    'source' => $sourceCode,
                    'sourceUpdatedAt' => date('Y-m-d H:i:s', strtotime($region->Updated))
                ]);

                $regionModel->save();
                unset($regionModel);
                $bar->advance();
            }

            unset($data);

            if($bar){
                $bar->finish();
                $this->line('');
                $this->info('Регионов добавлено: ' . $regionsInserted);
                $this->info('Регионов обновлено: ' . $regionsUpdated);
                Region::flushCache(Region::class);
            }

        }

        if(in_array('all', $this->argument('sections')) || in_array('city', $this->argument('sections'))) {

            if($this->option('force')){
                City::truncate();
                City::flushCache(City::class);
            }

            $cityLastUpdate = City::where('source', $sourceCode)->max('updated_at');
            $cityLastUpdateDate = $cityLastUpdate ? date('Y-m-d\TH:i:s',strtotime($cityLastUpdate)): null;

            $data = References::getCities(['LastUpdated' => $cityLastUpdateDate]);
            $citiesInserted = 0;
            $citiesUpdated = 0;

            $bar = count($data->Cities) > 0 ? $this->output->createProgressBar(count($data->Cities)) : false;

            foreach ($data->Cities as $city) {

                $cityModel = City::where('source', $sourceCode)->where('sourceId', $city->CityId)->first();
                if ($cityModel) {
                    $citiesUpdated++;
                }else{
                    $cityModel = new City();
                    $citiesInserted++;
                }

                $cityModel->fill([
                    'sourceId' => $city->CityId,
                    'countryId' => $city->CountryId,
                    'regionId' => $city->RegionId,
                    'code' => $city->Code,
                    'nameRu' => $city->NameRu,
                    'nameEn' => $city->NameEn,
                    'isActive' => $city->IsActive,
                    'source' => $sourceCode,
                    'sourceUpdatedAt' => date('Y-m-d H:i:s', strtotime($city->Updated)),
                    'info' => [
                        'popularity' => $city->PopularityIndex,
                        'sysCode' => $city->SysCode,
                        'expressCode' => trim($city->ExpressCode)!="" ? $city->ExpressCode : null,
                    ],
                ]);

                $cityModel->save();
                unset($cityModel);
                $bar->advance();
            }

            unset($data);

            if($bar){
                $bar->finish();
                $this->line('');
                $this->info('Городов добавлено: ' . $citiesInserted);
                $this->info('Городов обновлено: ' . $citiesUpdated);
                City::flushCache(City::class);
            }
        }

        if(in_array('all', $this->argument('sections')) || in_array('station', $this->argument('sections'))) {

            if($this->option('force')){

                $customFieldsSource = RailwayStation::select(['custom','sourceId'])->where('custom->nameRu','!=','')->get()->toArray();
                $customFields = [];

                foreach ($customFieldsSource as $item){
                    $customFields[$item['sourceId']] = $item['custom'];
                }

                unset($customFieldsSource);

                RailwayStation::truncate();
                RailwayStation::flushCache(RailwayStation::class);
            }

            $stationLastUpdate = RailwayStation::where('source', $sourceCode)->max('updated_at');
            $stationLastUpdateDate = $stationLastUpdate ? date('Y-m-d\TH:i:s',strtotime($stationLastUpdate)): null;

            $data = References::getTransportNodes(['LastUpdated' => $stationLastUpdateDate, 'Type'=> 'RailwayStation','IncludeInvisible'=>'true']);
            $stationsInserted = 0;
            $stationsUpdated = 0;

            $bar = count($data->TransportNodes) > 0 ? $this->output->createProgressBar(count($data->TransportNodes)) : false;

            foreach ($data->TransportNodes as $station) {

                $stationModel = RailwayStation::where('source', $sourceCode)->where('sourceId', $station->TransportNodeId)->first();
                if ($stationModel) {
                    $stationsUpdated++;
                }else{
                    $stationModel = new RailwayStation();
                    $stationsInserted++;
                }

                $stationModel->fill([
                    'sourceId' => $station->TransportNodeId,
                    'countryId' => $station->CountryId,
                    'regionId' => $station->RegionId,
                    'cityId' => $station->CityId,
                    'code' => $station->Code,
                    'nameRu' => $station->NameRu,
                    'nameEn' => $station->NameEn,
                    'isActive' => $station->IsActive,
                    'source' => $sourceCode,
                    'sourceUpdatedAt' => date('Y-m-d H:i:s', strtotime($station->Updated)),
                    'info' => [
                        'popularity' => $station->PopularityIndex,
                        'utcTimeOffset' => $station->UtcTimeOffset,
                        'description' => $station->Description,
                        'isSuburban' => $station->IsSuburban,
                        'isVisible' => $station->IsVisible,
                        'location' => $station->Location != null ?[
                            'lat' =>  $station->Location->Latitude,
                            'lon' => $station->Location->Longitude
                        ]:[]
                    ],
                ]);

                if(isset($customFields) && isset($customFields[$station->TransportNodeId])){
                    $stationModel->custom = $customFields[$station->TransportNodeId];
                }

                $stationModel->save();
                unset($stationModel);
                $bar->advance();
            }

            unset($data);

            if($bar){
                $bar->finish();
                $this->line('');
                $this->info('Ж/Д станций добавлено: ' . $stationsInserted);
                $this->info('Ж/Д станций обновлено: ' . $stationsUpdated);
                RailwayStation::flushCache(RailwayStation::class);
            }
        }

        if(in_array('all', $this->argument('sections')) || in_array('airport', $this->argument('sections'))) {

            if($this->option('force')){
                Airport::truncate();
                Airport::flushCache(Airport::class);
            }

            $stationLastUpdate = Airport::where('source', $sourceCode)->max('updated_at');
            $stationLastUpdateDate = $stationLastUpdate ? date('Y-m-d\TH:i:s',strtotime($stationLastUpdate)): null;

            $data = References::getTransportNodes(['LastUpdated' => $stationLastUpdateDate, 'Type'=> 'Airport','IncludeInvisible'=>'false']);
            $stationsInserted = 0;
            $stationsUpdated = 0;

            $bar = count($data->TransportNodes) > 0 ? $this->output->createProgressBar(count($data->TransportNodes)) : false;

            foreach ($data->TransportNodes as $station) {

                $stationModel = Airport::where('source', $sourceCode)->where('sourceId', $station->TransportNodeId)->first();
                if ($stationModel) {
                    $stationsUpdated++;
                }else{
                    $stationModel = new Airport();
                    $stationsInserted++;
                }

                $stationModel->fill([
                    'sourceId' => $station->TransportNodeId,
                    'countryId' => $station->CountryId,
                    'regionId' => $station->RegionId,
                    'cityId' => $station->CityId,
                    'code' => $station->Code,
                    'nameRu' => $station->NameRu,
                    'nameEn' => $station->NameEn,
                    'isActive' => $station->IsActive,
                    'source' => $sourceCode,
                    'sourceUpdatedAt' => date('Y-m-d H:i:s', strtotime($station->Updated)),
                    'info' => [
                        'popularity' => $station->PopularityIndex,
                        'utcTimeOffset' => $station->UtcTimeOffset,
                        'description' => $station->Description,
                        'location' => $station->Location != null ?[
                            'lat' =>  $station->Location->Latitude,
                            'lon' => $station->Location->Longitude
                        ]:[]
                    ],
                ]);

                $stationModel->save();
                unset($stationModel);
                $bar->advance();
            }

            unset($data);

            if($bar){
                $bar->finish();
                $this->line('');
                $this->info('Аэеропортов добавлено: ' . $stationsInserted);
                $this->info('Аэеропортов обновлено: ' . $stationsUpdated);
                Airport::flushCache(Airport::class);
            }
        }

        if(in_array('all', $this->argument('sections')) || in_array('bus_stops', $this->argument('sections'))) {

            if($this->option('force')){

                BusStop::truncate();
                BusStop::flushCache(BusStop::class);
            }

            $stationLastUpdate = BusStop::where('source', $sourceCode)->max('updated_at');
            $stationLastUpdateDate = $stationLastUpdate ? date('Y-m-d\TH:i:s',strtotime($stationLastUpdate)): null;

            $data = References::getTransportNodes(['LastUpdated' => $stationLastUpdateDate, 'Type'=> 'BusStop','IncludeInvisible'=>'true']);
            $stationsInserted = 0;
            $stationsUpdated = 0;

            $bar = count($data->TransportNodes) > 0 ? $this->output->createProgressBar(count($data->TransportNodes)) : false;

            foreach ($data->TransportNodes as $station) {

                $stationModel = BusStop::where('source', $sourceCode)->where('sourceId', $station->TransportNodeId)->first();
                if ($stationModel) {
                    $stationsUpdated++;
                }else{
                    $stationModel = new BusStop();
                    $stationsInserted++;
                }

                $stationModel->fill([
                    'sourceId' => $station->TransportNodeId,
                    'countryId' => $station->CountryId,
                    'regionId' => $station->RegionId,
                    'cityId' => $station->CityId,
                    'code' => $station->Code,
                    'nameRu' => $station->NameRu,
                    'nameEn' => $station->NameEn,
                    'isActive' => $station->IsActive,
                    'isSuburban' => $station->IsSuburban,
                    'source' => $sourceCode,
                    'sourceUpdatedAt' => date('Y-m-d H:i:s', strtotime($station->Updated)),
                    'info' => [
                        'popularity' => $station->PopularityIndex,
                        'utcTimeOffset' => $station->UtcTimeOffset,
                        'description' => $station->Description,
                        'location' => $station->Location != null ?[
                            'lat' =>  $station->Location->Latitude,
                            'lon' => $station->Location->Longitude
                        ]:[]
                    ],
                ]);

                if(isset($customFields) && isset($customFields[$station->TransportNodeId])){
                    $stationModel->custom = $customFields[$station->TransportNodeId];
                }

                $stationModel->save();
                unset($stationModel);
                $bar->advance();
            }

            unset($data);

            if($bar){
                $bar->finish();
                $this->line('');
                $this->info('Автобусных остановок добавлено: ' . $stationsInserted);
                $this->info('Автобусных остановок обновлено: ' . $stationsUpdated);
                BusStop::flushCache(BusStop::class);
            }
        }
    }
}
