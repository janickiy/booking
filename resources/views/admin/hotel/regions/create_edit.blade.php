@extends('admin.layout')

@section('title', isset($region) ? 'Редактирование региона' : 'Добавление региона')

@section('css')

@endsection

@section('content')

    <h2>{!! isset($region) ? 'Редактирование' : 'Добавление' !!} региона</h2>
    <div class="row-fluid">

        <div class="col">

            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget jarviswidget-color-blueDark" data-widget-editbutton="false">

                <!-- widget div-->
                <div>

                    <p>*-обязательные поля</p>

                    {!! Form::open(['url' => isset($region) ? URL::route('admin.hotels_regions.update') : URL::route('admin.hotels_regions.store'), 'method' => isset($region) ? 'put' : 'post', 'class' => 'form-horizontal', 'id' => "region_form"]) !!}

                    <div class="box-body">

                        {!! isset($region) ? Form::hidden('id', $region->id) : '' !!}


                        <div class="form-group">

                            {!! Form::label('name_ru', 'Название*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('name_ru', old('name_ru', isset($region) ? $region->name_ru : null), ['class' => 'form-control', 'id'=>'name_ru']) !!}

                                @if ($errors->has('name_ru'))
                                    <p class="text-danger">{{ $errors->first('name_ru') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('name_en', 'Название (English)*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('name_en', old('name_en', isset($region) ? $region->name_en : null), ['class' => 'form-control', 'id'=>'name_en']) !!}

                                @if ($errors->has('name_en'))
                                    <p class="text-danger">{{ $errors->first('name_en') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('slug_ru', 'Слаг*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('slug_ru', old('slug_ru', isset($region) ? $region->slug_ru : null), ['class' => 'form-control', 'id'=>'slug_ru']) !!}

                                @if ($errors->has('slug_ru'))
                                    <p class="text-danger">{{ $errors->first('slug_ru') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('slug_en', 'Слаг (English)*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('slug_en', old('slug_en', isset($region) ? $region->slug_en : null), ['class' => 'form-control', 'id'=>'slug_en']) !!}

                                @if ($errors->has('slug_en'))
                                    <p class="text-danger">{{ $errors->first('slug_en') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('parent_slug', 'Родительский слаг', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('parent_slug', old('parent_slug', isset($region) ? $region->parent_slug : null), ['class' => 'form-control', 'id'=>'parent_slug']) !!}

                                @if ($errors->has('parent_slug'))
                                    <p class="text-danger">{{ $errors->first('parent_slug') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('latitude', 'Широта', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('latitude', old('latitude', isset($region) ? $region->latitude : null), ['class' => 'form-control', 'id'=>'latitude']) !!}

                                @if ($errors->has('latitude'))
                                    <p class="text-danger">{{ $errors->first('latitude') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('longitude', 'Долгота', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('longitude', old('longitude', isset($region) ? $region->longitude : null), ['class' => 'form-control', 'id'=>'longitude']) !!}

                                @if ($errors->has('longitude'))
                                    <p class="text-danger">{{ $errors->first('longitude') }}</p>
                                @endif

                            </div>
                        </div>


                        <div class="form-group">

                            {!! Form::label('popularity', 'Приоритет*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('popularity', old('popularity', isset($region) ? $region->popularity: 0), ['class' => 'form-control', 'id'=>'popularity']) !!}

                                @if ($errors->has('popularity'))
                                    <p class="text-danger">{{ $errors->first('popularity') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('type', 'Тип*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::select('type', $options, old('type', isset($region) ? $region->type : null), ['class' => 'select2 type_region', 'style' => 'width:100%', 'id' => 'type', 'placeholder' => 'Выберите тип']) !!}

                                @if ($errors->has('type'))
                                    <span class="text-danger">{{ $errors->first('type') }}</span>
                                @endif

                            </div>
                        </div>

                        <div class="form-group" @if (!isset($region))style="display:none"@endif id="region">

                            {!! Form::label('parent_id', 'Регион', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::select('parent_id', $region_options, old('type', isset($region) ? $region->parent_id : null), ['class' => 'select2 itemName', 'style' => 'width:100%', 'id' => 'parent_id', 'placeholder' => 'Выберите регион']) !!}

                                @if ($errors->has('parent_id'))
                                    <span class="text-danger">{{ $errors->first('parent_id') }}</span>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('is_sng', 'СНГ', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::checkbox('is_sng', 1, isset($region) ? ($region->is_sng == 1 ? true : false): false) !!}

                                @if ($errors->has('is_sng'))
                                    <span class="text-danger">{{ $errors->first('is_sng') }}</span>
                                @endif

                            </div>
                        </div>

                    </div>
                    <div class="box-footer">
                        <div class="col-sm-4">
                            <a href="{{ URL::route('admin.hotels_regions.list') }}"
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

        $(document).ready(function () {



            $(function () {

                $("#region_form").on("change keyup input", function () {

                    var Type = $("#type").val();

                    if (Type !== 'undefined' && Type !== '')
                        $("#region").show();
                    else
                        $("#region").hide();

                });

                $("#region_form").on("change keyup input", function () {
                    $('.itemName').select2({

                        width: '100%',
                        placeholder: 'Выберите регион',
                        ajax: {
                            url: '{!! URL::route('admin.datatable.ajax') !!}?action=get_hotel_regions&type=' + $("#type").val() + '{!! isset($region) ? '&id=' . $region : '' !!}',
                            dataType: 'json',
                            processResults: function (data) {
                                return {
                                    results: data
                                };
                            },
                            cache: false
                        }
                    });
                })
            })

            $(".select2").select2({
                width: '100%'
            });

        })



    </script>

@endsection