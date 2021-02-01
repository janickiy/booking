@extends('admin.layout')

@section('title', isset($pageData) ? 'Редактирование контента' : 'Добавление контента' )

@section('css')

@endsection

@section('content')

    <h2>{!! isset($pageData) ? 'Редактирование' : 'Добавление' !!} контента</h2>

    <div class="row-fluid">
        <div class="col">

            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget jarviswidget-color-blueDark" data-widget-editbutton="false">

                <!-- widget div-->
                <div>

                    <p>*-обязательные поля</p>

                    {!! Form::open(['url' => isset($pageData) ? URL::route('admin.pages.update') : URL::route('admin.pages.store'), 'method' => isset($pageData) ? 'put' : 'post', 'class' => 'form-horizontal']) !!}

                    {!! isset($pageData) ? Form::hidden('id', $pageData->id) : '' !!}

                    <div class="box-body">

                        @foreach($languages as $language)

                            <div class="form-group">

                                {!! Form::label('title[' . $language->locale . ']', 'Название (' . $language->name . ')', ['class' => 'col-sm-3 control-label']) !!}

                                <div class="col-sm-6">

                                    {!! Form::text('title[' . $language->locale . ']', isset($page_title[$language->locale]) ? $page_title[$language->locale] : null, ['class' => 'form-control', 'placeholder' => 'Название (' . $language->name . ')', 'id' => 'title_' . $language->locale]) !!}

                                </div>
                            </div>

                        @endforeach

                        <div class="form-group">
                            <div class="col-sm-3 control-label"></div>
                            <div class="col-sm-6">

                                @if ($errors->has('title'))
                                    <span class="text-danger">{{ $errors->first('title') }}</span>
                                @endif

                            </div>
                        </div>

                        @foreach($languages as $language)

                            <div class="form-group">

                                {!! Form::label('content[' . $language->locale . ']', 'Содержание (' . $language->name . ')', ['class' => 'col-sm-3 control-label']) !!}

                                <div class="col-sm-6">

                                    {!! Form::textarea('content[' . $language->locale . ']', isset($page_content[$language->locale]) ? $page_content[$language->locale] : null, ['class' => 'form-control', 'id'=> 'content_' . $language->locale]) !!}

                                </div>
                            </div>

                        @endforeach

                        <div class="form-group">
                            <div class="col-sm-3 control-label"></div>
                            <div class="col-sm-6">

                                @if ($errors->has('content'))
                                    <span class="text-danger">{{ $errors->first('content') }}</span>
                                @endif

                            </div>
                        </div>

                        @foreach($languages as $language)

                            <div class="form-group">

                                {!! Form::label('meta_title[' . $language->locale . ']', 'meta title (' . $language->name . ')', ['class' => 'col-sm-3 control-label']) !!}

                                <div class="col-sm-6">

                                    {!! Form::text('meta_title[' . $language->locale . ']', isset($page_meta_title[$language->locale]) ? $page_meta_title[$language->locale] : null, ['class' => 'form-control', 'placeholder' => 'meta title', 'id' => 'meta_title_' . $language->locale]) !!}

                                </div>
                            </div>

                        @endforeach

                        <div class="form-group">
                            <div class="col-sm-3 control-label"></div>
                            <div class="col-sm-6">

                                @if ($errors->has('meta_title'))
                                    <span class="text-danger">{{ $errors->first('meta_title') }}</span>
                                @endif

                            </div>
                        </div>

                        @foreach($languages as $language)

                            <div class="form-group">

                                {!! Form::label('meta_description[' . $language->locale . ']', 'meta description (' . $language->name . ')', ['class' => 'col-sm-3 control-label']) !!}

                                <div class="col-sm-6">

                                    {!! Form::textarea('meta_description[' . $language->locale . ']', isset($page_meta_description[$language->locale]) ? $page_meta_description[$language->locale] : null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => 'meta description', 'id' => 'meta_description_' . $language->locale]) !!}


                                </div>
                            </div>

                        @endforeach

                        <div class="form-group">
                            <div class="col-sm-3 control-label"></div>
                            <div class="col-sm-6">

                                @if ($errors->has('meta_descriptionRu'))
                                    <span class="text-danger">{{ $errors->first('meta_descriptionRu') }}</span>
                                @endif

                            </div>
                        </div>

                        @foreach($languages as $language)

                            <div class="form-group">

                                {!! Form::label('meta_keywords[' . $language->locale . ']', 'meta keywords (' . $language->name . ')', ['class' => 'col-sm-3 control-label']) !!}

                                <div class="col-sm-6">

                                    {!! Form::text('meta_keywords[' . $language->locale . ']', isset($page_meta_keywords[$language->locale]) ? $page_meta_keywords[$language->locale] : null, ['class' => 'form-control', 'placeholder' => 'meta keywords', 'id' => 'meta_keywords_' . $language->locale]) !!}

                                    @if ($errors->has('meta_descriptionRu'))
                                        <span class="text-danger">{{ $errors->first('meta_descriptionRu') }}</span>
                                    @endif
                                </div>
                            </div>

                        @endforeach

                        <div class="form-group">
                            <div class="col-sm-3 control-label"></div>
                            <div class="col-sm-6">

                                @if ($errors->has('meta_descriptionRu'))
                                    <span class="text-danger">{{ $errors->first('meta_descriptionRu') }}</span>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('slug', 'ЧПУ*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('slug', old('slug', isset($pageData->slug) ? $pageData->slug : null), ['class' => 'form-control', 'placeholder' => 'ЧПУ', 'id' => 'slug']) !!}

                                @if ($errors->has('slug'))
                                    <span class="text-danger">{{ $errors->first('slug') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('parent_id', 'Раздел', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::select('parent_id', $options, old('parent_id', isset($pageData) ? $pageData->parent_id : null), ['class' => 'select2', 'style' => 'width:100%', 'placeholder' => 'Выберите']) !!}

                                @if ($errors->has('parent_id'))
                                    <span class="text-danger">{{ $errors->first('parent_id') }}</span>
                                @endif

                            </div>
                        </div>
                    </div>

                    <div class="form-group">

                        {!! Form::label('published', 'Публиковать', ['class' => 'col-sm-3 control-label']) !!}

                        <div class="col-sm-6">

                            {!! Form::checkbox('published', 1, isset($pageData) ? ($pageData->status == 1 ? true : false): true) !!}

                            @if ($errors->has('published'))
                                <span class="text-danger">{{ $errors->first('published') }}</span>
                            @endif

                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('page_path', 'Тип контента', ['class' => 'col-sm-3 control-label']) !!}
                        <div class="col-sm-6">
                            <div class="pull-left margin-right-13">

                                {!! Form::radio('page_path',1, (isset($pageData) && $pageData->contentType == 1) or !isset($pageData) ? true : false, ['class' => 'form-check-input']) !!}

                                {!! Form::label('inlineRadio1', 'Страница', ['class' => 'form-check-label']) !!}

                            </div>
                            <div class="pull-left  margin-right-13">

                                {!! Form::radio('page_path',0, isset($pageData->contentType) && $pageData->contentType == 0 ? true : false, ['class' => 'form-check-input']) !!}

                                {!! Form::label('inlineRadio2', 'Раздел', ['class' => 'form-check-label']) !!}

                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <div class="col-sm-4">
                            <a href="{{ URL::route('admin.pages.list') }}"
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

            $("#title_ru").on("change keyup input click", function () {

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

            @foreach($languages as $language)

            CKEDITOR.replace('content_{{ $language->locale }}');

            @endforeach

            $('.select2').select2({
                width: '100%',
            });

        });
    </script>ad

@endsection