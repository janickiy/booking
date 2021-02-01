<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'userId';

    protected $appends = ['passenger','role'];

    protected $fillable = [
        'userTypeId',
        'holdingId',
        'clientId',
        'password',
        'email',
        'mobile',
        'login',
        'isVerifiedMobile',
        'twoFactor',
        'contacts',
        'lastAccessIp',
        'allowedIp',
        'last_activity_at',
        'last_login_at'
    ];

    protected $visible = [
        'userId',
        'userTypeId',
        'holdingId',
        'clientId',
        'email',
        'mobile',
        'passenger',
        'role',
        'isVerifiedMobile',
        'twoFactor',
        'contacts',
        'lastAccessIp',
        'allowedIp',
        'last_activity_at',
        'last_login_at',
        'created_at'
    ];


    protected $hidden = [
        'password', 'remember_token', 'login', 'allowedIp'
    ];

    protected static $contactsValidationMessages = [
        'contactEmails.required' => 'Контактный E-mail не может быть пустым',
        'contactEmails.email' => 'Контактный E-mail указан неверно',
        'firstName.required' => 'Имя не может быть пустым',
        'lastName.required' => 'Фамилия не может быть пустой',
    ];

    protected static $accessLevels = [
        'self' => 0,
        'user' => 1,
        'client' => 2,
        'holding' => 3,
        'all' => 4
    ];

    public static $can = [
        'r' => 1 << 0,
        'w' => 1 << 1,
        'c' => 1 << 2,
        'd' => 1 << 3
    ];

    public $validationErrors;

    /**
     * @return array
     */
    protected static function getContactsValidationRules()
    {
        return [
            'contactEmails' => 'required|email',
            'firstName' => 'required',
            'lastName' => 'required'
        ];
    }

    /**
     * @param $value
     */
    public function setContactsAttribute($value)
    {
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

    /**
     * @param $value
     */
    public function setAllowedIpAttribute($value)
    {
        $this->attributes['allowedIp'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getAllowedIpAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|null|object
     */
    public function getPassengerAttribute()
    {
        return $this->passengers()->where('contacts->email', $this->email)->first();
    }

    /**
     * @return array
     */
    public function getRoleAttribute()
    {
        $roles = [];

        foreach ($this->roles as $role) {
            $roles[] = ['roleId' => $role->roleId, 'name' => $role->name, 'description' => $role->description];
        }

        return $roles;
    }

    public function getEnabledServices()
    {

    }


    /**
     * @param int $rights
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function passengers($rights = 1)
    {
        $access = $this->access('passengers', $rights);

        switch ($access) {
            case 'self':
                return $this->hasMany(Passengers::class, 'userId', 'userId');
                break;
            case 'user':
                return Passengers::query()->where('clientId', 0);
                break;
            case 'client':
                return Passengers::query()->where('clientId', $this->clientId);
                break;
            case 'holding':
                return Passengers::query()->where('holdingId', $this->holdingId);
                break;
            case 'all':
                return Passengers::query();
                break;
        }
        return $this->hasMany(Passengers::class, 'userId', 'userId');
    }


    /**
     * @param int $rights
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders($rights = 1)
    {
        $access = $this->access('orders', $rights);

        switch ($access) {
            case 'self':
                return $this->hasMany(Orders::class, 'userId', 'userId');
                break;
            case 'user':
                return Orders::query()->where('clientId', 0);
                break;
            case 'client':
                return Orders::query()->where('clientId', $this->clientId);
                break;
            case 'holding':
                return Orders::query()->where('holdingId', $this->holdingId);
                break;
            case 'all':
                return Orders::query();
                break;
        }

        return $this->hasMany(Orders::class, 'userId', 'userId');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function roles()
    {
        return $this->hasManyThrough(Role::class, UserRole::class, 'userId', 'roleId', 'userId', 'roleId');
    }

    /**
     * @param $data
     * @return bool
     */
    public function validateContacts($data)
    {
        $validator = Validator::make($data, static::getContactsValidationRules(), static::$contactsValidationMessages);

        if ($validator->fails()) {
            $this->validationErrors = $validator->errors()->toArray();
            return false;
        }

        return true;
    }

    public function services()
    {
        $roles = $this->roles;
        $services = [];
        $fromRole = false;
        foreach ($roles as $role){
            foreach ($role->services as $serviceName => $service){
                if($service->enabled) {
                    array_push($services, $serviceName);
                }
                $fromRole = true;
            }
        }

        if(!$fromRole){
            foreach (Role::$servicesDefaults as $serviceName => $service){
                if($service['enabled']) {
                    array_push($services, $serviceName);
                }
            }
        }

        return array_unique($services);
    }

    /**
     * @param $type
     * @param $rights
     * @return int|string
     */
    public function access($type, $rights)
    {
        $roles = $this->roles;

        if ($roles->count() < 1) {
            return 'self';
        }

        $access = 'self';

        foreach ($roles as $role) {
            if (!isset($role->accessMask->$type)) continue;
            $roleAccess = 'self';
            foreach ($role->accessMask->$type as $key => $right) {
                if ((($right & $rights) === $rights) && static::$accessLevels[$key] > static::$accessLevels[$roleAccess]) $roleAccess = $key;
            }

            if (static::$accessLevels[$roleAccess] > static::$accessLevels[$access]) $access = $roleAccess;
        }

        return $access;
    }


    /**
     * @param $value
     */
    public function setTwoFactorAttribute($value)
    {
        $this->attributes['twoFactor'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getTwoFactorAttribute($value)
    {
        return json_decode($value);
    }

}