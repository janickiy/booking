<?php

namespace App\Models;

use App\Models\Traits\ValidateJson;
use Illuminate\Database\Eloquent\Model;

class Passengers extends Model
{
    use ValidateJson;

    protected $table = 'passengers';
    protected $primaryKey = 'passengerId';

    protected $appends = ['nameRu', 'nameEn'];

    protected $fillable = [
        'userId',
        'holdingId',
        'clientId',
        'nameRu',
        'nameEn',
        'contacts',
        'documents',
        'cards'
    ];

    protected $visible = [
        'passengerId',
        'nameRu',
        'nameEn',
        'contacts',
        'documents',
        'cards',
    ];

    protected static $dataSchemes = [
        'contacts' => [
            'email' => false,
            'phone' => false,
        ],
        'documents' => [
            'documentType' => true,
            'documentCountry' => true,
            'documentNumber' => true,
            'documentBefore' => false
        ],
        'cards' => [
            'cardType' => false,
            'cardName' => false,
            'cardNumber' => false
        ],
        'nameRu' => [
            'firstName' => true,
            'middleName' => true,
            'lastName' => true
        ],
        'nameEn' => [
            'firstName' => false,
            'middleName' => false,
            'lastName' => false
        ]
    ];

    /**
     * @param $value
     */
    public function setContactsAttribute($value)
    {
        $value = static::prepareScheme('contacts', $value);
        $this->attributes['contacts'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getContactsAttribute($value)
    {
        return json_decode($value);
    }

    public function setNameRuAttribute($value)
    {
        $value = static::prepareScheme('nameRu', $value);
        $this->attributes['nameRu'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getNameRuAttribute()
    {
        return json_decode($this->attributes['nameRu']);
    }

    public function setNameEnAttribute($value)
    {
        $value = static::prepareScheme('nameEn', $value);
        $this->attributes['nameEn'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getNameEnAttribute()
    {
        return json_decode($this->attributes['nameEn']);
    }

    /**
     * @param $value
     */
    public function setDocumentsAttribute($value)
    {
        $value = static::prepareScheme('documents', $value);
        $this->attributes['documents'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getDocumentsAttribute($value)
    {
        return json_decode($value);
    }

    public function setCardsAttribute($value)
    {
        $value = static::prepareScheme('cards', $value);
        $this->attributes['cards'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getCardsAttribute($value)
    {
        return json_decode($value);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'userId');
    }
}