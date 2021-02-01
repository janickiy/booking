@extends('admin.layout')



@section('title', isset($tmanager) ? 'Редактирование контента' : 'Добавление контента' )

@section('css')

@endsection

@section('content')

    <h2>{!! isset($tmanager) ? 'Редактирование' : 'Добавление' !!} контента</h2>


    <div class="row-fluid">
        <div class="col">

            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget jarviswidget-color-blueDark" data-widget-editbutton="false">

                <!-- widget div-->
                <div>

                    <p>*-обязательные поля</p>

                    {!! Form::open(['url' => isset($tmanager) ? URL::route('admin.tmanager.update') : URL::route('admin.tmanager.store'), 'method' => isset($tmanager) ? 'put' : 'post', 'class' => 'form-horizontal']) !!}

                    {!! isset($tmanager) ? Form::hidden('id', $tmanager->id) : '' !!}

                    <div class="box-body">

                        <div class="form-group">

                            {!! Form::label('locale', 'Локализация*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('locale', old('locale', isset($tmanager->locale) ? $tmanager->locale : null), ['class' => 'form-control', 'placeholder'=>'Локализация', 'id' => 'locale']) !!}

                                @if ($errors->has('locale'))
                                    <span class="text-danger">{{ $errors->first('locale') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('group', 'Группа*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('group', old('group', isset($tmanager->group) ? $tmanager->group : null), ['class' => 'form-control', 'placeholder' => 'Группа', 'id' => 'group']) !!}

                                @if ($errors->has('group'))
                                    <span class="text-danger">{{ $errors->first('group') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('key', 'Ключ*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('key', old('key', isset($tmanager->key) ? $tmanager->key : null), ['class' => 'form-control', 'placeholder' => 'Группа', 'id' => 'key']) !!}

                                @if ($errors->has('key'))
                                    <span class="text-danger">{{ $errors->first('key') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('value', 'Значение*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('value', old('key', isset($tmanager->value) ? $tmanager->value : null), ['class' => 'form-control', 'placeholder' => 'Значение', 'id' => 'value']) !!}

                                @if ($errors->has('value'))
                                    <span class="text-danger">{{ $errors->first('value') }}</span>
                                @endif
                            </div>
                        </div>

                    </div>

                    <div class="box-footer">
                        <div class="col-sm-4">
                            <a href="{{ URL::route('admin.tmanager.list') }}"
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

    {!! Html::script('/admin/js/plugin/ckeditor/ckeditor.js') !!}

    <script>
        $(document).ready(function () {

            $("#titleRu").on("change keyup input click", function () {

                if (this.value.length >= 2) {

                    var Title = this.value;

                    var request = $.ajax({
                        url: '{!! URL::route('admin.datatable.ajax') !!}',
                        method: "POST",
                        data: {
                            action: "get_content_slug",
                            title: Title
                        },
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        dataType: "json"
                    });

                    request.done(function (data) {

                        if (data.slug != null && data.slug != '') {
                            $("#slug").val(data.slug);
                        }
                    });
                }

                console.log(html);

            });

            CKEDITOR.replace('contentRu');
            CKEDITOR.replace('contentEn');

        });
    </script>


@endsection