@extends('layouts.app', ['page'=>'login', 'news'=>false])

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="panel panel-default">
                <h2>Личный кабинет</h2>
                <p class="lead">Здравствуйте, {{auth()->user()->username}}</p>
                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    Поздравляем, вы вошли!
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
