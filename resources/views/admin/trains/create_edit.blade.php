@extends('admin.layout')

@section('title', isset($train) ? 'Редактирование поезда' : 'Добавление поезда')

@section('css')

@endsection

@section('content')

    <h2>{!! isset($train) ? 'Редактирование' : 'Добавление' !!} поезда</h2>
    <div class="row-fluid">

        <div class="col">

            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget jarviswidget-color-blueDark" data-widget-editbutton="false">

                <!-- widget div-->
                <div>

                    <p>*-обязательные поля</p>

                    {!! Form::open(['url' => isset($train) ? URL::route('admin.trains.update') : URL::route('admin.trains.store'), 'method' => isset($train) ? 'put' : 'post', 'class' => 'form-horizontal']) !!}

                    <div class="box-body">

                        {!! isset($train) ? Form::hidden('id', $train->id) : '' !!}

                        <div class="form-group">

                            {!! Form::label('trainNumber', 'Номер поезда*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('trainNumber', old('trainNumber', isset($train) ? $train->trainNumber : null), ['class' => 'form-control', 'id' => 'trainNumber', isset($train) && $train->isAddedManually == 0 ? 'readonly' : '']) !!}

                                @if ($errors->has('trainNumber'))
                                    <p class="text-danger">{{ $errors->first('trainNumber') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('trainNumberToGetRoute', 'Номер маршрута поезда*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('trainNumberToGetRoute', old('trainNumberToGetRoute', isset($train) ? $train->trainNumberToGetRoute : null), ['class' => 'form-control', 'id' => 'trainNumberToGetRoute', isset($train) && $train->isAddedManually == 0 ? 'readonly' : '']) !!}

                                @if ($errors->has('trainNumberToGetRoute'))
                                    <p class="text-danger">{{ $errors->first('trainNumberToGetRoute') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('trainDescription', 'Название*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('trainName', old('trainName', isset($train) ? $train->trainName : null), ['class' => 'form-control', 'id' => 'trainName', isset($train) && $train->isAddedManually == 0 ? 'readonly' : '']) !!}

                                @if ($errors->has('trainName'))
                                    <p class="text-danger">{{ $errors->first('trainName') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('trainDescription', 'Описание', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::textarea('trainDescription', old('trainDescription', isset($train) ? $train->trainDescription : null), ['class' => 'form-control', 'rows' => 3, 'id' => 'trainDescription', isset($train) && $train->isAddedManually == 0 ? 'readonly' : '']) !!}

                                @if ($errors->has('trainDescription'))
                                    <span class="text-danger">{{ $errors->first('trainDescription') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('carriers', 'Перевозчики (перечислить через запятую)*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::textarea('carriers', old('carriers', isset($train) ? implode(",", $train->carriers) : null), ['class' => 'form-control', 'rows' => 3, 'id' => 'carriers', isset($train) && $train->isAddedManually == 0 ? 'readonly' : '']) !!}

                                @if ($errors->has('carriers'))
                                    <span class="text-danger">{{ $errors->first('carriers') }}</span>
                                @endif
                            </div>
                        </div>

                    </div>
                    <div class="box-footer">
                        <div class="col-sm-4">
                            <a href="{{ URL::route('admin.trains.list') }}" class="btn btn-danger btn-flat pull-right">Отменить</a>
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

@endsection

@section('js')

@endsection