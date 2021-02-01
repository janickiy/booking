<?php

namespace App\Http\Formatters\Front\V1;

use App\Http\Formatters\BaseFormatter;
use App\Helpers\LangHelper;
use Illuminate\Support\Collection;

class CarPricingFormatter extends BaseFormatter
{
    protected static $allowedRelations = [];

    protected $relations = [];

    /**
     * @param Collection $car
     * @return array|null
     *
     */
    public  function __invoke($car): ?array
    {
        if (!$car) {
            return null;
        }

        $totalTax = (float) getSetting('taxRailwayImRzdPurchase') + (float) getSetting('taxRailwayImTrivagoPurchase');
        
        $data = [
            'info' => [
                //Здесь вывести всю нужную информацию касающуюся самого вагона
                'klass'                         => $car[0]['ServiceClass'],
                'services'                      => $car[0]['Services'],
                'serviceClassTranscript'        => $car[0]['ServiceClassTranscript'],
                'carType'                       => $car[0]['CarType'],
                'CarTypeDisplay'                => LangHelper::trans('references/im.carTypes.' . $car[0]['CarType']),
                'carTypeName'                   => $car[0]['CarTypeName'],
                'carDescription'                => $car[0]['CarDescription'],
                'genderCabins'                  => $car->contains('HasGenderCabins', 'true'),
                'placeQuantity'                 => $car->pluck('PlaceQuantity')->sum(),
                'isTwoStorey'                   => $car[0]['IsTwoStorey'],
                'serviceCost'                   => $car[0]['ServiceCost'],
                'carrier'                       => $car[0]['Carrier'],
                'isBeddingSelectionPossible'    => $car[0]['IsBeddingSelectionPossible'],
                'hasElectronicRegistration'     => $car[0]['HasElectronicRegistration'],
                'rzhdCardTypes'                 => $car[0]['RzhdCardTypes'],
                'minPrice'                      => $car->pluck('MinPrice')->min() + $totalTax,
            ],
            'cars' => $car->groupBy('CarNumber')->map(function (Collection $currentCars) use ($totalTax) {
                //Здесь выводим информацию касающуюся самого места
                return $currentCars->map(function ($currentCar) use ($totalTax) {
                    $freePlaces = explode(', ', $currentCar['FreePlaces']);
                    $places = [];

                    foreach ($freePlaces as $freePlace) {
                        $num = preg_match('/(\d*)(\D).*$/miu', $freePlace, $matches);
                        $places[$freePlace] = [
                            'place'                 => $num ? $matches[1]: $freePlace,
                            'minPrice'              =>  isset($currentCar['MinPrice']) && $currentCar['MinPrice'] > 0 ? $currentCar['MinPrice'] + $totalTax : null,
                            'maxPrice'              =>  isset($currentCar['MaxPrice']) && $currentCar['MaxPrice'] > 0 ? $currentCar['MaxPrice'] + $totalTax : null,
                            'carPlaceType'          => $currentCar['CarPlaceType'],
                            'placeReservationType'  => $currentCar['PlaceReservationType'],
                            'modificator'           => $num ? $matches[2] : false
                        ];
                    }
                    return $places;
                })->flatten(1)->keyBy('place');
            }),
        ];

        return $data;
    }
}
