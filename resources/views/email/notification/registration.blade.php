<!DOCTYPE html>
<html>
<head>
    <title>Вы зарегистрировались на trivago.ru</title>
    <meta charset="utf-8">
    <meta description="">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
</head>

<body>
<div style="padding:10;max-width: 720px;margin: 0 auto;">
    <table width="100%" style="padding:0;margin:0;border:none;cellpadding:0;cellspacing:0;">
        <tr>
            <td style="padding:0;margin:0;padding-bottom:45px;padding-top:10px;border:none;cellpadding:0;cellspacing:0;width:70px;vertical-align: top;">
                <img src="{{url('images/email/logo.png')}}" border="0" />
            </td>
            <td style="padding:0;padding-bottom:45px;margin:0;border:none;cellpadding:0;cellspacing:0;vertical-align: top;">
                <div style="text-transform:uppercase;color:#28B759;font-size:13px;line-height:18px;font-family:roboto,arial,sans-serif;margin-bottom: 15px;">Успешная регистрация</div>
                <div style="color:#333333;font-size:20px;line-height:26px;font-family:roboto,arial,sans-serif;margin-bottom: 15px;">Уважаемый путешественник</div>
                <div style="color:#333333;font-size:15px;line-height:26px;font-family:roboto,arial,sans-serif;">
                    <div style="margin-bottom: 20px;">Вы успешно зарегистрировались на портале путешествий <a href="https://trivago.ru">trivago.ru</a>.</div>
                </div>
                <div style="color:#333333;font-size:15px;font-family:roboto,arial,sans-serif;">
                    Для входа в личный кабинет и работы с бронированиями, используйте Ваши реквизиты:<br />
                    <strong>Ваш логин: {{$login}}</strong> <br />
                    <strong>Ваш пароль: {{$password}}</strong> <br />
                </div>
            </td>
        </tr>

    </table>


    <div style="padding-top: 40px;padding-left: 30px;text-align: right;margin-bottom: 20px;">
        <div style="font-style:italic;font-size: 15px;line-height:18px;font-family:roboto,arial,sans-serif;margin-bottom: 15px;">С пожеланиями хороших путешествий</div>
        <img src="{{url('images/email/logo-tlr.png')}}" alt="logo-tlr" border="0" />
    </div>
    <table width="100%" style="padding:0;margin:0;border:none;cellpadding:0;cellspacing:0;">
        <tr>
            <td style="padding-top:20px;vertical-align: top;border:none;cellpadding:0;cellspacing:0;">
                <div style="font-size: 15px;line-height:18px;font-family:roboto,arial,sans-serif;font-weight:bold;color: #333; margin-bottom: 5px;">8 (800) 700-81-80</div>
                <div style="color: #B5C6D8;font-size: 11px;line-height: 18px;font-family:roboto,arial,sans-serif;">Круглосуточная служба поддержки</div>
            </td>
            <td style="padding-top:20px;text-align: right;vertical-align: top;border:none;cellpadding:0;cellspacing:0;">
                <div style="font-size: 11px;line-height:18px;font-family:roboto,arial,sans-serif;font-weight:bold;color: #333;margin-bottom: 5px;">Москва, Балтийская 9</div>
                <div style="font-family:roboto,arial,sans-serif;font-size: 11px;line-height: 18px;"><a href="" style="color: #8C8C8C;text-decoration:none;">Изменить настройки оповещений</a>&nbsp;&nbsp;&nbsp;<a href="" style="color: #8C8C8C;text-decoration:none;">Карта офисов</a></div>
            </td>
        </tr>
    </table>
</div>

</body>

</html>