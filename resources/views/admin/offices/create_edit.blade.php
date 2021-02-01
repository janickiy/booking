@extends('admin.layout')

@section('title', isset($office) ? 'Редактирование контента' : 'Добавление контента' )

@section('css')

@endsection

@section('content')

    <h2>{!! isset($office) ? 'Редактирование' : 'Добавление' !!} контента</h2>
    <div class="row-fluid">
        <div class="col">

            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget jarviswidget-color-blueDark" data-widget-editbutton="false">

                <!-- widget div-->
                <div>

                    <p>*-обязательные поля</p>

                    {!! Form::open(['url' => isset($office) ? URL::route('admin.offices.update') : URL::route('admin.offices.store'), 'method' => isset($office) ? 'put' : 'post', 'class' => 'form-horizontal']) !!}

                    {!! isset($office) ? Form::hidden('id', $office->id) : '' !!}

                    <div class="box-body">

                        <div class="form-group">

                            {!! Form::label('titleRu', 'Название (Ru)*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('titleRu', old('titleRu', isset($office->name['ru']) ? $office->name['ru'] : null), ['class' => 'form-control', 'placeholder'=>'Название офиса (Ru)', 'id' => 'titleRu']) !!}

                                @if ($errors->has('titleRu'))
                                    <span class="text-danger">{{ $errors->first('titleRu') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('titleEn', 'Название (En)*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('titleEn', old('titleEn', isset($office->name['en']) ? $office->name['en'] : null), ['class' => 'form-control', 'placeholder'=>'Название офиса (En)', 'id' => 'titleEn']) !!}

                                @if ($errors->has('titleEn'))
                                    <span class="text-danger">{{ $errors->first('titleEn') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('contactEmail', 'Контактный Email', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::email('contact_email', old('contactEmail', isset($office->contact_email) ? $office->contact_email : null), ['class' => 'form-control', 'type' => 'email', 'placeholder' => 'Контактный Email', 'id' => 'contactEmail']) !!}

                                @if ($errors->has('contactEmail'))
                                    <span class="text-danger">{{ $errors->first('contactEmail') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('deliveryEmail', 'Email для доставок', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::email('delivery_email', old('deliveryEmail', isset($office->delivery_email) ? $office->delivery_email : null), ['class' => 'form-control', 'placeholder' => 'Email для доставок', 'id' => 'deliveryEmail']) !!}

                                @if ($errors->has('deliveryEmail'))
                                    <span class="text-danger">{{ $errors->first('deliveryEmail') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('phone', 'Телефон', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('phone', old('phone', isset($office->phone) ? $office->phone : null), ['class' => 'form-control', 'placeholder' => 'Телефон', 'id' => 'phone']) !!}

                                @if ($errors->has('phone'))
                                    <span class="text-danger">{{ $errors->first('phone') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('closed', 'Офис закрыт', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::checkbox('closed', 1, isset($office) ? ($office->closed == 1 ? true : false): false) !!}

                                @if ($errors->has('closed'))
                                    <span class="text-danger">{{ $errors->first('closed') }}</span>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('addressEn', 'Адрес (En)', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('addressEn', old('addressEn', isset($office->address['en']) ? $office->address['en'] : null), ['class' => 'form-control', 'placeholder' => 'Адрес (En)', 'id' => 'addressEn']) !!}

                                @if ($errors->has('addressEn'))
                                    <span class="text-danger">{{ $errors->first('addressEn') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('addressRu', 'Адрес (Ru)', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('addressRu', old('addressRu', isset($office->address['ru']) ? $office->address['ru'] : null), ['class' => 'form-control', 'placeholder' => 'Адрес (RU)', 'id' => 'addressRu']) !!}

                                @if ($errors->has('addressRu'))
                                    <span class="text-danger">{{ $errors->first('addressRu') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('longitude', 'Долгота', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('longitude', old('longitude', isset($office->longitude) ? $office->longitude : null), ['class' => 'form-control', 'placeholder' => 'Долгота', 'id' => 'longitude']) !!}

                                @if ($errors->has('longitude'))
                                    <span class="text-danger">{{ $errors->first('longitude') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('latitude', 'Широта', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('latitude', old('latitude', isset($office->latitude) ? $office->latitude : null), ['class' => 'form-control', 'placeholder' => 'Широта', 'id' => 'latitude']) !!}

                                @if ($errors->has('latitude'))
                                    <span class="text-danger">{{ $errors->first('latitude') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('code', 'Код города', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('code', old('code', isset($office->code) ? $office->code : null), ['class' => 'form-control', 'placeholder' => 'Код города', 'id' => 'code']) !!}

                                @if ($errors->has('code'))
                                    <span class="text-danger">{{ $errors->first('code') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('fax', 'Факс', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('fax', old('fax', isset($office->fax) ? $office->fax : null), ['class' => 'form-control', 'placeholder' => 'факс', 'id' => 'fax']) !!}

                                @if ($errors->has('fax'))
                                    <span class="text-danger">{{ $errors->first('fax') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('cityRu', 'Город (Ru)*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('cityRu', old('cityRu', isset($office->city['ru']) ? $office->city['ru'] : null), ['class' => 'form-control', 'placeholder'=>'Город (Ru)', 'id' => 'cityRu']) !!}

                                @if ($errors->has('cityRu'))
                                    <span class="text-danger">{{ $errors->first('cityRu') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('cityEn', 'Город (En)*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('cityEn', old('cityEn', isset($office->city['en']) ? $office->city['en'] : null), ['class' => 'form-control', 'placeholder'=>'Город (En)', 'id' => 'cityEn']) !!}

                                @if ($errors->has('cityEn'))
                                    <span class="text-danger">{{ $errors->first('cityEn') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('smsPhone', 'Телефон', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('sms_phone', old('fax', isset($office->sms_phone) ? $office->sms_phone : null), ['class' => 'form-control', 'placeholder' => 'Телефон', 'id' => 'smsPhone']) !!}

                                @if ($errors->has('smsPhone'))
                                    <span class="text-danger">{{ $errors->first('smsPhone') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('iataCodes', 'iata_codes', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('iata_codes', old('iataCodes', isset($office->iata_codes) ? $office->iata_codes : null), ['class' => 'form-control', 'placeholder' => 'iataCodes', 'id' => 'iataCodes']) !!}

                                @if ($errors->has('iataCodes'))
                                    <span class="text-danger">{{ $errors->first('iataCodes') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('schedule', 'Расписание', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('schedule', old('schedule', isset($office->schedule) ? $office->schedule : null), ['class' => 'form-control', 'placeholder' => 'Расписание', 'id' => 'schedule']) !!}

                                @if ($errors->has('schedule'))
                                    <span class="text-danger">{{ $errors->first('schedule') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <div class="col-sm-4">
                            <a href="{{ URL::route('admin.offices.list') }}"
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
            $('.select2').select2({
                width: '100%',
            });
        });
    </script>

@endsection