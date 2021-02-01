<h3>Лог заказа</h3>
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover tablesaw-swipe"
           data-tablesaw-mode="swipe" width="100%">
        <thead>
        <tr>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">ID</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">ID сессии</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Действие</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Сообщение</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Ошибка</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Дата</th>
        </tr>
        </thead>
        <tbody>

        @if(isset($logs))

            @foreach($logs as $log)

                <tr>
                    <td><a href="{!! URL::route('admin.orders_log.info', ['id' => $log->orders_log_id]) !!}">{!! $log->orders_log_id !!}</a></td>
                    <td>{!! $log->session_log_id !!}</td>
                    <td>{!! $log->action !!}</td>
                    <td>{!! $log->message !!}</td>
                    <td>{!! $log->error ? 'да' : 'нет' !!}</td>
                    <td>{!! $log->created_at !!}</td>
                </tr>

            @endforeach

        @endif

        </tbody>
    </table>
</div>