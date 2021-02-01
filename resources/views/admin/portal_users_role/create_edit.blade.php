@extends('admin.layout')

@section('title', isset($role) ? 'Редактирование роли' : 'Добавление роли')

@section('css')

@endsection

@section('content')

    <h2>{!! isset($role) ? 'Редактирование' : 'Добавление' !!} роли</h2>
    <div class="row-fluid">

        <div class="col">

            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget jarviswidget-color-blueDark" data-widget-editbutton="false">

                <!-- widget div-->
                <div>

                    <p>*-обязательные поля</p>

                    {!! Form::open(['url' => isset($role) ? URL::route('admin.portal_users_role.update') : URL::route('admin.portal_users_role.store'), 'method' => isset($role) ? 'put' : 'post', 'class' => 'form-horizontal', 'id' => "addRole"]) !!}

                    {!! isset($role) ? Form::hidden('roleId', $role->roleId) : '' !!}

                    <div class="box-body">
                        <div class="form-group">

                            {!! Form::label('name', 'Название*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('name', old('name', isset($role->name) ? $role->name : null), ['class' => 'form-control', 'placeholder'=>'Название', 'id' => 'name']) !!}

                                @if ($errors->has('name'))
                                    <span class="text-danger">{{ $errors->first('name') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="box-body">
                        <div class="form-group">

                            {!! Form::label('description', 'Описание*', ['class' => 'col-sm-3 control-label']) !!}
                            <div class="col-sm-6">
                                {!! Form::textarea('description', old('description', isset($role) ? $role->description : null), ['class' => 'form-control', 'rows' => 3]) !!}

                                @if ($errors->has('description'))
                                    <span class="text-danger">{{ $errors->first('description') }}</span>
                                @endif

                            </div>

                        </div>
                    </div>

                    <div class="box-footer">
                        <div class="col-sm-4">
                            <a href="{{ URL::route('admin.portal_users_role.list') }}"
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