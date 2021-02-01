<?php

namespace App\Models\References;

use Illuminate\Database\Eloquent\Model;

class TrainsCar extends Model
{
    protected $table = 'trains_car';
    protected $primaryKey = 'id';

    protected $fillable = [
        'typeRu',
        'typeEn',
        'description',
        'trainName',
        'typeScheme',
        'schemes',
        'train_id',
        'isAddedManually',
    ];

    protected $casts = [
        'schemes' => 'array',
        'trainName' => 'string'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function trains()
    {
        return $this->belongsTo(Trains::class, 'train_id', 'id');
    }

    const DEFAULT_SCHEME_KEY = 'default';
}