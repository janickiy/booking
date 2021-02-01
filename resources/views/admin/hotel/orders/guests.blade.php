@extends('admin.layout')

@section('title', $title)

@section('css')

@endsection

@section('content')

    <h2>{!! $title !!}</h2>

    @include('admin.notifications')

    <div class="row-fluid">

        <div class="col">

            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget jarviswidget-color-blueDark" data-widget-editbutton="false">

                <!-- widget div-->
                <div>

                    <p>
                        <a href="{{ URL::route('admin.orders_hotels.list') }}">назад</a>
                    </p>

                    <h3>Отель</h3>

                    <div class="table-responsive">
                        <table id="rowList" class="table table-striped table-bordered table-hover" width="100%">
                            <thead>
                            <tr>
                                <th data-hide="phone">Фамилия</th>
                                <th data-hide="phone">Имя</th>
                                <th data-hide="phone">Дата рождения</th>
                                <th data-hide="phone">Пол</th>
                                <th data-hide="phone">Граждансво</th>
                            </tr>
                            </thead>
                            <tbody>

                            @if(isset($guests))

                                @foreach($guests as $guest)
                                    <tr>
                                        <td>{{$guest["last_name"]}}</td>
                                        <td>{{$guest["first_name"]}}</td>
                                        <td>{{$guest["birthday"]}}</td>
                                        <td>{{$guest["gender"]}}</td>
                                        <td>{{\App\Helpers\StringHelpers::getCitizenship($guest["citizenship"])}}</td>
                                    </tr>
                                @endforeach

                            @endif

                            </tbody>
                        </table>
                    </div>


                </div>

            </div>
            <!-- end widget content -->

        </div>
        <!-- end widget div -->

    </div>
    <!-- end widget -->

    </div>

@endsection

@section('js')
    <script>

    </script>
@endsection