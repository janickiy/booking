<h3>История платежей</h3>
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover tablesaw-swipe"
           data-tablesaw-mode="swipe" width="100%">
        <thead>
        <tr>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">#</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Поставщик</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Тип</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">ID транзакции</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Сумма</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Статус</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Пользователь</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Клиент</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Холдинг</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Дата и время</th>
        </tr>
        </thead>
        <tbody>

        @if(isset($payments))

            @foreach($payments as $payment)

                <tr>
                    <td><a href="{!! URL::route('admin.orders_payment.info',['id' => $payment->id]) !!}">{!! $payment->id !!}</a></td>
                    <td>{!! $payment->provider !!}</td>
                    <td>{!! $payment->type !!}</td>
                    <td>{!! $payment->transactionId !!}</td>
                    <td>{!! $payment->amount !!}</td>
                    <td>{!! $payment->TypeName !!}</td>
                    <td>{!! $payment->user->email ?? isset($payment->user->email) !!}</td>
                    <td>{!! $payment->client->email ?? isset($payment->client->email) !!}</td>
                    <td>{!! $payment->holding->email ?? isset( $payment->holding->email)!!}</td>
                    <td>{!! $payment->created_at !!}</td>
                </tr>

            @endforeach

        @endif

        </tbody>
    </table>

</div>