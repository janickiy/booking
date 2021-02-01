@extends('admin.layout')

@section('title', $title)

@section('css')

@endsection

@section('content')

    <h2>{!! $title !!}</h2>

    @include('admin.notifications')

    <div class="row">
        <div class="col-lg-12"><p class="text-center">
                <button class="btn btn-outline btn-default btn-lg" title="Импорт" id="import_tmanager">
                    <span class="fa fa-download fa-2x"></span> Импорт
                </button>

                <button class="btn btn-outline btn-default btn-lg" title="Очистить" id="cache_clean">
                    <span class="fa fa-eraser fa-2x"></span> Очистить кэш
                </button>
            </p>
        </div>
    </div>

    <div class="row-fluid">

        <div class="col">

            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget jarviswidget-color-blueDark" data-widget-editbutton="false">

                <!-- widget div-->
                <div>

                    @if($user->hasAccess('admin.tmanager.create'))
                        <div class="box-header">
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="{{ URL::route('admin.tmanager.create') }}"
                                       class="btn btn-info btn-sm pull-left"><span class="fa fa-plus"> &nbsp;</span>Добавить перевод</a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="itemList" class="table table-striped table-bordered table-hover" width="100%">
                            <thead>
                            <tr>
                                <th > Локализация</th>
                                <th > Группа</th>
                                <th > Ключ</th>
                                <th > Значение</th>
                                <th > Добавлено</th>
                                <th > Изменено</th>
                                <th > Действия</th>
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

        $(document).ready(function () {

            $('#import_tmanager').on('click', function() {
                 $.ajax({
                    type: "POST",
                    url: '{{ URL::route('admin.datatable.ajax')}}',
                    data: { action: 'import_tmanager'},
                    dataType: "json",
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    success: function(data){
                        location.reload();
                    }
                });
            });

            $('#export_tmanager').on('click', function() {
                $.ajax({
                    type: "POST",
                    url: '{{ URL::route('admin.datatable.ajax')}}',
                    data: { action: 'export_tmanager'},
                    dataType: "json",
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    success: function(data){
                        location.reload();
                    }
                });
            });

            $('#cache_clean').on('click', function() {
                $.ajax({
                    type: "POST",
                    url: '{{ URL::route('admin.datatable.ajax')}}',
                    data: { action: 'cache_clean'},
                    dataType: "json",
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    success: function(data){
                        location.reload();
                    }
                });
            });

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
                phone: 480
            };

            $('#itemList').dataTable({
                "sDom": "flrtip",
                "autoWidth": true,
                "oLanguage": {
                    "sSearch": '<span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span>'
                },
                "preDrawCallback": function () {
                    // Initialize the responsive datatables helper once.
                    if (!responsiveHelper_dt_basic) {
                        responsiveHelper_dt_basic = new ResponsiveDatatablesHelper($('#itemList'), breakpointDefinition);
                    }
                },
                "rowCallback": function (nRow) {
                    responsiveHelper_dt_basic.createExpandIcon(nRow);
                },
                "drawCallback": function (oSettings) {
                    responsiveHelper_dt_basic.respond();
                },
                'createdRow': function (row, data, dataIndex) {
                    $(row).attr('id', 'rowid_' + data['id']);
                },
                processing: true,
                serverSide: true,
                "order": [[ 5, "desc" ]],
                ajax: {
                    url: '{!! URL::route('admin.datatable.tmanager') !!}'
                },
                columns: [
                    {data: 'locale', name: 'locale'},
                    {data: 'group', name: 'group'},
                    {data: 'key', name: 'key'},
                    {data: 'value', name: 'value'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'updated_at', name: 'updated_at'},
                    {data: "actions", name: 'actions', orderable: false, searchable: false}
                ],
            });

            /* END BASIC */

            // Apply the filter
            $("#itemList thead th input[type=text]").on('keyup change', function () {

                otable
                    .column($(this).parent().index() + ':visible')
                    .search(this.value)
                    .draw();

            });

            /* END COLUMN FILTER */

            $('#itemList').on('click', 'a.deleteRow', function () {
                var btn = this;
                var rowid = $(this).attr('id');
                swal({
                        title: "Вы уверены?",
                        text: "Вы не сможете восстановить эту информацию!",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Да, удалить!",
                        closeOnConfirm: false
                    },
                    function (isConfirm) {
                        if (!isConfirm) return;
                        $.ajax({
                            url: SITE_URL + "/cp/tmanager/destroy/" + rowid,
                            type: "DELETE",
                            dataType: "html",
                            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                            success: function () {
                                $("#rowid_" + rowid).remove();
                                swal("Сделано!", "Данные успешно удалены!", "success");
                            },
                            error: function (xhr, ajaxOptions, thrownError) {
                                swal("Ошибка при удалении!", "Попробуйте еще раз", "error");
                            }
                        });
                    });
            });
        })

    </script>


@endsection