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

                    {{--@if($user->hasAccess('trains'))
                        <div class="box-header">
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="{{ URL::route('admin.trains.create') }}"
                                       class="btn btn-info btn-sm pull-left"><span class="fa fa-plus"> &nbsp;</span>Добавить
                                        поезд</a>
                                </div>
                            </div>
                        </div>
                    @endif--}}

                    <br>
                    <div class="table-responsive">
                        <table id="itemList" class="table table-striped table-bordered table-hover" width="100%">
                            <thead>
                            <tr>
                                <th >Экспресс-код</th>
                                <th >Станция</th>
                                <th >Станция EN</th>
                                <th >Измененное имя станции</th>
                                <th >Измененное имя станции EN</th>
                                <th >Город</th>
                                <th >Страна</th>
                                <th>Популярность</th>
                                <th > Действия</th>
                            </tr>
                            </thead>
                            <tbody>
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
    <script type="text/javascript">
      $(document).ready(function () {
        var table = $("#itemList").DataTable({
          "sDom": "flrtip",
          "autoWidth": true,
          "oLanguage": {
            "sSearch": '<span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span>'
          },
          'createdRow': function (row, data, dataIndex) {
            $(row).attr('id', 'rowid_' + data['id']);
          },
          processing: true,
          serverSide: true,
          ajax: '{!! URL::route('admin.datatable.stations') !!}',
          columns: [
            {data: 'code', name: 'code'},
            {data: 'nameRu', name: 'nameRu'},
            {data: 'nameEn', name: 'nameEn'},
            {data: 'custom.nameRu', name: 'custom->nameRu'},
            {data: 'custom.nameEn', name: 'custom->nameEn'},
            {data: 'city.nameRu', name: 'city.nameRu'},
            {data: 'country.nameRu', name: 'country.nameRu'},
            {data: 'info.popularity', name: 'info->popularity'},
            {data: "actions", name: 'actions', orderable: false, searchable: false}
          ],
        });
      });
    </script>
@endsection