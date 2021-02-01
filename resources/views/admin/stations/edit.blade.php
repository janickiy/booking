@extends('admin.layout')

@section('title', 'Редактирование станции' )

@section('css')

@endsection

@section('content')

    <h2>Редактирование станции</h2>
    <div class="row-fluid">

        <div class="col">

            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget jarviswidget-color-blueDark" data-widget-editbutton="false">

                <!-- widget div-->
                <div>

                    {!! Form::open(['url' => URL::route('admin.stations.update') , 'method' => 'put', 'class' => 'form-horizontal']) !!}

                    <div class="box-body">

                        {!! Form::hidden('railwayStationId', $station->railwayStationId)  !!}

                        <div class="form-group">

                            {!! Form::label('code', 'Экспресс-код', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('code', old('code', $station->code), ['class' => 'form-control', 'id' => 'code',  'readonly' ]) !!}

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('nameRu', 'Имя Станции', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('nameRu', old('nameRu', $station->nameRu), ['class' => 'form-control', 'id' => 'nameRu','readonly']) !!}

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('nameEn', 'Имя Станции EN', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('nameEn', old('nameRu', $station->nameEn), ['class' => 'form-control', 'id' => 'nameEn','readonly']) !!}

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('customNameRu', 'Измененное имя станции', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('customNameRu', old('customNameRu', $station->custom->nameRu), ['class' => 'form-control', 'id' => 'customNameRu']) !!}

                                @if ($errors->has('customNameRu'))
                                    <p class="text-danger">{{ $errors->first('customNameRu') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('customNameEn', 'Измененное имя станции EN', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('customNameEn', old('customNameRu', $station->custom->nameEn), ['class' => 'form-control', 'id' => 'customNameEn']) !!}

                                @if ($errors->has('customNameEn'))
                                    <p class="text-danger">{{ $errors->first('customNameEn') }}</p>
                                @endif

                            </div>
                        </div>
                        <div class="box-footer">
                            <div class="col-sm-4">
                                <a href="{{ URL::route('admin.stations.list') }}"
                                   class="btn btn-danger btn-flat pull-right">Отменить</a>
                            </div>
                            <div class="col-sm-5 margin-bottom-10">
                                {!! Form::submit( 'Отправить', ['class'=>'btn btn-success']) !!}
                            </div>
                        </div>

                        {!! Form::close() !!}

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')

@endsection