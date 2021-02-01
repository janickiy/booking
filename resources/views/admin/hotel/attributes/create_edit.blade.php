@extends('admin.layout')

@section('title', isset($attribute) ? 'Редактирование атрибута' : 'Добавление атрибута')

@section('css')

@endsection

@section('content')

    <h2>{!! isset($attribute) ? 'Редактирование' : 'Добавление' !!} атрибута</h2>
    <div class="row-fluid">

        <div class="col">

            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget jarviswidget-color-blueDark" data-widget-editbutton="false">

                <!-- widget div-->
                <div>

                    <p>*-обязательные поля</p>

                    {!! Form::open(['url' => isset($attribute) ? URL::route('admin.hotels_attributes.update') : URL::route('admin.hotels_attributes.store'), 'method' => isset($attribute) ? 'put' : 'post', 'class' => 'form-horizontal', 'id' => "admin"]) !!}

                    <div class="box-body">

                        {!! isset($attribute) ? Form::hidden('id', $attribute->id) : '' !!}


                        <div class="form-group">

                            {!! Form::label('name_ru', 'Название*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('name_ru', old('name_ru', isset($attribute) ? $attribute->name_ru : null), ['class' => 'form-control', 'id'=>'name_ru']) !!}

                                @if ($errors->has('name_ru'))
                                    <p class="text-danger">{{ $errors->first('name_ru') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('name_en', 'Название (English)', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('name_en', old('name_en', isset($attribute) ? $attribute->name_en : null), ['class' => 'form-control', 'id'=>'name_en']) !!}

                                @if ($errors->has('name_en'))
                                    <p class="text-danger">{{ $errors->first('name_en') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('type', 'Тип*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('type', old('type', isset($attribute) ? $attribute->type : null), ['class' => 'form-control', 'id'=>'type']) !!}

                                @if ($errors->has('type'))
                                    <p class="text-danger">{{ $errors->first('type') }}</p>
                                @endif

                            </div>
                        </div>

                    </div>
                    <div class="box-footer">
                        <div class="col-sm-4">
                            <a href="{{ URL::route('admin.hotels_attributes.list') }}" class="btn btn-danger btn-flat pull-right">Отменить</a>
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