@extends('admin.layout')

@section('title', isset($userData) ? 'Редактирование пользователя' : 'Добавление пользователя')

@section('css')

@endsection

@section('content')

    <h2>{!! isset($userData) ? 'Редактирование' : 'Добавление' !!} пользователя</h2>

    <div class="row-fluid">

        <div class="col">

            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget jarviswidget-color-blueDark" data-widget-editbutton="false">

                <!-- widget div-->
                <div>

                    <p>*-обязательные поля</p>

                    {!! Form::open(['url' => isset($userData) ? URL::route('admin.users.update') : URL::route('admin.users.store'), 'method' => isset($userData) ? 'put' : 'post', 'class' => 'form-horizontal', 'id' => "admin"]) !!}

                    <div class="box-body">

                        {!! isset($userData) ? Form::hidden('adminUserId', $userData->adminUserId) : '' !!}

                        <div class="form-group">

                            {!! Form::label('login', 'Логин*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('login', old('login', isset($userData) ? $userData->login : null), ['class' => 'form-control', 'id' => 'login']) !!}

                                @if ($errors->has('login'))
                                    <p class="text-danger">{{ $errors->first('login') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('name', 'Имя*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::text('name', old('name', isset($userData) ? $userData->name : null), ['class' => 'form-control', 'id' => 'login']) !!}

                                @if ($errors->has('name'))
                                    <p class="text-danger">{{ $errors->first('name') }}</p>
                                @endif

                            </div>
                        </div>

                        <div class="form-group">

                            {!! Form::label('email', 'Email*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::email('email', old('email', isset($userData) ? $userData->email : null), ['class' => 'form-control', 'id'=>'email']) !!}

                                @if ($errors->has('email'))
                                    <p class="text-danger">{{ $errors->first('email') }}</p>
                                @endif

                            </div>
                        </div>

                        @if (isset($userData) && ($userData->adminUserId != \Auth::id()))


                        @else

                            <div class="form-group">

                                {!! Form::label('password', isset($userData) ? 'Пароль' : 'Пароль*', ['class' => 'col-sm-3 control-label']) !!}

                                <div class="col-sm-6">

                                    {!! Form::password('password', null, ['class' => 'form-control', 'id'=>'password']) !!}

                                    @if ($errors->has('password'))
                                        <p class="text-danger">{{ $errors->first('password') }}</p>
                                    @endif

                                </div>
                            </div>

                            <div class="form-group">

                                {!! Form::label('confirm_password', isset($userData) ? 'Подтвердите пароль' : 'Подтвердите пароль*', ['class' => 'col-sm-3 control-label']) !!}

                                <div class="col-sm-6">

                                    {!! Form::password('confirm_password', null, ['class' => 'form-control', 'id'=>'confirm_password']) !!}

                                    @if ($errors->has('confirm_password'))
                                        <p class="text-danger">{{ $errors->first('confirm_password') }}</p>
                                    @endif

                                </div>
                            </div>

                        @endif

                        <div class="form-group">

                            {!! Form::label('adminRoleId', 'Роль*', ['class' => 'col-sm-3 control-label']) !!}

                            <div class="col-sm-6">

                                {!! Form::select('adminRoleId[]', $role_list, isset($userData) ? $adminRoleId :  null, ['class' => 'form-control', 'multiple', 'id' => 'adminRoleId']) !!}

                                @if ($errors->has('adminRoleId'))
                                    <p class="text-danger">{{ $errors->first('adminRoleId') }}</p>
                                @endif

                            </div>
                        </div>

                        @if (isset($userData) && $user->hasAccess('admin.users.changeuserpassword') && $userData->adminUserId != \Auth::id())
                            <div class="form-group">
                                <div class="col-sm-3"></div>

                                <div class="col-sm-8" id="changeUserPassword" data-id="{!! $userData->adminUserId !!}">

                                    <a href="#" class="btn  btn-success">Отправить новый пароль пользователю</a>

                                </div>
                            </div>
                        @endif

                    </div>
                    <div class="box-footer">
                        <div class="col-sm-4">
                            <a href="{{ URL::route('admin.users.list') }}" class="btn btn-danger btn-flat pull-right">Отменить</a>
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
            $(document).on("click", "#changeUserPassword", function () {

                $.ajax({
                    type: "GET",
                    url: SITE_URL + "/cp/users/change-user-password/" + $(this).attr('data-id'),
                    dataType: "json",
                    success: function (data) {
                        if (data.result == true) {
                            $("#changeUserPassword").html('<div class="alert alert-success fade in"><i class="fa-fw fa fa-check"></i>' + data.message + '</div>');
                        } else {
                            $("#changeUserPassword").html('<div class="alert alert-danger fade in"><i class="fa-fw fa fa-times"></i><strong>Ошибка!</strong>' + data.message + '</div>');
                        }
                    }
                });
            });
        });

    </script>

@endsection