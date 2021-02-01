@extends('admin.layout')

@section('title', $title)

@section('css')

@endsection

@section('content')

    <h2>{!! $title !!}</h2>

    @include('admin.notifications')

    <div class="row-fluid">

        <div class="col">

            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget jarviswidget-color-blueDark" data-widget-editbutton="false">

                <!-- widget div-->
                <div>

                    @if($user->hasAccess('admin.hotel.create'))
                        <div class="box-header">
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="{{ URL::route('admin.hotels_regions.create') }}"  class="btn btn-info btn-sm pull-left"><span class="fa fa-plus"> &nbsp;</span>Добавить регион</a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="rowList" class="table table-striped table-bordered table-hover" width="100%">
                            <thead>
                            <tr>
                                <th data-hide="phone">ID</th>
                                <th data-hide="phone,tablet"> Название (Русский)</th>
                                <th data-hide="phone,tablet"> Название (English)</th>
                                <th data-hide="phone,tablet"> Тип</th>
                                <th data-hide="phone,tablet"> Слаг (Русский)</th>
                                <th data-hide="phone,tablet"> Слаг (English)</th>
                                <th data-hide="phone,tablet"> Родительски слаг</th>
                                <th data-hide="phone,tablet"> Широта</th>
                                <th data-hide="phone,tablet"> Долгота</th>
                                <th data-hide="phone,tablet"> Приоритет</th>
                                <th data-hide="phone,tablet"> СНГ</th>
                                <th data-hide="phone,tablet"> Регион</th>
                                <th data-hide="phone,tablet"> Действия</th>
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

            $('#rowList').dataTable({
                "sDom": "flrtip",
                "autoWidth": true,
                "oLanguage": {
                    "sSearch": '<span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span>'
                },
                "preDrawCallback": function () {
                    // Initialize the responsive datatables helper once.
                    if (!responsiveHelper_dt_basic) {
                        responsiveHelper_dt_basic = new ResponsiveDatatablesHelper($('#rowList'), breakpointDefinition);
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
                ajax: {
                    url: '{!! URL::route('admin.datatable.hotels_regions',['parent_id' => $parent_id]) !!}'
                },
                columns: [
                    {data: 'id', name: 'id'},
                        {data: 'name_ru', name: 'name_ru'},
                        {data: 'name_en', name: 'name_en'},
                        {data: 'type', name: 'type'},
                        {data: 'slug_ru', name: 'slug_ru'},
                        {data: 'slug_en', name: 'slug_en'},
                        {data: 'parent_slug', name: 'parent_slug'},
                        {data: 'latitude', name: 'latitude'},
                        {data: 'longitude', name: 'longitude'},
                        {data: 'popularity', name: 'popularity'},
                        {data: 'is_sng', name: 'is_sng', searchable: false},
                        {data: 'hotels_regions.parent', name: 'parent_id'},
                        {data: "actions", name: 'actions', orderable: false, searchable: false}],
            });

            /* END BASIC */

            $('#rowList').on('click', 'a.deleteRow', function () {
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
                            url: SITE_URL + "/cp/hotels_regions/destroy/" + rowid,
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