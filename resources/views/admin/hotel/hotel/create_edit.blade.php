@extends('admin.layout')

@section('title', isset($hotel) ? 'Редактирование отеля' : 'Добавление отеля')

@section('css')

@endsection

@section('content')

    <h2>{!! isset($hotel) ? 'Редактирование' : 'Добавление' !!} отели</h2>
    <div class="row-fluid">

        <div class="col">

            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget jarviswidget-color-blueDark" data-widget-editbutton="false">

                <!-- widget div-->
                <div>

                    <p>*-обязательные поля</p>

                    {!! Form::open(['url' => isset($hotel) ? URL::route('admin.hotel.update') : URL::route('admin.hotel.store'), 'method' => isset($hotel) ? 'put' : 'post', 'class' => 'form-horizontal', 'id' => "admin"]) !!}

                    <div class="box-body">

                        {!! isset($hotel) ? Form::hidden('id', $hotel->id) : '' !!}


                        <div class="form-group">

                            {!! Form::label('name_ru', 'Название*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('name_ru', old('name_ru', isset($hotel) ? $hotel->name_ru : null), ['class' => 'form-control', 'id'=>'name_ru']) !!}

                                @if ($errors->has('name_ru'))
                                    <p class="text-danger">{{ $errors->first('name_ru') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('name_en', 'Название (English)', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('name_en', old('name_en', isset($hotel) ? $hotel->name_en : null), ['class' => 'form-control', 'id'=>'name_en']) !!}

                                @if ($errors->has('name_en'))
                                    <p class="text-danger">{{ $errors->first('name_en') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('address_ru', 'Адрес*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::textarea('address_ru', isset($hotel) ? $hotel->address_ru : null, ['class' => 'form-control', 'rows'=> 3, 'id'=> 'address_ru']) !!}

                                @if ($errors->has('address_ru'))
                                    <p class="text-danger">{{ $errors->first('address_ru') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('address_en', 'Адрес (English)', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::textarea('address_en', isset($hotel) ? $hotel->address_en : null, ['class' => 'form-control', 'rows'=> 3, 'id'=> 'address_en']) !!}

                                @if ($errors->has('address_en'))
                                    <p class="text-danger">{{ $errors->first('address_en') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('region_id', 'Регион', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::select('region_id', $options, old('region_id', isset($hotel) ? $hotel->region_id : null), ['class' => 'select2 itemName', 'style' => 'width:100%', 'id' => 'region_id', 'placeholder' => 'Выберите регион']) !!}

                                @if ($errors->has('region_id'))
                                    <span class="text-danger">{{ $errors->first('region_id') }}</span>
                                @endif

                            </div>
                        </div>

                    </div>
                    <div class="box-footer">
                        <div class="col-sm-4">
                            <a href="{{ URL::route('admin.hotel.list') }}" class="btn btn-danger btn-flat pull-right">Отменить</a>
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

        $(document).ready(function () {
            $(".select2").select2({
                width: '100%'
            });
            $('.itemName').select2({
                width: '100%',
                placeholder: 'Выберите регион',
                ajax: {
                    url: '{!! URL::route('admin.datatable.ajax') !!}?action=get_hotel_regions&type=city',
                    dataType: 'json',
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            });
        })

    </script>

@endsection