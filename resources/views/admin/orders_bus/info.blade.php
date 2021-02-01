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
                                <th >Дата рождения</th>
                                <th >Место рождения</th>
                                <th >Граждансво</th>
                                <th >Тип документа</th>
                                <th >№ документа</th>
                                <th >Срок действия документа</th>
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
                                        <td >{{ isset($customer['BirthDate']) ? App\Helpers\DateTimeHelpers::convertDate($customer['BirthDate']) :'' }}</td>
                                        <td >{{ isset($customer['BirthPlace']) ? $customer['BirthPlace']:'' }}</td>
                                        <td >{{ isset($customer['CitizenshipCode']) ? $customer['CitizenshipCode']:'' }}</td>
                                        <td >{{ isset($customer['DocumentType']) ?  trans('references/im.documentType.' . $customer['DocumentType']):'' }}</td>
                                        <td >{{ isset($customer['DocumentNumber']) ? $customer['DocumentNumber']:''}}</td>
                                        <td >{{ isset($customer['DocumentValidTill']) ? App\Helpers\DateTimeHelpers::convertDate($customer['DocumentValidTill']):'' }}</td>
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

                            @if(isset($passengers))

                            @foreach($passengers as $passenger)

                                <tr>
                                    <td>{{ isset($passenger['OrderCustomerReferenceIndex']) ? $passenger['OrderCustomerReferenceIndex'] : '' }}</td>
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

                            @endif

                            </tbody>

                        </table>
                    </div>

                    <br><br>

                    <h3>Список документов</h3>

                    <table class="table table-striped table-bordered table-hover" width="100%">
                        <thead>
                        <tr>
                            <th>Идентификатор бланка</th>
                            <th>Тип тарифа</th>
                            <th>Стоимость билета</th>
                        </tr>
                        </thead>
                        <tbody>

                        @if(isset($documents))
                        @foreach($documents as $document)
                            <tr>
                                <td>{{ isset($document['OrderItemBlankId']) ? $document['OrderItemBlankId']:'' }}</td>
                                <td>{{ isset($document['TariffType']) ? trans('references/im.busPassengerResponseTariffType.' . $document['TariffType']):'' }}</td>
                                <td>{{ isset($document['Amount']) ? $document['Amount']:'' }}</td>
                            </tr>
                        @endforeach
                        @endif

                        </tbody>
                    </table>

                    <br><br>

                    <h3>Ответ на запрос покупки электронных билетов на аэроэкспресс</h3>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover tablesaw-swipe"
                               data-tablesaw-mode="swipe" width="100%">
                            <thead>
                            <tr>
                                <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">#</th>
                                <th scope="col" data-tablesaw-sortable-col  data-tablesaw-priority="persist">{!! trans('references/im.busReservationResponse.OrderItemId') !!}</th>
                                <th>{!! trans('references/im.busReservationResponse.Amount') !!}</th>
                                <th>{!! trans('references/im.busReservationResponse.Fare') !!}</th>
                                <th>{!! trans('references/im.busReservationResponsee.Tax') !!}</th>
                                <th>{!! trans('references/im.busReservationResponse.ReservationNumber') !!}</th>
                                <th>{!! trans('references/im.busReservationResponse.ConfirmTill') !!}</th>
                                <th width="180px">{!! trans('references/im.busReservationResponse.ClientFeeCalculation') !!}</th>
                                <th>{!! trans('references/im.busReservationResponse.AgentFeeCalculation') !!}</th>
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
                                        <td>
                                            {!! isset($orderDetail['ClientFeeCalculation']['Charge']) ?  $orderDetail['ClientFeeCalculation']['Charge']:0!!} /
                                            {!! isset($orderDetail['ClientFeeCalculation']['Profit']) ?  $orderDetail['ClientFeeCalculation']['Profit']:0!!}
                                        </td>
                                        <td>
                                            {!! isset($orderDetail['AgentFeeCalculation']['Charge']) ?  $orderDetail['AgentFeeCalculation']['Charge']: 0!!} /
                                            {!! isset($orderDetail['AgentFeeCalculation']['Profit']) ?  $orderDetail['AgentFeeCalculation']['Profit']:0!!}

                                        </td>
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
                                <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">{!! trans('references/im.busReservationResponse.OrderItemId') !!}</th>
                                <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">{!! trans('references/im.busReservationResponse.Amount') !!}</th>
                                <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">{!! trans('references/im.busReservationResponse.Fare') !!}</th>
                                <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">{!! trans('references/im.busReservationResponse.Tax') !!}</th>
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
                                    <td>{!! isset($payInfo['Tax']) ?  $payInfo['Tax']:''!!}</td>
                                </tr>

                            @endforeach
                            @endif

                            </tbody>
                        </table>

                    </div>
                    <!-- end widget content -->
                    @include('admin.orders_payment')

                    @include('admin.order_logs')

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