<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 10.04.2018
 * Time: 14:49
 */

namespace App\Models\References\Traits;

use App\Helpers\StringHelpers;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait ByKeywords
 * @package App\Models\References\Traits
 * @method static Builder byKeyword($searchString, $side = 'right')
 */
trait ByKeywords
{
    public function scopeByKeyword(Builder $query, $searchString, $add = 'right')
    {
        switch ($add) {
            case 'right':
                $left = '';
                $right = '%';
                $op = 'ilike';
                break;
            case 'left':
                $left = '%';
                $right = '';
                $op = 'ilike';
                break;
            case 'both':
                $left = '%';
                $right = '%';
                $op = 'ilike';
                break;
            case 'strict':
            default:
                $left = '';
                $right = '';
                $op = '=';
                break;
        }

        $intSearchString = (int)$searchString;

        if ("$intSearchString" === $searchString) {
            if ($this->table == 'city') {
                $result = $query->whereHas('stations', function ($q) use ($searchString) {
                    $q->where('code', $searchString);
                })->orWhere('info->expressCode', $searchString);

            } elseif ($this->table == 'railway_station') {
                $result = $query->where('code', $searchString);
            } else {
                $result = $query;
            }
        } else {
            $result = $query->where('nameRu', $op, $left . $searchString . $right)
                ->orWhere('nameEn', $op, $left . $searchString . $right)
                ->orWhere('nameRu', $op, $left . StringHelpers::remap($searchString) . $right)
                ->orWhere('nameEn', $op, $left . StringHelpers::remap($searchString) . $right);
            if ($this->table == 'city') {
                $result = $result->orWhereHas('airports', function ($q) use ($searchString) {
                    $q->where('code', 'ilike', $searchString);
                })->orWhere('code', 'ilike', $searchString);
            }
        }

        return $result;
    }
}