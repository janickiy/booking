@extends('admin.layout')

@section('title', $title)

@section('css')

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

                    <div class="box-header">
                        <div class="row">
                            <div class="col-md-12 padding-bottom-10">
                                <a href="{{ URL::route('admin.menu.create') }}" class="btn btn-info btn-sm pull-left"><span class="fa fa-plus"> &nbsp;</span>Добавить меню</a>
                            </div>
                        </div>
                    </div>

                    {!! build_tree($cats,0) !!}

                </div>
                <!-- end widget content -->

            </div>
            <!-- end widget div -->

        </div>
        <!-- end widget -->

    </div>

@endsection

@section('js')

@endsection