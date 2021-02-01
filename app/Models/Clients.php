<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 11.04.2019
 * Time: 15:42
 */

namespace App\Models;


use App\Models\Old\ClientsInHolding;
use Illuminate\Database\Eloquent\Model;

class Clients extends Model
{
    protected $table = 'clients';
    protected $primaryKey = 'clientId';
    protected $fillable = [
        'outerClientId',
        'holdingId',
        'code',
        'inn',
        'name',
        'manager',
        'accounter',
        'paymentAccount',
        'fees',
        'special',
        'isHoldingHead',
        'sourceCreatedAt',
        'sourceUpdatedAt'
    ];

    public function setPaymentAccountAttribute($value)
    {
        $this->attributes['paymentAccount'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function getPaymentAccountAttribute($value)
    {
        return json_decode($value);
    }


    public function setFeesAttribute($value)
    {
        $this->attributes['fees'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function getFeesAttribute($value)
    {
        return json_decode($value);
    }

    public function holding()
    {
        return $this->belongsTo(Holding::class,'holdingId','holdingId');
    }

    public function departments()
    {
        return $this->hasMany(ClientDepartments::class,'clientId','clientId');
    }

    public function fillFrom1C($client)
    {
        $clientHoldingModel = ClientsInHolding::where('client_id', $client->onecCode)->first();

        $data = [
            'outerClientId' => $client->clientId,
            'holdingId' => $clientHoldingModel ? $clientHoldingModel->holding_id : 0,
            'code' => $client->onecCode,
            'inn' => $client->inn,
            'name' => $client->name,
            'manager' => $client->manager,
            'accounter' => $client->accounter,
            'paymentAccount' => $client->paymentAccount,
            'fees' => $client->fees,
            'special' => $client->special,
            'isHoldingHead' => $clientHoldingModel ? ($clientHoldingModel->is_main ? 'true': 'false' ) : 'false',
            'sourceCreatedAt' => $client->created,
            'sourceUpdatedAt' => $client->updated
        ];

        $this->fill($data);


    }

    public function updateDepartments($departments)
    {
        $departmentsIds = [];

        foreach ($departments as $department) {

            $departmentModel = ClientDepartments::where('outerDepartmentId',
                $department->departmentId)->first();
            $departmentsIds[] = $department->departmentId;

            if (!$departmentModel) {
                $departmentModel = new ClientDepartments();
            }

            $dData = [
                'outerDepartmentId' => $department->departmentId,
                'name' => $department->name
            ];

            $departmentModel->fill($dData);
            $this->departments()->save($departmentModel);
        }

        if (count($departmentsIds) > 0) {
            $this->departments()->whereNotIn('outerDepartmentId', $departmentsIds)
                ->update(['isActive' => 'false']);
        }
    }
}