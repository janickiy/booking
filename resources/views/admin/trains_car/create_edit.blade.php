@extends('admin.layout')

@section('title', isset($trainsCar) ? 'Редактирование типа вагона' : 'Добавление типа вагона')

@section('css')

    <style type="text/css">

        .item__remove-btn, .item__remove-btn:active {
            position: absolute;
            top: 0;
            right: 0;
            left: auto!important;
        }

        .schemes-list__item {
            position: relative;
        }

    </style>

@endsection

@section('content')
    <script id="scheme-item-template" type="text/template">
        @include('admin.trains_car.scheme_item')
    </script>

    <h2>{!! isset($trainsCar) ? 'Редактирование' : 'Добавление' !!} типа вагона</h2>

    <div class="row-fluid">

        <div class="col">

            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget jarviswidget-color-blueDark" data-widget-editbutton="false">

                <!-- widget div-->
                <div>

                    <p>*-обязательные поля</p>

                    {!! Form::open(['url' => isset($trainsCar) ? URL::route('admin.trainscar.update') : URL::route('admin.trainscar.store'), 'files' => true, 'method' => isset($trainsCar) ? 'put' : 'post', 'class' => 'form-horizontal']) !!}

                    <div class="box-body">

                        {!! isset($trainsCar) ? Form::hidden('id', $trainsCar->id) : '' !!}

                        <div class="form-group">

                            {!! Form::label('typeRu', 'Тип вагона для отображения пассажиру*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('typeRu', old('typeRu', isset($trainsCar) ? $trainsCar->typeRu : null), ['class' => 'form-control', 'id'=>'typeRu', isset($trainsCar) && $trainsCar->isAddedManually == 0 ? 'readonly' : '']) !!}

                                @if ($errors->has('typeRu'))
                                    <p class="text-danger">{{ $errors->first('typeRu') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('typeEn', 'Тип вагона*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('typeEn', old('typeEn', isset($trainsCar) ? $trainsCar->typeEn : null), ['class' => 'form-control', 'id'=>'typeEn', isset($trainsCar) && $trainsCar->isAddedManually == 0 ? 'readonly' : '']) !!}

                                @if ($errors->has('typeEn'))
                                    <p class="text-danger">{{ $errors->first('typeEn') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('typeScheme', 'Тип схемы*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('typeScheme', old('typeScheme', isset($trainsCar) ? $trainsCar->typeScheme : null), ['class' => 'form-control', 'id' => 'typeScheme', isset($trainsCar) && $trainsCar->isAddedManually == 0 ? 'readonly' : '']) !!}

                                @if ($errors->has('typeScheme'))
                                    <p class="text-danger">{{ $errors->first('typeScheme') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('description', 'Дополнительное описание вагона', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::textarea('description', old('description', isset($trainsCar) ? $trainsCar->description : null), ['class' => 'form-control', 'rows' => 3]) !!}

                                @if ($errors->has('description'))
                                    <span class="text-danger">{{ $errors->first('description') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('train_id', 'Поезд', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                @if(isset($trainsCar) && $trainsCar->isAddedManually == 0)
                                    {!! Form::select('train_id', $options, isset($trainsCar) ? $trainsCar->train_id : null, ['placeholder' => 'Выберите', 'class' => 'form-control', 'disabled' => 'disabled']) !!}
                                @else
                                    {!! Form::select('train_id', $options, isset($trainsCar) ? $trainsCar->train_id : null, ['placeholder' => 'Выберите', 'class' => 'form-control']) !!}
                                @endif

                                @if ($errors->has('train_id'))
                                    <p class="text-danger">{{ $errors->first('train_id') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('trainName', 'Название поезда', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('trainName', old('trainName', isset($trainsCar) ? $trainsCar->trainName : null), ['class' => 'form-control', 'id' => 'trainName', isset($trainsCar) && $trainsCar->isAddedManually == 0 ? 'readonly' : '']) !!}

                                @if ($errors->has('typeScheme'))
                                    <p class="text-danger">{{ $errors->first('typeScheme') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-3 control-label">
                                Схемы вагонов
                                <button type="button" class="btn btn-xs btn-default add-custom-car-scheme"> <i class="fa fa-plus"></i> </button>
                            </div>
                            <div class="col-sm-6 schemes-list">

                                @if ($errors->has('schemes.*'))
                                    <p class="text-danger">{{$errors->first('schemes.*') }}</p>
                                @endif

                                @if(isset($trainsCar) && $trainsCar->schemes)
                                    @foreach($trainsCar->schemes as $key => $schemeUrl)
                                        @include('admin.trains_car.scheme_item', ['key' => $key, 'schemeUrl' => $schemeUrl, 'iteration' => $loop->iteration ])
                                    @endforeach
                                @endif

                            </div>
                        </div>

                    </div>
                    <div class="box-footer">
                        <div class="col-sm-4">

                            <a href="{{ URL::route('admin.trainscar.list') }}"  class="btn btn-danger btn-flat pull-right">Отменить</a>

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

    <script type="text/javascript">
        $(document).ready(function () {
            var idTrainsCar = $('.deleteIm').attr('data-id');

            /** BEGIN мультифайл */
            $(".schemes-list").on("change", ".item__file", function() {
                var input = this;
                var preview = $(input).closest(".schemes-list__item").find(".item__preview");

                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        preview.attr('src', e.target.result);
                    };
                    reader.readAsDataURL(this.files[0]);
                } else {
                    preview.removeAttr('src');
                }
            });

            $(".schemes-list").on("click", ".item__remove-btn", function() {
                var itemElement = $(this).closest('.schemes-list__item');
                itemElement.remove();
            });

            $(".add-custom-car-scheme").click(function(){
                var template = $("#scheme-item-template").html();
                var key = $('.schemes-list__container .item').length + 1;

                template = $(template);
                template.find('input.item__key').val(key === 1 ? 'default' : '').attr('name', 'schemes['+key+'][key]');
                template.find('.item__preview').removeAttr('src');
                template.find('.item__file-hidden').val('').attr('name', 'schemes['+key+'][file_path]');
                template.find('input.item__file').val('').attr('name', 'schemes['+key+'][file]');

                $(".schemes-list").append(template);
            });

        });
    </script>

@endsection