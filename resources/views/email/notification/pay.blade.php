<!DOCTYPE html>
<html>

<head>
    <title>Ваш заказ оплачен</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
</head>

<body>
<div style="padding:35px 75px 35px 35px;max-width: 720px;margin: 0 auto;">
    <table width="100%" style="padding:0;margin:0;border:none;cellpadding:0;cellspacing:0;">
        <tr>
            <td style="padding:0;margin:0;padding-bottom:45px;padding-top:10px;border:none;cellpadding:0;cellspacing:0;width:70px;vertical-align: top;">
                <img src="{{url('images/email/logo.png')}}" border="0"/>
            </td>
            <td style="padding:0;padding-bottom:45px;margin:0;border:none;cellpadding:0;cellspacing:0;vertical-align: top;">
                <div style="text-transform:uppercase;color:#28B759;font-size:13px;line-height:18px;font-family:roboto,arial,sans-serif;margin-bottom: 15px;">
                    ваш заказ оплачен
                </div>
                <div style="color:#333333;font-size:20px;line-height:26px;font-family:roboto,arial,sans-serif;margin-bottom: 15px;">
                    Уважаемый путешественник
                </div>
                <div style="color:#333333;font-size:15px;line-height:26px;font-family:roboto,arial,sans-serif;">
                    <div style="margin-bottom: 20px;">Ваш Заказ №{{ $orders['orderId'] }} был оплачен.</div>
                </div>
            </td>
        </tr>
    </table>
    <div style="margin-left: 30px;border: 1px solid #B5C6D8;padding: 24px 30px 40px 30px;">
        <table width="100%" style="padding:0;margin:0;border:none;cellpadding:0;cellspacing:0;">

            @foreach($orders['items'] as $order)

                @if(isset($order[0]) && $order[0])

                    <tr>
                        <td style="padding-bottom:20px;vertical-align: top;border:none;cellpadding:0;cellspacing:0;">
                            <div style="color: #FB722A;text-transform: uppercase;font-size:13px;line-height:24px;font-weight:bold;font-family:roboto,arial,sans-serif;margin-bottom: 10px;">
                                ТУДА
                            </div>
                            @if(isset($order[0]['OriginStation']))
                            <div style="color: #333;font-size:15px;line-height:24px;font-weight:bold;font-family:roboto,arial,sans-serif;margin-bottom: 10px;">
                                Из {{ $order[0]['OriginStation'] }}
                            </div>
                            @endif
                            <div style="color: #B5C6D8;font-size:15px;line-height:24px;font-weight:bold;font-family:roboto,arial,sans-serif;">{{ count($order[0]['Passengers']) }}
                                пассажир@if(count($order[0]['Passengers']) > 1)а@endif
                            </div>
                        </td>
                        <td style="text-align: right;padding-bottom:20px;vertical-align: top;border:none;cellpadding:0;cellspacing:0;width: 60px;">
                            @if(isset($order[0]['DepartureDateTime']))
                            <div style="color: #B5C6D8;text-transform: uppercase;font-size:13px;line-height:24px;font-weight:bold;font-family:roboto,arial,sans-serif;margin-bottom: 15px;">{{ App\Helpers\DateTimeHelpers::dateTimeFormat($order[0]['DepartureDateTime'],app()->getLocale()) }}</div>
                            <div style="color: #333;font-size:15px;line-height:24px;font-weight:bold;font-family:roboto,arial,sans-serif;">
                                Поезд {{ $order[0]['BookingTrainNumber'] }}, {{ $order[0]['Carrier'] }}</div>
                            @endif
                        </td>
                    </tr>

                @endif

                @if(isset($order[1]) && $order[1])
                    <tr>
                        <td style="padding-bottom:20px;vertical-align: top;border:none;cellpadding:0;cellspacing:0;">
                            <div style="color: #FB722A;text-transform: uppercase;font-size:13px;line-height:24px;font-weight:bold;font-family:roboto,arial,sans-serif;margin-bottom: 10px;">
                                обратно
                            </div>
                            <div style="color: #333;font-size:15px;line-height:24px;font-weight:bold;font-family:roboto,arial,sans-serif;margin-bottom: 10px;">
                                Из {{ $order[1]['OriginStation'] }}</div>
                            <div style="color: #B5C6D8;font-size:15px;line-height:24px;font-weight:bold;font-family:roboto,arial,sans-serif;">{{ count($order[1]['Passengers']) }}
                                пассажир@if(count($order[1]['Passengers']) > 1)а@endif
                            </div>
                        </td>
                        <td style="text-align: right;padding-bottom:20px;vertical-align: top;border:none;cellpadding:0;cellspacing:0;width: 60px;">
                            @if(isset($order[1]['DepartureDateTime']))
                            <div style="color: #B5C6D8;text-transform: uppercase;font-size:13px;line-height:24px;font-weight:bold;font-family:roboto,arial,sans-serif;margin-bottom: 15px;">
                                {{ App\Helpers\DateTimeHelpers::dateTimeFormat($order[1]['DepartureDateTime'],app()->getLocale()) }}
                            </div>
                            @endif
                            <div style="color: #333;font-size:15px;line-height:24px;font-weight:bold;font-family:roboto,arial,sans-serif;">
                                Поезд {{ $order[1]['BookingTrainNumber'] }}, {{ $order[1]['Carrier'] }}</div>
                        </td>
                    </tr>

                @endif

            @endforeach

            <tr>
                <td style="padding-bottom:40px;vertical-align: top;border:none;cellpadding:0;cellspacing:0;width: 60px;">
                    <div style="color: #8C8C8C;font-size: 29px;line-height: 34px;font-family:roboto,arial,sans-serif;">
                        Оплачено
                    </div>
                </td>
                <td style="text-align: right;padding-bottom:40px;vertical-align: top;border:none;cellpadding:0;cellspacing:0;width: 60px;">
                    <div style="color: #333;font-family:roboto,arial,sans-serif;margin-bottom:20px;font-weight:bold;">
                        <span style="font-size: 29px;line-height: 34px;">{{ $orders['Amount'] }}</span> <span
                                style="font-size: 21px;line-height: 34px;"> руб.</span></div>
                    <div style="color: #A1A1A1;font-size: 11px;font-family:roboto,arial,sans-serif;">В том числе Сервисный сбор - {{ $orders['Tax'] }} руб
                    </div>
                    <div style="color: #A1A1A1;font-size: 11px;font-family:roboto,arial,sans-serif;">Комиссия за банковские транзакции - {{ $orders['Сommission'] }} руб
                    </div>
                </td>
            </tr>
        </table>
        <div style="text-align: center;">
            <a href="{{ URL::route('profile.order',['id' => $orders['complexOrderId']]) }}"><span style="display: inline-block;font-weight: bold;vertical-align: top;width: 210px;height: 36px;background-color: #fff;color:#fb722a;border: 2px solid #fb722a;font-size:15px;line-height: 38px;text-align: center;font-family:roboto,arial,sans-serif;">Перейти в заказ</span></a>
        </div>
    </div>
    <div style="color: #8C8C8C;font-size: 11px;line-height:18px;font-family:roboto,arial,sans-serif;text-align: center;padding: 40px 0 0;">
        <span style="vertical-align: middle;margin-right:20px;">Мы принимаем</span>
        <img src="{{url('images/email/visa.png')}}" style="vertical-align: middle;margin-right:20px;" alt="visa" border="0"/>
        <img src="{{url('images/email/verified-by-visa.png')}}" style="vertical-align: middle;margin-right:20px;" alt="verified-by-visa" border="0"/>
        <img src="{{url('images/email/mastercard.png')}}" alt="mastercard" style="vertical-align: middle;margin-right:20px;" border="0">
        <img src="{{url('images/email/maestro.png')}}" alt="maestro" border="0" style="vertical-align: middle;margin-right:20px;"/>
        <img src="{{url('images/email/jcb.png')}}" alt="jcb" border="0" style="vertical-align: middle;margin-right:20px;"/>
        <img src="{{url('images/email/diners-club.png')}}" alt="diners-club" border="0" style="vertical-align: middle;margin-right:20px;"/>
        <img src="{{url('images/email/amex.png')}}" alt="amex" border="0" style="vertical-align: middle;"/>
    </div>
    <div style="padding-top: 40px;padding-left: 30px;text-align: right;margin-bottom: 20px;">
        <div style="font-style:italic;font-size: 15px;line-height:18px;font-family:roboto,arial,sans-serif;margin-bottom: 15px;">
            С пожеланиями хороших путешествий
        </div>
        <img src="{{url('images/email/logo-tlr.png')}}" alt="logo-tlr" border="0"/>
    </div>
    <table width="100%" style="padding:0;margin:0;border:none;cellpadding:0;cellspacing:0;">
        <tr>
            <td style="padding-top:40px;vertical-align: top;border:none;cellpadding:0;cellspacing:0;">
                <div style="font-size: 15px;line-height:18px;font-family:roboto,arial,sans-serif;font-weight:bold;color: #333; margin-bottom: 5px;">
                    8 (800) 700-81-80
                </div>
                <div style="color: #B5C6D8;font-size: 11px;line-height: 18px;font-family:roboto,arial,sans-serif;">
                    Круглосуточная служба поддержки
                </div>
            </td>
            <td style="padding-top:40px;text-align: right;vertical-align: top;border:none;cellpadding:0;cellspacing:0;">
                <div style="font-size: 11px;line-height:18px;font-family:roboto,arial,sans-serif;font-weight:bold;color: #333;margin-bottom: 5px;">
                    Москва, Балтийская 9
                </div>
                <div style="font-family:roboto,arial,sans-serif;font-size: 11px;line-height: 18px;"><a href=""  style="color: #8C8C8C;text-decoration:none;">Изменить  настройки оповещений</a>&nbsp;&nbsp;&nbsp;<a href=""  style="color: #8C8C8C;text-decoration:none;">Карта  офисов</a></div>
            </td>
        </tr>
    </table>
</div>
</body>
</html>