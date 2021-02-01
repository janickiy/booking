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

                    @if($user->hasAccess('trains'))
                        <div class="box-header">
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="{{ URL::route('admin.trains.create') }}"
                                       class="btn btn-info btn-sm pull-left"><span class="fa fa-plus"> &nbsp;</span>Добавить
                                        поезд</a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <br>
                    <div class="table-responsive">
                        <table id="itemList" class="table table-striped table-bordered table-hover" width="100%">
                            <thead>
                            <tr>
                                <th >Номер поезда</th>
                                <th >Номер маршрута поезда</th>
                                <th>Название</th>
                                <th >Описание</th>
                                <th >Перевозчики</th>
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
    <script type="text/javascript">
        $(document).ready(function () {
            var table = $("#itemList").DataTable({
                "sDom": "flrtip",
                "autoWidth": true,
                "oLanguage": {
                    "sSearch": '<span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span>'
                },
                'createdRow': function (row, data, dataIndex) {
                    $(row).attr('id', 'rowid_' + data['id']);
                },
                processing: true,
                serverSide: true,
                ajax: '{!! URL::route('admin.datatable.trains') !!}',
                columns: [
                    {data: 'trainNumber', name: 'trainNumber'},
                    {data: 'trainNumberToGetRoute', name: 'trainNumberToGetRoute'},
                    {data: 'trainName', name: 'trainName'},
                    {data: 'trainDescription', name: 'trainDescription'},
                    {data: 'carriers', name: 'carriers', orderable: false},
                    {data: "actions", name: 'actions', orderable: false, searchable: false}
                ],
            });
        });

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
                    cancelButtonText: "Отмена",
                    closeOnConfirm: false
                },
                function (isConfirm) {
                    if (!isConfirm) return;
                    $.ajax({
                        url: SITE_URL + "/cp/railway/trains-car/destroy/" + rowid,
                        type: "DELETE",
                        dataType: "html",
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        success: function () {
                            $("#rowid_" + rowid).remove();
                            swal("Сделано!", "Данные успешно удаленны!", "success");
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            swal("Ошибка при удалении!", "Попробуйте еще раз", "error");
                        }
                    });
                });
        });

    </script>
@endsection