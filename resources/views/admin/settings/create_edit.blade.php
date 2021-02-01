@extends('admin.layout')

@section('title', isset($setting) ? 'Редактирование параметра' : 'Добавление параметра')

@section('css')

@endsection

@section('content')

    <h2>{!! isset($setting) ? 'Редактирование' : 'Добавление' !!} параметра</h2>
    <div class="row-fluid">

        <div class="col">

            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget jarviswidget-color-blueDark" data-widget-editbutton="false">

                <!-- widget div-->
                <div>

            <p>*-обязательные поля</p>

            {!! Form::open(['url' => isset($setting) ? URL::route('admin.settings.update') : URL::route('admin.settings.store'), 'method' => isset($setting) ? 'put' : 'post', 'class' => 'form-horizontal', 'id' => "addSetting"]) !!}

            {!! isset($setting) ? Form::hidden('settingId', $setting->settingId) : '' !!}

            <div class="box-body">
                <div class="form-group">

                    {!! Form::label('name', 'Ключ*', ['class' => 'col-sm-3 control-label']) !!}

                    <div class="col-sm-6">

                        {!! Form::text('name', old('name', isset($setting->name) ? $setting->name : null), ['class' => 'form-control', 'placeholder' => 'Ключ', 'id' => 'name']) !!}

                        @if ($errors->has('name'))
                        <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif

                    </div>
                </div>

                <div class="form-group">

                    {!! Form::label('value', 'Значение*', ['class' => 'col-sm-3 control-label']) !!}

                    <div class="col-sm-6">

                        {!! Form::text('value', old('value', isset($setting->value) ? $setting->value : null), ['class' => 'form-control', 'placeholder' => 'Ключ', 'id' => 'value']) !!}

                        @if ($errors->has('value'))
                        <span class="text-danger">{{ $errors->first('value') }}</span>
                        @endif

                    </div>
                </div>

                <div class="form-group">

                    {!! Form::label('description', 'Описание', ['class' => 'col-sm-3 control-label']) !!}

                    <div class="col-sm-6">

                        {!! Form::textarea('description', old('description', isset($setting) ? $setting->description : null), ['class' => 'form-control', 'rows'=> 3, 'id' => 'description']) !!}

                        @if ($errors->has('description'))

                            <span class="text-danger">{{ $errors->first('description') }}</span>

                        @endif

                    </div>
                </div>

                <div class="form-group">

                    {!! Form::label('accessLevel', 'Уровень доступа*', ['class' => 'col-sm-3 control-label']) !!}

                    <div class="col-sm-6">

                        {!! Form::selectRange('accessLevel', 0, 9, isset($setting->accessLevel) ? $setting->accessLevel : 1, ['placeholder' => 'выберите', 'class' => 'assessment form-control', 'id' => 'accessLevel']) !!}

                        <span class="text-danger">{{ $errors->first('accessLevel') }}</span>
                    </div>
                </div>
            </div>

            <div class="box-footer">
                <div class="col-sm-4">
                    <a href="{{ URL::route('admin.settings.list') }}"
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

@endsection

@section('js')
    <script>

    </script>
@endsection