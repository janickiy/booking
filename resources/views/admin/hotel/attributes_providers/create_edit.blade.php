@extends('admin.layout')

@section('title', isset($attribute) ? 'Редактирование атрибута поставщика' : 'Добавление атрибута поставщика')

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

                    {!! Form::open(['url' => isset($attribute) ? URL::route('admin.hotels_attributes_providers.update') : URL::route('admin.hotels_attributes_providers.store',['attribute_id' => $attribute_id]), 'method' => isset($attribute) ? 'put' : 'post', 'class' => 'form-horizontal', 'id' => "admin"]) !!}

                    <div class="box-body">

                        {!! isset($attribute) ? Form::hidden('attribute_id', $attribute->id) : '' !!}

                        <div class="form-group">

                            {!! Form::label('provider', 'Поставщик*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('provider', old('provider', isset($attribute) ? $attribute->provider : null), ['class' => 'form-control', 'id'=>'provider']) !!}

                                @if ($errors->has('provider'))
                                    <p class="text-danger">{{ $errors->first('provider') }}</p>
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

                        <div class="form-group">

                            {!! Form::label('code', 'Код*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('code', old('code', isset($attribute) ? $attribute->code : null), ['class' => 'form-control', 'id'=>'code']) !!}

                                @if ($errors->has('code'))
                                    <p class="text-danger">{{ $errors->first('code') }}</p>
                                @endif

                            </div>
                        </div>


                    </div>
                    <div class="box-footer">
                        <div class="col-sm-4">
                            <a href="{{ URL::route('admin.hotels_attributes_providers.list',['attribute_id' => $attribute_id]) }}" class="btn btn-danger btn-flat pull-right">Отменить</a>
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