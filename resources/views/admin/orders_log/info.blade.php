@extends('admin.layout')

@section('title', 'полезная нагрузка')

@section('css')


@endsection

@section('content')

    @if (isset($title))<h2>{!! $title !!}</h2>@endif

    <div class="row-fluid">

        <div class="col">

            <p><a href="#" onclick="window.history.back();">назад</a></p>
            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget jarviswidget-color-blueDark" id="wid-id-1" data-widget-editbutton="false">

                <div>

                    <div class="box-header">
                        <div class="row">
                            <div class="col-md-12">
                                <buttom id="buttom_json" class="btn btn-info btn-sm pull-left" data-tree="true">json
                                </buttom>
                            </div>
                        </div>
                    </div>
                    <br>

                    <div id="json" style="padding-bottom: 15px; display:none;">

                        {{ json_encode($log) }}

                    </div>

                    <div id="tree" style="padding-bottom: 15px;">
                        @if ($log->payload)

                            {!! \App\Helpers\StringHelpers::tree($log->payload) !!}

                        @endif
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
            $('.tree-checkbox').treeview({
                collapsed: true,
                animated: 'medium',
                unique: false
            });

                $('#buttom_json').on('click', function () {

                if ($(this).attr('data-tree') == 'true') {
                    $(this).attr('data-tree', "false");
                    $('#tree').hide();
                    $('#json').show();
                } else {
                    $(this).attr('data-tree', "true");
                    $('#json').hide();
                    $('#tree').show();
                }

            });
        });

    </script>

@endsection