<?php

return [
    'New' => 'Платеж зарегистрирован в шлюзе, но его обработка в процессинге не начата',
    'PreAuthorized3DS' => 'Платеж находится в процессе аутентификации по протоколу 3D Secure',
    'Authorized' => 'Средства заблокированы, но не списаны (2-х стадийный платеж)',
    'Voided' => 'Средства на карте были заблокированы и разблокированы (2-х стадийный платеж)',
    'Charged' => 'Денежные средства списаны с карты Пользователя, платёж завершен успешно',
    'Refunded' => 'Успешно произведен полный возврат денежных средств на карту Пользователя',
    'Forwarded' => 'Платеж был перенаправлен на терминал, указанный в скобках',
    'Rejected' => 'Неуспешный платеж',
    'Error' => 'Последняя операция по платежу завершена с ошибкой',
    'Chargeback' => 'Успешно завершенная транзакция, по которой впоследствии поступила претензия от банка-эмитента',
    'errors' => [
        'NONE' => 'Операция выполнена без ошибок',
        'ACCESS_DENIED' => 'Запрещены операции с данным набором параметров для терминала',
        'AMOUNT_ERROR' => 'Ошибка суммы операции. Превышена сумма либо сумма операции не проверена в биллинге',
        'AMOUNT_EXCEED' => 'Сумма транзакции превышает доступный остаток средств на выбранном счете',
        'API_NOT_ALLOWED' => 'Запрет использования API с данного IP',
        'CARD_EXPIRED' => 'Истек срок действия карты',
        'CARD_NOT_FOUND' => 'Не найдена карта по данному идентификатору',
        'COMMUNICATE_ERROR' => 'Ошибка связи в физических каналах',
        'CURRENCY_NOT_ALLOWED' => 'Валюта не разрешена для предприятия',
        'CUSTOMER_NOT_FOUND' => 'Пользователь не найден',
        'DUPLICATE_ORDER_ID' => 'Номер заказа уже использовался ранее',
        'DUPLICATE_PROCESSING_ORDER_ID' => 'Заказ существует в процессинге с данным идентификатором',
        'DUPLICATE_CARD' => 'Карта уже существует',
        'DUPLICATE_USER' => 'Пользователь уже зарегистрирован',
        'EMPTY_RESPONSE' => 'Пустой ответ процессинга',
        'EMAIL_ERROR' => 'Ошибка при обработке сообщения электронной почты ошибка отправки сообщения',
        'FRAUD_ERROR' => 'Недопустимая транзакция согласно настройкам антифродового фильтра',
        'FRAUD_ERROR_CRITICAL_CARD' => 'Номер карты(BIN, маска) внесен в черный список антифродового фильтра на стороне эмитента',
        'ILLEGAL_ORDER_STATE' => 'Попытка выполнения недопустимой операции для текущего состояния платежа',
        'INTERNAL_ERROR' => 'Внутренняя ошибка шлюза',
        'INVALID_PAYTUREID' => 'Неверный fingerprint устройства',
        'INVALID_SIGNATURE' => 'Неверная подпись запроса',
        'ISSUER_BLOCKED_CARD' => 'Владелец карты пытается выполнить транзакцию, которая для него не разрешена банком-эмитентом, либо произошла внутренняя ошибка эмитента',
        'ISSUER_CARD_FAIL' => 'Банк-эмитент запретил интернет транзакции по карте',
        'ISSUER_FAIL' => 'Владелец карты пытается выполнить транзакцию, которая для него не разрешена банком-эмитентом, либо внутренняя ошибка эмитента',
        'ISSUER_LIMIT_FAIL' => 'Предпринята попытка, превышающая ограничения банка-эмитента на сумму или количество операций в определенный промежуток времени',
        'ISSUER_LIMIT_AMOUNT_FAIL' => 'Предпринята попытка выполнить транзакцию на сумму, превышающую (дневной) лимит, заданный банком-эмитентом',
        'ISSUER_LIMIT_COUNT_FAIL' => 'Превышен лимит на число транзакций: клиент выполнил максимально разрешенное число транзакций в течение лимитного цикла и пытается провести еще одну',
        'ISSUER_TIMEOUT' => 'Эмитент не ответил в установленное время',
        'MERCHANT_FORWARD' => 'Перенаправление на другой терминал',
        'MERCHANT_RESTRICTION' => 'Запрет МПС или экваера на проведение операции мерчанту',
        'MPI_CERTIFICATE_ERROR' => 'Ошибка сервиса MPI(шлюз)',
        'MPI_RESPONSE_ERROR' => 'Ошибка сервиса MPI(МПС)',
        'ORDER_NOT_FOUND' => 'Не найдена транзакция',
        'ORDER_TIME_OUT' => 'Время платежа (сессии) истекло',
        'PAYMENT_ENGINE_ERROR' => 'Ошибка взаимодействия в ядре процессинга',
        'PROCESSING_ACCESS_DENIED' => 'Доступ к процессингу запрещен',
        'PROCESSING_ERROR' => 'Ошибка функционирования системы, имеющая общий характер. Фиксируется платежной сетью или банком-эмитентом',
        'PROCESSING_FRAUD_ERROR' => 'Процессинг отклонил мошенническую транзакцию',
        'ROCESSING_TIME_OUT' => 'Не получен ответ от процессинга в установленное время',
        'REFUSAL_BY_GATE' => 'Отказ шлюза в выполнении операции',
        'THREE_DS_ATTEMPTS_FAIL' => 'Попытка 3DS авторизации неудачна',
        'THREE_DS_AUTH_ERROR' => 'Ошибка авторизации 3DS',
        'THREE_DS_ERROR' => 'Ошибка оплаты 3DS',
        'THREE_DS_FAIL' => 'Ошибка сервиса 3DS',
        'THREE_DS_NOT_ATTEMPTED' => '3DS не вводился',
        'THREE_DS_NOTENROLLED' => 'Карта не вовлечена в систему 3DS',
        'THREE_DS_TIME_OUT' => 'Превышено время ожидания 3DS',
        'THREE_DS_USER_AUTH_FAIL' => 'Пользователь ввел неверный код 3DS',
        'UNKNOWN_STATE' => 'Неизвестный статус транзакции',
        'USER_NOT_FOUND' => 'Пользователь не найден',
        'WRONG_AUTHORIZATION_CODE' => 'Неверный код авторизации',
        'WRONG_CARD_INFO' => 'Введены неверные параметры карты',
        'WRONG_CONFIRM_CODE' => 'Недопустимый код подтверждения',
        'WRONG_CVV' => 'Недопустимый CVV',
        'WRONG_EXPIRE_DATE' => 'Неправильная дата окончания срока действия',
        'WRONG_PAN' => 'Неверный номер карты',
        'WRONG_CARDHOLDER' => 'Недопустимое имя держателя карты',
        'WRONG_PARAMS' => 'Неверный набор или формат параметров',
        'WRONG_PAY_INFO' => 'Некорректный параметр PayInfo (неправильно сформирован или нарушена криптограмма)',
        'WRONG_PHONE' => 'Неверный телефон',
        'WRONG_USER_PARAMS' => 'Пользователь с такими параметрами не найден',
        'OTHER_ERROR' => 'Ошибка, которая произошла при невозможном стечении обстоятельств',
        'OFD_ERROR' => 'Ошибка в ответе сервиса взаимодействующим с кассами',
        'CHEQUE_DATA_EMPTY' => 'Для всех оплат через терминал должны отправляться чека, но данных для чека нет в запросе',
        'CHEQUE_DATA_INVALID' => 'Для всех оплат через терминал должны отправляться чека, но данных для чека не корректны',
        'WRONG_CHEQUE_AMOUNT' => 'Если сумма позиций чека не совпадает с суммой переданной для списания',
        'CHEQUE_RESENDING' => 'Чек уже был отправлен',
        'CHEQUE_WRONG_RECIPIENT' => 'Формат адреса/телефона получателя чека на валидный',
        'CHEQUE_NOT_CREATED' => 'Чек не создан по каким то причинам',
        'CHEQUE_TIMEOUT' => 'Не был получен статус чека от сервиса взаимодействующего с кассами за допустимое время',
        'CHEQUE_PARSE_ERROR' => 'Ошибка при парсинге строки чека в json',
        'CHEQUE_NOT_FOUND' => 'Чек не найден',
        'CHEQUE_WRONG_STATUS' => 'Чек имеет статус отличный от ожидаемого, подробности зависят от контекста',
    ],
];