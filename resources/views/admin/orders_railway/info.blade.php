@extends('admin.layout')

@section('title', 'Подробности')

@section('css')

@endsection

@section('content')

    <h2>{{ $title }}</h2>
    <div class="row-fluid">

        <div class="col">

            <p>
                <a href="{{ URL::route('admin.orders.list') . ($order->complexOrderId ? '#' . $order->complexOrderId : '') }}">назад</a>
            </p>
            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget jarviswidget-color-blueDark" id="wid-id-1" data-widget-editbutton="false">

                <div>
                    <h3>
                        Пользователь: {{ isset($order->user->login) && $order->user->login ?  $order->user->login : 'незарегистрированный' }}</h3>
                    <h3>Идентификатор заказа: {{ isset($orderData['orderId']) ? $orderData['orderId'] : '-' }}</h3>
                    <h3>Сумма за бронирование: {{ isset($order->Amount) ? $order->Amount : '0.00' }}</h3>

                    <h3>Пользователи услуг заказа</h3>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover tablesaw-swipe"
                               data-tablesaw-mode="swipe" width="100%">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th >Идентификатор пользователя</th>
                                <th data-class="expand">ФИО</th>
                                <th >Пол</th>
                                <th data-hide="phone,tablet">Дата рождения</th>
                                <th data-hide="phone,tablet">Место рождения</th>
                                <th data-hide="phone,tablet">Граждансво</th>
                                <th data-hide="phone,tablet">Тип документа</th>
                                <th data-hide="phone,tablet">№ документа</th>
                                <th data-hide="phone,tablet">Срок действия документа</th>
                            </tr>
                            </thead>
                            <tbody>

                            @if(isset($orderData['Customers']))

                                @foreach($orderData['Customers'] as $customer)
                                    <tr>
                                        <td>{{ isset($customer['Index']) ? $customer['Index']:'' }}</td>
                                        <td >{{ isset($customer['OrderCustomerId']) ? $customer['OrderCustomerId'] : '' }}</td>
                                        <td data-class="expand">{{ isset($customer['LastName']) ? $customer['LastName'] : '' }}  {{ isset($customer['FirstName']) ? $customer['FirstName'] : '' }} {{ isset($customer['MiddleName']) ? $customer['MiddleName'] : '' }}</td>
                                        <td >{{ isset($customer['Sex']) ? trans('references/im.sex.' . $customer['Sex'])  :'-' }}</td>
                                        <td data-hide="phone,tablet">{{ isset($customer['BirthDate']) ? App\Helpers\DateTimeHelpers::convertDate($customer['BirthDate']) :'' }}</td>
                                        <td data-hide="phone,tablet">{{ isset($customer['BirthPlace']) ? $customer['BirthPlace']:'' }}</td>
                                        <td data-hide="phone,tablet">{{ isset($customer['CitizenshipCode']) ? $customer['CitizenshipCode']:'' }}</td>
                                        <td data-hide="phone,tablet">{{ isset($customer['DocumentType']) ?  trans('references/im.documentType.' . $customer['DocumentType']):'' }}</td>
                                        <td data-hide="phone,tablet">{{ isset($customer['DocumentNumber']) ? $customer['DocumentNumber']:''}}</td>
                                        <td data-hide="phone,tablet">{{ isset($customer['DocumentValidTill']) ? App\Helpers\DateTimeHelpers::convertDate($customer['DocumentValidTill']):'' }}</td>
                                    </tr>
                                @endforeach

                            @endif

                            </tbody>
                        </table>
                    </div>

                    @if($order['orderStatus'] == 1) <a href="{{ URL::route('admin.ordersrailway.editpassenger',['id'=> $id]) }}" class="btn btn-success btn-flat pull-left">Редактировать</a> @endif

                    <h3>Пассажиры</h3>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover tablesaw-swipe"
                               data-tablesaw-mode="swipe" width="100%">
                            <thead>
                            <tr>
                                <th >Категория пассажира</th>
                                <th data-class="expand">Места пассажира</th>
                                <th >Ярусы мест пассажира</th>
                                <th >Места пассажира в виде списка</th>
                                <th >Место с указанием типа</th>
                                <th >Имя</th>
                                <th >Отчество</th>
                                <th >Фамилия</th>
                                <th >Дата рождения</th>
                                <th >Пол</th>
                                <th >Гражданство</th>
                                <th >Идентификатор бланка</th>
                                <th >Идентификатор пользователя</th>
                                <th >Сумма</th>
                                <th >Тариф</th>
                                <th >Сборы</th>
                            </tr>
                            </thead>

                            <tbody>

                            @foreach($passengers as $passenger)

                                <tr>
                                    <td>{{ isset($passenger['OrderCustomerReferenceIndex']) ? $passenger['OrderCustomerReferenceIndex'] : '' }}</td>
                                    <td>{{ trans('references/im.railwayPassengerCategory.' . $passenger['Category']) }}</td>
                                    <td>{{ isset($passenger['Places']) ? $passenger['Places']:'' }}</td>
                                    <td>{{ isset($passenger['PlaceTiers']) ? $passenger['PlaceTiers']:'' }}</td>
                                    <td>
                                        {{ $passenger['PlacesWithType'][0]["Type"] && trans('references/im.reservationPlaceType.' . $passenger['PlacesWithType'][0]["Type"]) ? trans('references/im.reservationPlaceType.' . $passenger['PlacesWithType'][0]["Type"]) : '' }}
                                        {{ isset($passenger['PlacesWithType'][0]["Number"]) ? $passenger['PlacesWithType'][0]["Number"]:'' }}
                                    </td>
                                    <td>{{ isset($passenger['FirstName']) ? $passenger['FirstName']:'' }}</td>
                                    <td>{{ isset($passenger['MiddleName']) ? $passenger['MiddleName']:''}}</td>
                                    <td>{{ isset($passenger['LastName']) ? $passenger['LastName']:'' }}</td>
                                    <td>{{ isset($passenger['Birthday']) ? $passenger['Birthday']:'' }}</td>
                                    <td>{{ isset($passenger['Sex']) ? $passenger['Sex']:'' }}</td>
                                    <td>{{ isset($passenger['Citizenship']) ? $passenger['Citizenship']:'' }}</td>
                                    <td>{{ isset($passenger['OrderItemBlankId']) ? $passenger['OrderItemBlankId'] :'' }}</td>
                                    <td>{{ isset($passenger['OrderCustomerId']) ? $passenger['OrderCustomerId']:'' }}</td>
                                    <td>{{ isset($passenger['Amount']) ? $passenger['Amount']:'' }}</td>
                                    <td>{{ isset($passenger['Fare']) ? $passenger['Fare']:''}}</td>
                                    <td>{{ isset($passenger['Tax']) ? $passenger['Tax']:'' }}</td>
                                </tr>

                            @endforeach

                            </tbody>

                        </table>
                    </div>

                    <br><br>

                    <h3>Список документов</h3>

                    @if($order['orderStatus'] == 2)

                        <table class="table table-striped table-bordered table-hover" width="100%">
                            <thead>
                            <tr>
                                <th>Идентификатор бланка</th>
                                <th>Номер бланка (билета)</th>
                                <th>Статус бланка</th>
                                <th>Ожидание действий по электронной регистрации</th>
                            </tr>
                            </thead>
                            <tbody>

                            @if(isset($documents))
                            @foreach($documents as $document)
                                <tr>
                                    <td>{{ isset($document['OrderItemBlankId']) ? $document['OrderItemBlankId']:'' }}</td>
                                    <td>{{ isset($document['Number']) ? $document['Number']:'' }}</td>
                                    <td>{{ isset($document['BlankStatus']) ? trans('references/im.blankStatus.' . $document['BlankStatus']):'' }}</td>
                                    <td>{{ isset($document['PendingElectronicRegistration']) ? trans('references/im.pendingElectronicRegistration.' . $document['PendingElectronicRegistration']):'' }}</td>
                                </tr>
                            @endforeach
                            @endif

                            </tbody>
                        </table>

                    @else

                    <table class="table table-striped table-bordered table-hover" width="100%">
                        <thead>
                        <tr>
                            <th>Идентификатор бланка</th>
                            <th>Сумма за бронирование</th>
                            <th>Номер билета</th>
                            <th>Тариф</th>
                            <th>Возможность выбора<br>предоплаченного питания</th>
                            <th>Суммы и ставки НДС по электронному билету</th>
                            <th>Базовый тариф</th>
                            <th>Стоимость билетной части<br>по электронному билету</th>
                            <th>Информация о тарифе</th>
                            <th>Информация о предоплаченном питании</th>
                            <th>Стоимость за предоставляемый сервис</th>
                        </tr>
                        </thead>
                        <tbody>

                        @if(isset($documents))

                        @foreach($documents as $document)
                            <tr>
                                <td>{{ isset($document['OrderItemBlankId']) ? $document['OrderItemBlankId']:'' }}</td>
                                <td>{{ isset($document['Amount']) ? $document['Amount']:'' }}</td>
                                <td>{{ isset($document['Number']) ? $document['Number']:'' }}</td>
                                <td>{{ isset($document['TariffType']) ? trans('references/im.tariffType.' . $document['TariffType']) : '' }}</td>
                                <td>{{ isset($document['IsMealOptionPossible']) && $document['IsMealOptionPossible'] ? 'да':'нет' }}</td>
                                <td>
                                    {!! isset($document['VatRateValues'][0]['Rate']) && isset($document['VatRateValues'][0]['Value']) ? 'Ставка и сумма НДС со стоимости перевозки: ' .$document['VatRateValues'][0]['Rate']. ', ' .$document['VatRateValues'][0]['Value']. ' <br>':'' !!}
                                    {!! isset($document['VatRateValues'][1]['Rate']) && isset($document['VatRateValues'][1]['Value']) ? 'Ставка и сумма НДС со стоимости сервиса: ' .$document['VatRateValues'][1]['Rate']. ', ' .$document['VatRateValues'][1]['Value']. '<br>':'' !!}
                                    {!! isset($document['VatRateValues'][2]['Rate']) && isset($document['VatRateValues'][2]['Value']) ? 'Ставка и сумма со стоимости комиссионного сбора: ' .$document['VatRateValues'][2]['Rate']. ', ' .$document['VatRateValues'][2]['Value']. '':'' !!}
                                </td>
                                <td>{{ isset($document['BaseFare']) ? $document['BaseFare']:'' }}</td>
                                <td>{{ isset($document['AdditionalPrice']) ? $document['AdditionalPrice']:'' }}</td>
                                <td>
                                    {!! isset($document['TariffInfo']['TariffType']) ? trans('references/im.tariffType.' . $document['TariffInfo']['TariffType']):''!!}
                                </td>
                                <td>
                                    код: {!! isset($document['PrepaidMealInfo']['MealOptionCode']) ? $document['PrepaidMealInfo']['MealOptionCode']:'' !!}
                                    <br>
                                    {{ isset($document['PrepaidMealInfo']['MealName']) ? $document['PrepaidMealInfo']['MealName']:'' }}
                                    <br>
                                    {{ isset($document['PrepaidMealInfo']['Description']) ? $document['PrepaidMealInfo']['Description']:'' }}
                                    <br>
                                </td>
                                <td>{{ isset($document['ServicePrice']) ? $document['ServicePrice']:'' }}</td>
                            </tr>

                        @endforeach

                        @endif

                        </tbody>
                    </table>

                    @endif

                    <br><br>

                    <h3>Ответ на запрос на бронирование ЖД-билетов</h3>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover tablesaw-swipe"
                               data-tablesaw-mode="swipe" width="100%">
                            <thead>
                            <tr>
                                <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">#</th>
                                <th scope="col" data-tablesaw-sortable-col
                                    data-tablesaw-priority="persist">{!! trans('references/im.railwayReservationResponse.OrderItemId') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.Amount') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.Fare') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.Tax') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.ReservationNumber') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.ConfirmTill') !!}</th>
                                <th width="180px">{!! trans('references/im.railwayReservationResponse.ClientFeeCalculation') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.OriginStation') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.DestinationStation') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.OriginStationCode') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.DestinationStationCode') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.OriginTimeZoneDifference') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.DestinationTimeZoneDifference') !!}</th>
                                <th width="180px">{!! trans('references/im.railwayReservationResponse.DepartureDateTime') !!}</th>
                                <th width="180px">{!! trans('references/im.railwayReservationResponse.ArrivalDateTime') !!}</th>
                                <th width="180px">{!! trans('references/im.railwayReservationResponse.LocalDepartureDateTime') !!}</th>
                                <th width="180px">{!! trans('references/im.railwayReservationResponse.LocalArrivalDateTime') !!}</th>
                                <th width="180px">{!! trans('references/im.railwayReservationResponse.TrainNumber') !!}</th>
                                <th width="180px">{!! trans('references/im.railwayReservationResponse.BookingTrainNumber') !!}</th>
                                <th width="180px">{!! trans('references/im.railwayReservationResponse.TrainNumberToGetRoute') !!}</th>
                                <th width="180px">{!! trans('references/im.railwayReservationResponse.CarType') !!}</th>
                                <th width="180px">{!! trans('references/im.railwayReservationResponse.CarNumber') !!}</th>
                                <th width="180px">{!! trans('references/im.railwayReservationResponse.ServiceClass') !!}</th>
                                <th width="180px">{!! trans('references/im.railwayReservationResponse.InternationalServiceClass') !!}</th>
                                <th width="180px">{!! trans('references/im.railwayReservationResponse.TimeDescription') !!}</th>
                                <th width="180px">{!! trans('references/im.railwayReservationResponse.Carrier') !!}</th>
                                <th width="180px">{!! trans('references/im.railwayReservationResponse.CarrierCode') !!}</th>
                                <th width="180px">{!! trans('references/im.railwayReservationResponse.CarrierTin') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.CountryCode') !!}</th>
                                <th width="180px">{!! trans('references/im.railwayReservationResponse.IsMealOptionPossible') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.IsAdditionalMealOptionPossible') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.MealGroup') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.BookingSystem') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.IsThreeHoursReservationAvailable') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.TripDuration') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.TrainDescription') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.CarDescription') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.IsSuburban') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.CabinGenderDescription') !!}</th>
                                <th>{!! trans('references/im.railwayReservationResponse.IsExchange') !!}</th>
                                <th width="180px">{!! trans('references/im.railwayReservationResponse.DepartureDateFromFormingStation')!!}</th>
                            </tr>
                            </thead>
                            <tbody>

                            @if(isset($orderData['result']['ReservationResults']))

                                @foreach($orderData['result']['ReservationResults'] as $orderDetail)

                                    <tr>
                                        <td>{!! isset($orderDetail['Index']) ?  $orderDetail['Index']:''!!}</td>
                                        <td>{!! isset($orderDetail['OrderItemId']) ?  $orderDetail['OrderItemId']:''!!}</td>
                                        <td>{!! isset($orderDetail['Amount']) ?  $orderDetail['Amount']:''!!}</td>
                                        <td>{!! isset($orderDetail['Fare']) ?  $orderDetail['Fare']:''!!} </td>
                                        <td>{!! isset($orderDetail['Tax']) ?  $orderDetail['Tax']:''!!} </td>
                                        <td>{!! isset($orderDetail['ReservationNumber']) ?  $orderDetail['ReservationNumber']:''!!}</td>
                                        <td>{!! isset($orderDetail['ConfirmTill']) ?  App\Helpers\DateTimeHelpers::convertDate($orderDetail['ConfirmTill']):''!!}</td>
                                        <td>{!! isset($orderDetail['ClientFeeCalculation']) ?  $orderDetail['ClientFeeCalculation']:''!!}</td>
                                        <td>{!! isset($orderDetail['OriginStation']) ?  $orderDetail['OriginStation']:''!!}</td>
                                        <td>{!! isset($orderDetail['DestinationStation']) ?  $orderDetail['DestinationStation']:''!!}</td>
                                        <td>{!! isset($orderDetail['OriginStationCode']) ?  $orderDetail['OriginStationCode']:''!!}</td>
                                        <td>{!! isset($orderDetail['DestinationStationCode']) ?  $orderDetail['DestinationStationCode']:''!!}</td>
                                        <td>{!! isset($orderDetail['OriginTimeZoneDifference']) ?  $orderDetail['OriginTimeZoneDifference']:''!!}</td>
                                        <td>{!! isset($orderDetail['DestinationTimeZoneDifference']) ?  $orderDetail['DestinationTimeZoneDifference']:''!!}</td>
                                        <td>{!! isset($orderDetail['DepartureDateTime']) ?  App\Helpers\DateTimeHelpers::convertDate($orderDetail['DepartureDateTime']):''!!}</td>
                                        <td>{!! isset($orderDetail['ArrivalDateTime']) ?  App\Helpers\DateTimeHelpers::convertDate($orderDetail['ArrivalDateTime']):''!!}</td>
                                        <td>{!! isset($orderDetail['LocalDepartureDateTime']) ?  App\Helpers\DateTimeHelpers::convertDate($orderDetail['LocalDepartureDateTime']) :''!!}</td>
                                        <td>{!! isset($orderDetail['LocalArrivalDateTime']) ?  App\Helpers\DateTimeHelpers::convertDate($orderDetail['LocalArrivalDateTime']) :''!!}</td>
                                        <td>{!! isset($orderDetail['TrainNumber']) ?  $orderDetail['TrainNumber']:''!!}</td>
                                        <td>{!! isset($orderDetail['BookingTrainNumber']) ?  $orderDetail['BookingTrainNumber']:''!!}</td>
                                        <td>{!! isset($orderDetail['TrainNumberToGetRoute']) ?  $orderDetail['TrainNumberToGetRoute']:''!!}</td>
                                        <td>{!! isset($orderDetail['CarType']) ?  $orderDetail['CarType']:''!!}</td>
                                        <td>{!! isset($orderDetail['CarNumber']) ?  $orderDetail['CarNumber']:''!!}</td>
                                        <td>{!! isset($orderDetail['ServiceClass']) ? $orderDetail['ServiceClass']:''!!}</td>
                                        <td>{!! isset($orderDetail['InternationalServiceClass']) ?  $orderDetail['InternationalServiceClass']:''!!}</td>
                                        <td>{!! isset($orderDetail['TimeDescription']) ?  $orderDetail['TimeDescription']:''!!}</td>
                                        <td>{!! isset($orderDetail['Carrier']) ?  $orderDetail['Carrier']:''!!}</td>
                                        <td>{!! isset($orderDetail['CarrierCode']) ?  $orderDetail['CarrierCode']:''!!}</td>
                                        <td>{!! isset($orderDetail['CarrierTin']) ?  $orderDetail['CarrierTin']:''!!}</td>
                                        <td>{!! isset($orderDetail['CountryCode']) ?  $orderDetail['CountryCode']:''!!}</td>
                                        <td>{!! isset($orderDetail['IsMealOptionPossible']) ?  $orderDetail['IsMealOptionPossible']:''!!}</td>
                                        <td>{!! isset($orderDetail['IsAdditionalMealOptionPossible']) ?  $orderDetail['IsAdditionalMealOptionPossible']:''!!}</td>
                                        <td>{!! isset($orderDetail['MealGroup']) ?  $orderDetail['MealGroup']:''!!}</td>
                                        <td>{!! isset($orderDetail['BookingSystem']) ?  $orderDetail['BookingSystem']:''!!}</td>
                                        <td>{!! isset($orderDetail['IsThreeHoursReservationAvailable']) ?  $orderDetail['IsThreeHoursReservationAvailable']:''!!}</td>
                                        <td>{!! isset($orderDetail['TripDuration']) ?  $orderDetail['TripDuration']:''!!}</td>
                                        <td>{!! isset($orderDetail['TrainDescription']) ?  $orderDetail['TrainDescription']:''!!}</td>
                                        <td>{!! isset($orderDetail['CarDescription']) ?  $orderDetail['CarDescription']:''!!}</td>
                                        <td>{!! isset($orderDetail['IsSuburban']) ?  $orderDetail['IsSuburban']:''!!}</td>
                                        <td>{!! isset($orderDetail['CabinGenderDescription']) ?  $orderDetail['CabinGenderDescription']:''!!}</td>
                                        <td>{!! isset($orderDetail['IsExchange']) ?  $orderDetail['IsExchange']:''!!}</td>
                                        <td>{!! isset($orderDetail['DepartureDateFromFormingStation']) ?  App\Helpers\DateTimeHelpers::convertDate($orderDetail['DepartureDateFromFormingStation']):''!!}</td>
                                    </tr>

                                @endforeach

                            @endif

                            </tbody>
                        </table>
                    </div>
                    <h3>Данные платежа</h3>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover tablesaw-swipe"
                               data-tablesaw-mode="swipe" width="100%">
                            <thead>
                            <tr>
                                <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">#</th>
                                <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">{!! trans('references/im.railwayReservationResponse.OrderItemId') !!}</th>
                                <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">{!! trans('references/im.railwayReservationResponse.Amount') !!}</th>
                                <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">{!! trans('references/im.railwayReservationResponse.Fare') !!}</th>
                                <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">{!! trans('references/im.railwayReservationResponse.Tax') !!}</th>
                            </tr>
                            </thead>
                            <tbody>

                            @if(isset($orderData['result']['ReservationResults']))

                            @foreach($orderData['result']['ReservationResults'] as $payInfo)

                                <tr>
                                    <td>{!! isset($payInfo['Index']) ? $payInfo['Index']:''!!}</td>
                                    <td>{!! isset($payInfo['OrderItemId']) ? $payInfo['OrderItemId']:''!!}</td>
                                    <td>{!! isset($payInfo['Amount']) ? $payInfo['Amount']:''!!}</td>
                                    <td>{!! isset($payInfo['Fare']) ? $payInfo['Fare']:''!!}</td>
                                    <td>{!! isset($payInfo['Tax']) ? $payInfo['Tax']:''!!}</td>
                                </tr>

                            @endforeach

                            @endif

                            </tbody>
                        </table>

                    </div>
                    <!-- end widget content -->
                    @include('admin.orders_payment')

                    @include('admin.order_logs')

                    <!-- end widget content -->

                </div>
                <!-- end widget div -->

            </div>
            <!-- end widget -->

        </div>
    </div>
@endsection

@section('js')

    <script>

        $(document).ready(function () {

            $('.tree-checkbox').treeview({
                collapsed: true,
                animated: 'medium',
                unique: false
            });
        });

    </script>

@endsection