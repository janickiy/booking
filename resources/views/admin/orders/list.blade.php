@extends('admin.layout')

@section('title', $title)

@section('css')

    <style>

        .sweet-alert {
            width:  850px;
        }

    </style>

@endsection

@section('content')

    @if (isset($title))<h2>{!! $title !!}</h2>@endif

    @include('admin.notifications')

    <div class="row-fluid">

        <div class="col">

            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget jarviswidget-color-blueDark" data-widget-editbutton="false">

                <!-- widget div-->
                <div>

                    <div class="table-responsive">
                        <table id="itemList" class="table table-striped table-bordered table-hover" width="100%">
                            <thead>
                            <tr>
                                <th>Заказ</th>
                                <th>Дата и время заказа</th>
                                <th>Пользователь</th>
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

        var orderId = decodeURI(window.location.hash).substr(1);

        if (orderId && parseInt(orderId)) {
            $.ajax({
                url: '{!! URL::route('admin.datatable.ajax') !!}',
                method: "POST",
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data: {
                    action: "get_order_items",
                    id: orderId
                },
                cache: false,
                success: function(html){
                    swal({
                        title: '<h3 class="text-left">Заказ № ' + orderId + '</h3>',
                        text: html,
                        customClass: 'sweet-alert',
                        html: true,
                    });
                }
            });
        }

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
                "order": [[0, "desc"]],
                processing: true,
                serverSide: true,
                ajax: '{!! URL::route('admin.datatable.orders') !!}',
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'user.contacts', name: 'user.userId'},
                ],
            });

            $('#itemList').on('click', 'a.orderRow', function () {
                var rowid = $(this).attr('id');

                $.ajax({
                    url: '{!! URL::route('admin.datatable.ajax') !!}',
                    method: "POST",
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: {
                        action: "get_order_items",
                        id: rowid
                    },
                    cache: false,
                    success: function(html){
                        swal({
                            title: '<h3 class="text-left">Заказ № ' + rowid + '</h3>',
                            text: html,
                            customClass: 'sweet-alert',
                            html: true,
                        });
                    }
                });
            });
        });

    </script>
@endsection