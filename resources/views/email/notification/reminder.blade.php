<!DOCTYPE html>
<html>

<head>
    <title>Название страницы</title>
    <meta charset="utf-8">
    <meta description="">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
</head>

<body>
<div style="padding:35px 75px 35px 35px;max-width: 720px;margin: 0 auto;">
    <table width="100%" style="padding:0;margin:0;border:none;cellpadding:0;cellspacing:0;">
        <tr>
            <td style="padding:0;margin:0;padding-bottom:45px;padding-top:10px;border:none;cellpadding:0;cellspacing:0;width:70px;vertical-align: top;">
                <img src="{{url('images/email/logo.png')}}" border="0" />
            </td>
            <td style="padding:0;padding-bottom:45px;margin:0;border:none;cellpadding:0;cellspacing:0;vertical-align: top;">
                <div style="text-transform:uppercase;color:#FB722A;font-size:13px;line-height:18px;font-family:roboto,arial,sans-serif;margin-bottom: 15px;">Напоминание о поездке</div>
                <div style="color:#333333;font-size:20px;line-height:26px;font-family:roboto,arial,sans-serif;margin-bottom: 15px;">Уважаемый путешественник</div>
                <div style="color:#333333;font-size:15px;line-height:26px;font-family:roboto,arial,sans-serif;">
                    <div style="margin-bottom: 20px;">напоминаем, что ваша поездка/перелет по маршруту {{$rout}} состоится в ближайшие дни.</div>
                    Рекомендуем проверить указанные при оформлении документы и вещи, необходимые для поездки.
                </div>
            </td>
        </tr>
        <tr>
            <td style="padding:0;margin:0;padding-bottom:45px;border:none;cellpadding:0;cellspacing:0;width:70px;vertical-align: top;">
                <img src="{{url('public/images/email/time.png')}}" alt="time" border="0" />
            </td>
            <td style="padding:0;padding-bottom:45px;padding-top:10px;margin:0;border:none;cellpadding:0;cellspacing:0;vertical-align: top;">
                <div style="margin-bottom: 15px;font-family:roboto,arial,sans-serif;">
                    <span style="display: inline-block;color:#FB722A;font-size:13px;line-height:18px;font-weight:bold;text-align: left;width: 300px;">через {{$data['days_left']}} дня</span>
                    <span style="display: inline-block;color:#B5C6D8;font-size:13px;line-height:18px;font-weight:bold;text-align: left;">{{$data['departure_date']}}</span>
                </div>
                <div style="margin-bottom: 15px;color:#B5C6D8;font-size:10px;line-height:18px;font-weight:bold;font-family:roboto,arial,sans-serif;text-transform:uppercase;">Отправление</div>
                <div style="margin-bottom: 15px;color:#8C8C8C;font-size:13px;line-height:18px;font-weight:bold;font-family:roboto,arial,sans-serif;"><span style="color: #333;font-size: 21px;">{{$data['departure_time']}}</span>  {{$data['departure_city']}}</div>
                <div style="color:#8C8C8C;font-size:10px;line-height:18px;font-weight:bold;font-family:roboto,arial,sans-serif;">
                    <span style="display:inline-block;width: 300px;">{{$data['departure_place']}}</span>
                    <a href="{{$data['how_to_get_link']}}" style="color:#FB722A;text-decoration:none;">КАК ПРОЕХАТЬ?</a>
                </div>
            </td>
        </tr>
        <tr>
            <td style="padding:0;margin:0;padding-bottom:45px;border:none;cellpadding:0;cellspacing:0;width:70px;vertical-align: top;">
                <img src="{{url('images/email/train.png')}}" alt="train" border="0" />
            </td>
            <td style="padding:0;padding-bottom:45px;padding-top:10px;margin:0;border:none;cellpadding:0;cellspacing:0;vertical-align: top;">
                <div style="color: #333;font-size:13px;line-height:18px;font-weight:bold;font-family:roboto,arial,sans-serif;text-transform: uppercase;margin-bottom: 15px;">{{$data['traian_number']}}</div>
                <div style="color: #333;font-size:21px;line-height:24px;font-weight:bold;font-family:roboto,arial,sans-serif;">{{$data['route']}}</div>
            </td>
        </tr>
    </table>
    <div style="margin-left: 30px;border: 1px solid #B5C6D8;padding: 24px 30px 40px 30px;">
        <table width="100%" style="padding:0;margin:0;border:none;cellpadding:0;cellspacing:0;">

            @foreach($passangers as $passanger)

            <tr>
                <td style="padding-bottom:20px;vertical-align: top;border:none;cellpadding:0;cellspacing:0;">
                    <div style="color: #333;font-size:15px;line-height:20px;font-weight:700;font-family:roboto,arial,sans-serif;">{{$passanger['lastName']}}<br />{{$passanger['firstName']}} @if(isset($passanger['middleName']) && $passanger['middleName']) {{$passanger['middleName']}} @endif</div>
                </td>
            </tr>
            <tr>
                <td style="padding-bottom:20px;vertical-align: top;border:none;cellpadding:0;cellspacing:0;width: 60px;">
                    <img src="{{url('images/email/pass.png')}}" alt="pass" border="0" />
                </td>
                <td style="padding-bottom:20px;vertical-align: top;border:none;cellpadding:0;cellspacing:0;">
                    <div style="color: #333;font-size:15px;line-height:20px;font-weight:700;font-family:roboto,arial,sans-serif;">{{$passanger['document_number']}}</div>
                    <div style="color:#8C8C8C;font-size:11px;line-height:20px;font-family:roboto,arial,sans-serif;">{{$passanger['document']}}</div>
                </td>
            </tr>
            <tr>
                <td style="padding-bottom:20px;vertical-align: top;border:none;cellpadding:0;cellspacing:0;width: 60px;">

                </td>
                <td style="padding-bottom:20px;vertical-align: top;border:none;cellpadding:0;cellspacing:0;">
                    <div style="color: #333;font-size:15px;line-height:20px;font-weight:700;font-family:roboto,arial,sans-serif;">{{$passanger['place']}}</div>
                </td>
            </tr>

            @endforeach

            <tr>
                <td style="padding-bottom:20px;vertical-align: top;border:none;cellpadding:0;cellspacing:0;width: 60px;">

                </td>
                <td style="padding-bottom:20px;vertical-align: top;border:none;cellpadding:0;cellspacing:0;">
                    <div style="color: #333;font-size:15px;line-height:20px;font-weight:700;font-family:roboto,arial,sans-serif;">Необходимо пройти электронную регистрацию на сайте компании</div>
                </td>
            </tr>
        </table>
        <div style="text-align: center;">
            <a href="" style="text-decoration: none;margin-right: 28px;color:#fff;vertical-align: top;"><span style="display: inline-block;font-weight: bold;vertical-align: top;width: 210px;height: 36px;background-color: #fb722a;color:#fff;border: 2px solid #fb722a;font-size:15px;line-height: 38px;text-align: center;font-family:roboto,arial,sans-serif;">Распечатать билеты</span></a>
            <a href="" style="text-decoration: none;vertical-align: top;"><span style="display: inline-block;font-weight: bold;vertical-align: top;width: 210px;height: 36px;background-color: #fff;color:#fb722a;border: 2px solid #fb722a;font-size:15px;line-height: 38px;text-align: center;font-family:roboto,arial,sans-serif;">Пройти регистрацию</span></a>
        </div>
    </div>
    <div style="padding-top: 40px;padding-left: 30px;text-align: right;margin-bottom: 20px;">
        <div style="font-style:italic;font-size: 15px;line-height:18px;font-family:roboto,arial,sans-serif;margin-bottom: 15px;">С пожеланиями хороших путешествий</div>
        <img src="{{url('images/email/logo-tlr.png')}}" alt="logo-tlr" border="0" />
    </div>
    <table width="100%" style="padding:0;margin:0;border:none;cellpadding:0;cellspacing:0;">
        <tr>
            <td style="padding-top:40px;vertical-align: top;border:none;cellpadding:0;cellspacing:0;">
                <div style="font-size: 15px;line-height:18px;font-family:roboto,arial,sans-serif;font-weight:bold;color: #333; margin-bottom: 5px;">8 (800) 700-81-80</div>
                <div style="color: #B5C6D8;font-size: 11px;line-height: 18px;font-family:roboto,arial,sans-serif;">Круглосуточная служба поддержки</div>
            </td>
            <td style="padding-top:40px;text-align: right;vertical-align: top;border:none;cellpadding:0;cellspacing:0;">
                <div style="font-size: 11px;line-height:18px;font-family:roboto,arial,sans-serif;font-weight:bold;color: #333;margin-bottom: 5px;">Москва, Балтийская 9</div>
                <div style="font-family:roboto,arial,sans-serif;font-size: 11px;line-height: 18px;"><a href="" style="color: #8C8C8C;text-decoration:none;">Изменить настройки оповещений</a>&nbsp;&nbsp;&nbsp;<a href="" style="color: #8C8C8C;text-decoration:none;">Карта офисов</a></div>
            </td>
        </tr>
    </table>
</div>

</body>

</html>