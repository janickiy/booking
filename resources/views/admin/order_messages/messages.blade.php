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

                <p>
                    <a href="{{ URL::route('admin.order_messages.list') }}">назад</a>
                </p>

                <!-- widget div-->
                <div>
                    <div class="table-responsive">
                        <table id="itemList" class="table table-hover" width="100%">
                            <thead>
                            <tr>
                                <th>Содержание</th>
                                <th>Имя</th>
                                <th>Собщение</th>
                                <th>Действие</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>

                    {!! Form::open(['url' => URL::route('admin.order_messages.add_answer'), 'method' => 'post', 'class' => 'form-horizontal', 'id' => "addAnswer"]) !!}

                    {!! Form::hidden('order_id', $order_id) !!}

                    <div class="box-body">

                        <div class="form-group">

                            {!! Form::label('message', 'Ответ', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::textarea('message', null, ['class' => 'form-control', 'rows'=> 3, 'id' => 'message']) !!}

                                @if ($errors->has('message'))

                                    <span class="text-danger">{{ $errors->first('message') }}</span>

                                @endif

                            </div>
                        </div>

                    </div>

                    <div class="box-footer">
                        <div class="col-sm-4">

                        </div>
                        <div class="col-sm-5 margin-bottom-10">

                            {!! Form::submit( 'Ответить', ['class'=>'btn btn-success']) !!}

                        </div>
                    </div>

                    {!! Form::close() !!}

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

            $('#itemList').dataTable({
                "sDom": "flrtip",
                "autoWidth": true,
                "bSort":false,
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
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! URL::route('admin.datatable.messages',['receiver_id' => $receiver_id]) !!}'
                },
                columns: [
                    {data: 'order_id', name: 'order_id', orderable: false},
                    {data: 'user.contacts', name: 'user.contacts', orderable: false},
                    {data: 'message', name: 'message', orderable: false},
                ],
            });
        });

    </script>

@endsection