@extends('admin.layout')

@section('title', 'Dashboard')

@section('css')


@endsection

@section('content')

    <h2>Dashboard</h2>

    @include('admin.notifications')

    <!-- widget grid -->
    <section id="widget-grid" class="">

        <!-- row -->
        <div class="row">
            <article class="col-sm-12">
                <!-- new widget -->
                <div class="jarviswidget" id="wid-id-0" data-widget-togglebutton="false" data-widget-editbutton="false"
                     data-widget-fullscreenbutton="false" data-widget-colorbutton="false"
                     data-widget-deletebutton="false">
                    <!-- widget options:
                    usage: <div class="jarviswidget" id="wid-id-0" data-widget-editbutton="false">

                    data-widget-colorbutton="false"
                    data-widget-editbutton="false"
                    data-widget-togglebutton="false"
                    data-widget-deletebutton="false"
                    data-widget-fullscreenbutton="false"
                    data-widget-custombutton="false"
                    data-widget-collapsed="true"
                    data-widget-sortable="false"

                    -->
                    <header>
                        <span class="widget-icon"> <i class="glyphicon glyphicon-stats txt-color-darken"></i> </span>
                        <h2>Live Feeds </h2>

                        <ul class="nav nav-tabs pull-right in" id="myTab">
                            <li class="active">
                                <a data-toggle="tab" href="#s1"><i class="fa fa-flag"></i> <span class="hidden-mobile hidden-tablet">Сервисы</span></a>
                            </li>

                            <li>
                                <a data-toggle="tab" href="#s2"><i class="fa fa-user"></i> <span class="hidden-mobile hidden-tablet">Пользователи портала</span></a>
                            </li>

                            <li>
                                <a data-toggle="tab" href="#s3"><i class="fa fa-area-chart"></i> <span class="hidden-mobile hidden-tablet">Логи</span></a>
                            </li>
                        </ul>

                    </header>

                    <!-- widget div-->
                    <div class="no-padding">
                        <!-- widget edit box -->

                        <!-- end widget edit box -->

                        <div class="widget-body">
                            <!-- content -->
                            <div id="myTabContent" class="tab-content">
                                <div class="tab-pane fade active in padding-10 no-padding-bottom" id="s1">
                                    <div class="row no-space">

                                        <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 show-stats">

                                            <table class="table table-striped table-bordered table-hover" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th data-hide="phone"> Сервис</th>
                                                        <th data-class="expand"> Статус</th>
                                                        <th data-hide="phone"> Сообщение</th>
                                                        <th data-hide="phone"> Проверено</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($statusAPI as $status)
                                                    <tr>
                                                        <td>{{ $status->api_name }}</td>
                                                        <td>{{ $status->status === true ? 'включен' : 'выключен' }}</td>
                                                        <td>{{ $status->message }}</td>
                                                        <td>{{ $status->checkAt }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                                <!-- end s1 tab pane -->

                                <div class="tab-pane fadein padding-10 no-padding-bottom" id="s2">
                                    <div class="row no-space">

                                        <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 show-stats">

                                            <table class="table table-striped table-bordered table-hover" width="100%">
                                                <thead>
                                                <tr>
                                                    <th data-hide="phone"> ID</th>
                                                    <th data-class="expand"> Логин</th>
                                                    <th data-hide="phone"> Email</th>
                                                    <th data-hide="phone"> Дата регистрации</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($lastUsers as $lastuser)
                                                    <tr>
                                                     <td>{{ $lastuser->userId }}</td>
                                                    <td>{{ $lastuser->login }}</td>
                                                    <td>{{ $lastuser->email }}</td>
                                                    <td>{{ $lastuser->created_at }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                                <!-- end s2 tab pane -->

                                <div class="tab-pane fade in padding-10" id="s3">

                                    <div class="row no-space">

                                        <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 show-stats">

                                            <table class="table table-striped table-bordered table-hover" width="100%">
                                                <thead>
                                                <tr>
                                                    <th data-hide="phone"> ID</th>
                                                    <th data-class="expand"> ID сессии</th>
                                                    <th data-hide="phone"> Дата</th>
                                                    <th data-hide="phone"> Пользователь</th>
                                                    <th data-hide="phone"> Реферер</th>
                                                    <th data-hide="phone"> Путь</th>
                                                    <th data-hide="phone"> Маршрут</th>
                                                </tr>
                                                </thead>
                                                <tbody>

                                                @foreach($logs as $log)
                                                <tr>
                                                    <td><a href="{{ url('/cp/logs/info/' . $log->session_log_id) }}">{{ $log->session_log_id }}</a></td>
                                                    <td>{{ $log->session_id }}</td>
                                                    <td>{{ $log->created_at }}</td>
                                                    <td>{{ App\Helpers\StringHelpers::getUserById($log->user_id) ?? $log->user_id > 0 }}</td>
                                                    <td>{{ $log->referer }}</td>
                                                    <td>{{ $log->path }}</td>
                                                    <td>{{ $log->route }}</td>
                                                </tr>
                                                @endforeach
                                                </tbody>
                                            </table>

                                        </div>
                                    </div>
                                </div>
                                <!-- end s3 tab pane -->
                            </div>

                            <!-- end content -->
                        </div>

                    </div>
                    <!-- end widget div -->
                </div>
                <!-- end widget -->

            </article>
        </div>

    </section>
    <!-- end widget grid -->

@endsection

@section('js')

    <script>


    </script>

@endsection