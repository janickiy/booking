<?php

namespace App\Services\Payments;

use App\Services\External\RSB\RsbPaymentAPI;
use App\Services\External\Payture\PaytureAPI;
use App\Helpers\{
    StringHelpers
};

class DmsPayFacade
{
    private $transId;

    public $provider;

    public $parameter;

    public $amount;

    public $email;

    public $cardname;

    public $pan;

    public $expiry;

    public $cvc2;

    public $orderId;

    public $expiryYear;

    public $expiryMonth;

    protected static $Error;

    protected static $Response;

    public function __construct($parameter)
    {

        if (isset($parameter['pan'])) {
            $this->provider = $this->isAmericanExpress($parameter['pan']) ? 'rsb' : 'payture';
            $this->pan = $parameter['pan'];
        }

        if (isset($parameter['provider'])) {
            $this->provider = $parameter['provider'];
        }

        if (isset($parameter['orderId'])) {
            $this->orderId = $parameter['orderId'];
        }

        if (isset($parameter['amount'])) {
            $this->amount = $parameter['amount'];
        }

        if (isset($parameter['email'])) {
            $this->email = $parameter['email'];
        }

        if (isset($parameter['cardname'])) {
            $this->cardname = $parameter['cardname'];
        }

        if (isset($parameter['expiryYear'])) {
            $this->expiryYear = $parameter['expiryYear'];
        }

        if (isset($parameter['expiryMonth'])) {
            $this->expiryMonth = $parameter['expiryMonth'];
        }

        if (isset($parameter['cvc2'])) {
            $this->cvc2 = $parameter['cvc2'];
        }

        if (isset($parameter['orderId'])) {
            $this->orderId = $parameter['orderId'];
        }
    }

    /**
     * @param $transId
     */
    private function setTransId($transId)
    {
        $this->transId = $transId;
    }

    public function getTransId()
    {
        return $this->transId;
    }

    /**
     * @return array|bool
     */
    public function block()
    {
        switch ($this->provider) {
            case 'rsb':

                $expiry = $this->expiryYear . $this->expiryMonth;
                $response = RsbPaymentAPI::CardDMSAuth($this->amount, $this->email, $this->cardname, $this->pan,
                    $expiry, $this->cvc2);

                static::$Response = $response;

                if (isset($response['error'])) {

                    static::$Error = $response['error'];

                    return false;
                }

                $transId = trim($response['TRANSACTION_ID']);
                $this->setTransId($transId);

                return true;

                break;

            case 'payture':

                $payInfo = [
                    "PAN" => $this->pan,
                    "EMonth" => $this->expiryMonth,
                    "EYear" => $this->expiryYear,
                    "CardHolder" => $this->cardname,
                    "SecureCode" => $this->cvc2,
                    "OrderId" => $this->orderId
                ];

                $this->setTransId($this->orderId);

                $response = PaytureAPI::Block($this->orderId, $this->amount, $payInfo);
                $response = StringHelpers::ObjectToArray($response);

                static::$Response = $response;

                if (isset($response['error'])) {

                    static::$Error = $response['error'];

                    return false;
                }

                return true;

                break;

            default:

                static::$Error = 'Не найден провайдер';

                return false;
        }
    }

    public function block3DS($params)
    {
        switch ($this->provider) {
            case 'rsb':

                if(!$this->status()){
                    return false;
                }

                if(self::$Response['3DSECURE']==='FAILED'){
                    return false;
                }

                return true;
                break;
            case 'payture':
                $response = PaytureAPI::Block3DS($this->orderId, $params['paRes']);
                $response = StringHelpers::ObjectToArray($response);

                static::$Response = $response;

                if (isset($response['error'])) {

                    static::$Error = $response['error'];

                    return false;
                }

                return true;
                break;
            default:
                static::$Error = 'Не найден провайдер';
                return false;
        }
    }

    /**
     * @return array|bool
     */
    public function charge()
    {
        switch ($this->provider) {
            case 'rsb':

                $response = RsbPaymentAPI::MakeDMSTransaction($this->transId, $this->amount, $this->orderId);

                static::$Response = $response;

                if (isset($response['error'])) {

                    static::$Error = $response['error'];

                    return false;
                }

                break;

            case 'payture':

                $response = PaytureAPI::Charge($this->orderId);
                $response = StringHelpers::ObjectToArray($response);

                static::$Response = $response;

                if (isset($response['error'])) {

                    static::$Error = $response['error'];

                    return false;
                }

                break;

        }

        return true;
    }

    /**
     * @return array|bool
     */
    public function reverse()
    {
        switch ($this->provider) {
            case 'rsb':

                $response = RsbPaymentAPI::reverseTransaction($this->transId, $this->amount);

                static::$Response = $response;

                if (isset($response['error'])) {

                    static::$Error = $response['error'];

                    return false;
                }

                break;

            case 'payture':

                $response = PaytureAPI::Unblock($this->orderId, $this->amount);
                $response = StringHelpers::ObjectToArray($response);

                static::$Response = $response;

                if (isset($response['error'])) {

                    static::$Error = $response['error'];

                    return false;
                }

                break;
        }

        return true;
    }

    public function status()
    {
        switch ($this->provider) {
            case 'rsb':

                $response = RsbPaymentAPI::Status($this->transId);

                static::$Response = $response;

                if (isset($response['error'])) {

                    static::$Error = $response['error'];

                    return false;
                }

                break;

            case 'payture':

                $response = PaytureAPI::GetState($this->orderId);
                $response = StringHelpers::ObjectToArray($response);

                static::$Response = $response;

                if (isset($response['error'])) {

                    static::$Error = $response['error'];

                    return false;
                }

                break;
        }

        return true;
    }

    /**
     * @param $num
     * @return bool
     */
    public function isAmericanExpress($num)
    {
        switch (substr($num, 0, 2)) {
            case 34:
            case 37:
                return true;
                break;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public static function getError()
    {
        return static::$Error;
    }

    public static function getResponse()
    {
        return static::$Response;
    }
}