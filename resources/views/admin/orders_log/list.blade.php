@extends('admin.layout')

@section('title', $title)

@section('css')
<style>
    .error-row{
        background: lightcoral;
    }
    .error-row.odd{
        background: lightpink!important;
    }

    .warning-row{
        background: lightgoldenrodyellow;
    }
    .warning-row.odd{
        background: lightyellow!important;
    }
</style>
@endsection

@section('content')

    <h2>{!! $title !!}</h2>

    <div class="row-fluid">

        <div class="col">

            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget jarviswidget-color-blueDark" data-widget-editbutton="false">
                <!-- widget options:
                usage: <div class="jarviswidget" id="wid-id-0" data-widget-editbutton="false">

                data-widget-colorbutton="false"
                data-widget-editbutton="false"
                data-widget-togglebutton="false"
                data-widget-deletebutton="false"
                data-widget-fullscreenbutton="false"
                data-widget-custombutton="false"
                data-widget-collapsed="true"
                data-widget-sortable="false"

                -->

                <!-- widget div-->
                <div>
                    <div class="table-responsive">
                        <table id="datatable_fixed_column" class="table table-striped table-bordered table-hover"
                               width="100%">
                            <thead>
                            <tr>
                                <th class="hasinput">
                                    <input type="text" class="form-control" placeholder="Фильтровать по ID"/>
                                </th>
                                <th class="hasinput">
                                    <input type="text" class="form-control" id="session-filter" placeholder="Фильтровать по ID сессии"/>
                                </th>
                                <th class="hasinput">
                                    <input type="text" class="form-control" id="session-filter" placeholder="Фильтровать по ID заказа"/>
                                </th>
                                <th class="hasinput">
                                    <input type="text" id="user" class="form-control" placeholder="Фильтровать по действию"/>
                                </th>
                                <th class="hasinput">
                                </th>
                                <th class="hasinput">
                                </th>
                                <th class="hasinput icon-addon FilterinputSearc" style="width:150px">
                                    <input id="reportrange" type="text" placeholder="Фильтр Date" class="form-control"
                                           data-dateformat="YYYY-MM-DD hh:mm:ss - DD/MM/YYYYYYYY-MM-DD hh:mm:ss">
                                    <label for="dateselect_filter" class="glyphicon glyphicon-calendar no-margin padding-top-15" rel="tooltip" title="" data-original-title="Фильтровать по дате"></label>
                                </th>
                            </tr>
                            <tr>
                                <th data-class="expand">ID</th>
                                <th data-class="expand">ID сессии</th>
                                <th data-class="expand">ID заказ</th>
                                <th data-class="expand">Действие</th>
                                <th data-class="expand">Сообщение</th>
                                <th data-class="expand">Ошибка</th>
                                <th>Дата</th>
                            </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- end widget content -->

            </div>
            <!-- end widget div -->


        </div>
        <!-- end widget -->
    </div>

@endsection

@section('js')
    <script>

        // DO NOT REMOVE : GLOBAL FUNCTIONS!

        $(document).ready(function() {

            pageSetUp();

            /* // DOM Position key index //

            l - Length changing (dropdown)
            f - Filtering input (search)
            t - The Table! (datatable)
            i - Information (records)
            p - Pagination (paging)
            r - pRocessing
            < and > - div elements
            <"#id" and > - div with an id
            <"class" and > - div with a class
            <"#id.class" and > - div with an id and class

            Also see: http://legacy.datatables.net/usage/features
            */

            /* BASIC ;*/
            var responsiveHelper_dt_basic = undefined;

            var breakpointDefinition = {
                tablet: 1024,
                phone: 480,
            };

            var startdate;
            var enddate;
            //instantiate datepicker and choose your format of the dates
            $('#reportrange').daterangepicker({
                    timePicker: true,
                    timePicker24Hour: true,
                    autoUpdateInput: false,

                    locale: {
                        format: 'YYYY-MM-DD hh:mm:ss',
                        applyLabel: 'Принять',
                        cancelLabel: 'Отмена',
                        invalidDateLabel: 'Выберите дату',
                        daysOfWeek: ['Сб', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Вс'],
                        monthNames: [
                            'Январь',
                            'Февраль',
                            'Март',
                            'Апрель',
                            'Май',
                            'Июнь',
                            'Июль',
                            'Август',
                            'Сентябрь',
                            'Октябрь',
                            'Ноябрь',
                            'Декабрь'],
                        firstDay: 1,
                    },
                    ranges: {
                        'Сегодня': [moment(), moment()],
                        'Вчера': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'За последние 7 дней': [moment().subtract(6, 'days'), moment()],
                        'За последние 30 дней': [moment().subtract(29, 'days'), moment()],
                        'За этот месяц': [moment().startOf('month'), moment().endOf('month')],
                        'За прошлый месяц': [
                            moment().subtract(1, 'month').startOf('month'),
                            moment().subtract(1, 'month').endOf('month')],
                    }
                    ,
                    'opens': 'right',
                },

                function(start_date, end_date) {
                    if (start_date && end_date) this.element.val(start_date.format('YYYY-MM-DD hh:mm:ss') + ' - ' +
                        end_date.format('YYYY-MM-DD hh:mm:ss'));
                });

            //Filter the datatable on the datepicker apply event
            $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
                startdate = picker.startDate.format('YYYY-MM-DD hh:mm:ss');
                enddate = picker.endDate.format('YYYY-MM-DD hh:mm:ss');
                otable.draw();
            });

            var otable = $('#datatable_fixed_column').DataTable({
                'sDom': 'flrtip',
                'autoWidth': true,
                'oLanguage': {
                    'sSearch': '<span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span>',
                },
                'preDrawCallback': function() {
                    // Initialize the responsive datatables helper once.
                    if (!responsiveHelper_dt_basic) {
                        responsiveHelper_dt_basic = new ResponsiveDatatablesHelper($('#datatable_fixed_column'),
                            breakpointDefinition);
                    }
                },
                'rowCallback': function(nRow) {
                    responsiveHelper_dt_basic.createExpandIcon(nRow);
                },
                'drawCallback': function(oSettings) {
                    responsiveHelper_dt_basic.respond();
                },
                'order': [[0, 'desc']],
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! URL::route('admin.datatable.orders_log',['orderId' => $orderId]) !!}',
                    data: function(d) {
                        d.date = $('#reportrange').val();
                    },
                },
                columns: [
                    {data: 'orders_log_id', name: 'orders_log_id'},
                    {data: 'session_log_id', name: 'session_log_id'},
                    {data: 'order_id', name: 'order_id'},
                    {data: 'action', name: 'action'},
                    {data: 'message', name: 'message'},
                    {data: 'error', name: 'error', searchable: false},
                    {data: 'created_at', name: 'created_at'},
                ],
            });

            /* END BASIC */

            // Apply the filter
            $('#datatable_fixed_column thead th input[type=text]').on('keyup change', function() {

                otable.column($(this).parent().index() + ':visible').search(this.value).draw();
            });

            /* END COLUMN FILTER */

        });

    </script>
@endsection