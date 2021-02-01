<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 16.04.2019
 * Time: 11:14
 */

namespace App\Http\Controllers\Api;


use App\Helpers\ResponseHelpers;
use App\Models\Clients;
use Illuminate\Http\Request;

class OneCPushController extends BaseController
{

    public function client(Request $request)
    {
        $client = $request->get('client', false);

        if (!$client) {
            return ResponseHelpers::jsonResponse(['error' => ['text' => 'Не найдены данные контрагента в теле запроса']],
                400);
        }

        try {
            $client = json_decode(json_encode($client, JSON_UNESCAPED_UNICODE));

            $clientModel = Clients::where('outerClientId', $client->clientId)->first();


            if (!$clientModel) {
                $clientModel = new Clients();
                $status = 201;
            } else {
                $status = 202;
            }


            $clientModel->fillFrom1C($client);
            $clientModel->save();

            $clientModel->updateDepartments($client->departments);

            return ResponseHelpers::jsonResponse([
                'client' => [
                    'clientSiteId' => $clientModel->clientId,
                    'clientId' => $clientModel->outerClientId
                ]
            ], $status);
        } catch (\Exception $e) {
            return ResponseHelpers::jsonResponse(['error' => ['text' => $e->getMessage()]], 500);
        }
    }

    public function paymentAccount(Request $request)
    {
        $client = $request->get('client', false);

        if (!$client) {
            return ResponseHelpers::jsonResponse(['error' => ['text' => 'Не найдены данные платежной информации контрагента в теле запроса']],
                400);
        }

        try {
            $client = json_decode(json_encode($client, JSON_UNESCAPED_UNICODE));

            $clientModel = Clients::where('outerClientId', $client->clientId)->first();

            if (!$clientModel) {
                return ResponseHelpers::jsonResponse(['error' => ['text' => "Контрагент с clientId {$client->clientId} не найден"]],
                    404);
            }


            $clientModel->paymentAccount = $client->paymentAccount;
            $clientModel->save();

            return ResponseHelpers::jsonResponse([
                'client' => [
                    'clientSiteId' => $clientModel->clientId,
                    'clientId' => $clientModel->outerClientId,
                    'paymentAccount' => $clientModel->paymentAccount
                ]
            ], 202);

        } catch (\Exception $e) {
            return ResponseHelpers::jsonResponse(['error' => ['text' => $e->getMessage()]], 500);
        }
    }
}