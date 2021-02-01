@extends('admin.layout')

@section('title', isset($menu) ? 'Редактирование меню' : 'Добавление меню' )

@section('css')

@endsection

@section('content')

    <h2>{!! isset($menu) ? 'Редактирование' : 'Добавление' !!} категории</h2>

    <div class="row-fluid">
        <div class="col">

            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget jarviswidget-color-blueDark" data-widget-editbutton="false">

                <!-- widget div-->
                <div>

                    <p>*-обязательные поля</p>

                    {!! Form::open(['url' => isset($menu) ? URL::route('admin.menu.update') : URL::route('admin.menu.store'), 'method' => isset($menu) ? 'put' : 'post', 'class' => 'form-horizontal']) !!}

                    {!! isset($menu) ? Form::hidden('id', $menu->id) : '' !!}

                    {!!  Form::hidden('parent_id', isset($parent_id) ? $parent_id : 0) !!}

                    <div class="box-body">

                        @foreach($languages as $language)

                            <div class="form-group">

                                {!! Form::label('title[' . $language->locale . ']', 'Название (' . $language->name . ')', ['class' => 'col-sm-3 control-label']) !!}

                                <div class="col-sm-6">

                                    {!! Form::hidden('locale[' . $language->locale . ']', $language->locale) !!}

                                    {!! Form::text('title[' . $language->locale . ']', isset($menu_title[$language->locale]) ? $menu_title[$language->locale] : null, ['class' => 'form-control', 'placeholder'=>'Название (' . $language->name . ') ']) !!}

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

                        <div class="form-group">
                            {!! Form::label('item_order', 'Порядок *', ['class' => 'col-sm-3 control-label']) !!}
                            <div class="col-sm-6">

                                {!! Form::text('item_order', old('item_order',  $item_order), ['class' => 'form-control', 'placeholder'=>'Порядок']) !!}

                            </div>

                            @if ($errors->has('item_order'))
                                <span class="text-danger">{{ $errors->first('item_order') }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            {!! Form::label('menu_type', 'Тип пункта меню', ['class' => 'col-sm-3 control-label']) !!}
                            <div class="col-sm-6">
                                <div class="pull-left margin-right-13">

                                    {!! Form::radio('menu_type','url', (isset($menu->menu_type) && $menu->menu_type == 'url') or !isset($menu->menu_type) ? true : false, ['class' => 'form-check-input', 'id' => 'inlineRadio1']) !!}

                                    {!! Form::label('inlineRadio1', 'url', ['class' => 'form-check-label']) !!}

                                </div>
                                <div class="pull-left  margin-right-13">

                                    {!! Form::radio('menu_type','catalog', isset($menu->menu_type) && $menu->menu_type == 'catalog' ? true : false, ['class' => 'form-check-input', 'id' => 'inlineRadio2']) !!}

                                    {!! Form::label('inlineRadio2', 'раздел', ['class' => 'form-check-label']) !!}

                                </div>
                                <div class="pull-left  margin-right-13">

                                    {!! Form::radio('menu_type','pages', isset($menu->menu_type) && $menu->menu_type == 'pages' ? true : false, ['class' => 'form-check-input', 'id' => 'inlineRadio3']) !!}

                                    {!! Form::label('inlineRadio3', 'страница', ['class' => 'form-check-label']) !!}

                                </div>
                            </div>
                        </div>

                        <div class="form-group" id="form_url" @if ((isset($menu->menu_type) && $menu->menu_type == 'url') or !isset($menu->menu_type)) style="" @else style="display: none;" @endif>
                            {!! Form::label('url', 'URL', ['class' => 'col-sm-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::text('url', old('url', isset($menu) ? $menu->url : null), ['class' => 'form-control', 'placeholder' => 'url']) !!}
                            </div>

                            @if ($errors->has('url'))
                                <span class="text-danger">{{ $errors->first('url') }}</span>
                            @endif
                        </div>

                        <div class="form-group" id="form_catalog"  @if (isset($menu->menu_type) && $menu->menu_type == 'catalog') style="" @else style="display: none;" @endif >

                            {!! Form::label('item_id', 'Раздел', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::select('item_id', $options_catalog, isset($menu) ? $menu->item_id : null, ['placeholder' => 'Выберите', 'class' => 'form-control select2']) !!}

                                @if ($errors->has('item_id'))
                                    <span class="text-danger">{{ $errors->first('item_id') }}</span>
                                @endif

                            </div>
                        </div>

                        <div class="form-group" id="form_articles" @if (isset($menu->menu_type) && $menu->menu_type == 'pages') style="" @else style="display: none;" @endif>

                            {!! Form::label('item_id', 'Страница', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::select('item_id', $options_articles, isset($menu) ? $menu->item_id : null, ['placeholder' => 'Выберите', 'class' => 'select2', 'style' => "width:100%"]) !!}

                                @if ($errors->has('item_id'))
                                    <span class="text-danger">{{ $errors->first('item_id') }}</span>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('parent_id', 'Категория', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                @if ($parent_id > 0)

                                    {!! Form::select('parent_id', $options, $parent_id, ['class' => 'select2', 'style' => "width:100%"]) !!}

                                @else

                                    {!! Form::select('parent_id', $options, isset($menu) ? $menu->parent_id : 0, ['class' => 'select2', 'style' => "width:100%"]) !!}

                                @endif

                                @if ($errors->has('parent_id'))
                                    <span class="text-danger">{{ $errors->first('parent_id') }}</span>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('status', 'Отображать', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::checkbox('status', 1, isset($menu) ? ($menu->status == 1 ? true : false): true) !!}

                                @if ($errors->has('status'))
                                    <span class="text-danger">{{ $errors->first('status') }}</span>
                                @endif

                            </div>
                        </div>

                    </div>

                    <div class="box-footer">
                        <div class="col-sm-4">
                            <a href="{{ URL::route('admin.menu.list') }}" class="btn btn-danger btn-flat pull-right">Отменить</a>
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

        $('#inlineRadio1').on('click', function() {
            $("#form_url").show();
            $("#form_catalog").hide();
            $("#form_articles").hide();
        });

        $('#inlineRadio2').on('click', function() {
            $("#form_url").hide();
            $("#form_catalog").show();
            $("#form_articles").hide();
        });

        $('#inlineRadio3').on('click', function() {
            $("#form_url").hide();
            $("#form_catalog").hide();
            $("#form_articles").show();
        });

        $('.select2').select2({
            width: '100%',
        });

    </script>

@endsection