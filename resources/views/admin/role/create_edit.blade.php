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

                    {!! Form::open(['url' => isset($role) ? URL::route('admin.role.update') : URL::route('admin.role.store'), 'method' => isset($role) ? 'put' : 'post', 'class' => 'form-horizontal', 'id' => "addRole"]) !!}

                    {!! isset($role) ? Form::hidden('adminRoleId', $role->adminRoleId) : '' !!}

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

                        <div class="form-group">

                            {!! Form::label('accessLevel', 'Уровень доступа*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::selectRange('accessLevel', 1, 9, isset($role->accessLevel) ? $role->accessLevel : 9, ['placeholder' => 'выберите', 'class' => 'assessment form-control', 'id' => 'accessLevel']) !!}

                                @if ($errors->has('accessLevel'))
                                    <span class="text-danger">{{ $errors->first('accessLevel') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Права доступа</label>

                            <div class="col-sm-6">
                                <ul style="display: inline-block;list-style-type: none;padding:0; margin:0;">

                                    @foreach($roout_list as $route)

                                        <li class="checkbox" style="display: inline-block; min-width: 155px;">

                                            {{ $route->getName() }}

                                            <ul>
                                                @foreach(["GET" => "r","POST" => "c", "PUT" => "w", "DELETE" => "d"] as $key => $value)
                                                    <li>
                                                        @if(isset($role))

                                                            @if (isset($role->accessMap[$route->getName()]))

                                                                {!! Form::checkbox('accessMap['.$route->getName().'][]', $value, in_array($value, $role->accessMap[$route->getName()] ) ? true : false, [ in_array($key,$route->methods) === false ? 'disabled' : '' ]) !!}

                                                            @else
                                                                {!! Form::checkbox('accessMap['.$route->getName().'][]', $value, false, [ in_array($key,$route->methods) === false ? 'disabled' : '' ] ) !!}
                                                            @endif
                                                        @else

                                                            {!! Form::checkbox('accessMap['.$route->getName().'][]', $value, true, [ in_array($key,$route->methods) === false ? 'disabled' : '' ]) !!}

                                                        @endif

                                                        {{ $key }}

                                                    </li>
                                                @endforeach

                                            </ul>
                                        </li>
                                    @endforeach

                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <div class="col-sm-4">
                            <a href="{{ URL::route('admin.role.list') }}" class="btn btn-danger btn-flat pull-right">Отменить</a>
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