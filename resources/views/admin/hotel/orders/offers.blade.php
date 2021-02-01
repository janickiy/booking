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
                                <th data-hide="phone">Название</th>
                                <th data-hide="phone">Адрес</th>
                                <th data-hide="phone">Регион</th>
                                <th data-hide="phone">Кол. звезд</th>
                                <th data-hide="phone">Местоположение</th>
                                <th data-hide="phone">Метод</th>
                                <th data-hide="phone">Дата и время заселение</th>
                                <th data-hide="phone">Дата и время выселение</th>
                                <th data-hide="phone">Номеров</th>
                                <th data-hide="phone">Взрослых</th>
                                <th data-hide="phone">Детей</th>
                                <th data-hide="phone">Телефон</th>
                                <th data-hide="phone">Email</th>
                                <th data-hide="phone">Поставщик</th>
                                <th data-hide="phone">Язык</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{{ isset($target['name']) ? $target['name'] : '' }}</td>
                                <td>{{ isset($target['address']) ? $target['address'] : '' }}</td>
                                <td>{{ isset($region->name_ru) ? $region->name_ru : '' }}</td>
                                <td>{{isset($target['stars']) ? $target['stars'] : ''}}</td>
                                <td>{{ isset($target['latitude']) ? 'latitude: ' . $target['latitude'] : '' }}
                                    <br>{{ isset($target['longitude']) ? 'longitude: ' . $target['longitude'] : '' }}
                                </td>
                                <td></td>
                                <td>{{ isset($params['checkin']) ? $params['checkin'] : ''}} {{ isset($target['check_in']) ? $target['check_in'] : ''}}</td>
                                <td>{{ isset($params['checkout']) ? $params['checkout'] : ''}} {{ isset($target['check_out']) ? $target['check_out'] : ''}}</td>
                                <td>{{ isset($params['rooms']) ? $params['rooms'] : 0 }}</td>
                                <td>{{ isset($params['adults']) ? $params['adults'] : 0 }}</td>
                                <td>{{ isset($params['children'][0]) ? $params['children'][0] : 0 }}</td>
                                <td>{{ isset($target['phone']) ? $target['phone'] : '' }}</td>
                                <td>{{ isset($target['email']) ? $target['email'] : '' }}</td>
                                <td>{{ isset($provider) ? $provider : '' }}</td>
                                <td>{{ isset($params['lang']) ? $params['lang'] : '' }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <h3>Предложение</h3>


                    <div class="table-responsive">

                        <table id="rowList" class="table table-striped table-bordered table-hover" width="100%">
                            <thead>
                            <tr>
                                <th data-hide="phone">ID</th>
                                <th data-hide="phone">ID номера</th>
                                <th data-hide="phone">Название</th>
                                <th data-hide="phone">Оригинальнгое название</th>
                                <th data-hide="phone">Количество</th>
                                <th data-hide="phone">Вместимость</th>
                                <th data-hide="phone">Кровати</th>
                                <th data-hide="phone">Размер</th>
                                <th data-hide="phone">Аннулирование</th>
                                <th data-hide="phone">Питание</th>
                                <th data-hide="phone">Код доступности</th>
                                <th data-hide="phone">Цена</th>
                            </tr>
                            </thead>
                            <tbody>

                            @if(isset($target['offers']))

                                @foreach($target['offers'] as $offer)

                                    <tr>
                                        <td>{{ isset($offer['offer_id']) ? $offer['offer_id'] : '' }}</td>
                                        <td>{{ isset($offer['room_id']) ? $offer['room_id'] : '' }}</td>
                                        <td>
                                        {{ isset($offer['name']) ? $offer['name'] : '' }}</th>
                                        <td>{{ isset($offer['original_name']) ? $offer['original_name'] : '' }}</td>
                                        <td>{{ isset($offer['count']) ? $offer['count'] : 0 }}</td>
                                        <td>{{ isset($offer['capacity']) ? $offer['capacity'] : 0 }}</td>
                                        <td>
                                            @if(isset($offer["beds"]))
                                                @for($i=0;$i<count($offer["beds"]);$i++)
                                                   тип: {{$offer["beds"][$i]['type']}} кол:{{$offer["beds"][$i]['count']}} <br>
                                                @endfor
                                            @endif
                                        </td>
                                        <td>{{ isset($offer['size']) ? $offer['size'] : 0 }}</td>
                                        <td></td>
                                        <td>{{ isset($offer['meal_type']) ? $offer['meal_type'] : '' }}</td>
                                        <td>{{ isset($offer['availability_code']) ? $offer['availability_code'] : '' }}</td>
                                        <td>{{ isset($offer['price']) ? $offer['price'] : '' }}</td>
                                    </tr>

                                @endforeach

                            @endif

                            </tbody>
                        </table>

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