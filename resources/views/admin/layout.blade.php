<!DOCTYPE html>
<html lang="ru-ru">
<head>
    <meta charset="utf-8">

    <title>Таларии - Панель управления | @yield('title')</title>
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Basic Styles -->
{!! Html::style('/admin/css/bootstrap.min.css') !!}
{!! Html::style('/admin/css/font-awesome.min.css') !!}

<!-- SmartAdmin Styles : Caution! DO NOT change the order -->
{!! Html::style('/admin/css/smartadmin-production-plugins.min.css') !!}
{!! Html::style('/admin/css/smartadmin-production.min.css') !!}
{!! Html::style('/admin/css/smartadmin-skins.min.css') !!}

{!! Html::style('/admin/js/plugin/daterangepicker/daterangepicker.css') !!}



<!-- SmartAdmin RTL Support -->
{!! Html::style('/admin/css/smartadmin-rtl.min.css') !!}

<!-- We recommend you use "your_style.css" to override SmartAdmin
         specific styles this will also ensure you retrain your customization with each SmartAdmin update.
    <link rel="stylesheet" type="text/css" media="screen" href="css/your_style.css"> -->

    <!-- Demo purpose only: goes with demo.js, you can delete this css when designing your own WebApp -->

{!! Html::style('/admin/css/demo.min.css') !!}

{!! Html::style('/admin/js/plugin/sweetalert/sweetalert.css') !!}

{!! Html::style('/admin/js/plugin/jquery-treeview-master/jquery.treeview.css') !!}

@yield('css')

<!-- FAVICONS -->
    <link rel="shortcut icon" href="/admin/img/favicon/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/admin/img/favicon/favicon.ico" type="image/x-icon">

    <!-- GOOGLE FONT -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,300,400,700">

    <!-- Specifying a Webpage Icon for Web Clip
         Ref: https://developer.apple.com/library/ios/documentation/AppleApplications/Reference/SafariWebContent/ConfiguringWebApplications/ConfiguringWebApplications.html -->
    <link rel="apple-touch-icon" href="/admin/img/splash/sptouch-icon-iphone.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/admin/img/splash/touch-icon-ipad.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/admin/img/splash/touch-icon-iphone-retina.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/admin/img/splash/touch-icon-ipad-retina.png">


    <script type="text/javascript">
        var SITE_URL = "{{ url('/') }}";
    </script>
</head>

<!--

TABLE OF CONTENTS.

Use search to find needed section.

===================================================================

|  01. #CSS Links                |  all CSS links and file paths  |
|  02. #FAVICONS                 |  Favicon links and file paths  |
|  03. #GOOGLE FONT              |  Google font link              |
|  04. #APP SCREEN / ICONS       |  app icons, screen backdrops   |
|  05. #BODY                     |  body tag                      |
|  06. #HEADER                   |  header tag                    |
|  07. #PROJECTS                 |  project lists                 |
|  08. #TOGGLE LAYOUT BUTTONS    |  layout buttons and actions    |
|  09. #MOBILE                   |  mobile view dropdown          |
|  10. #SEARCH                   |  search field                  |
|  11. #NAVIGATION               |  left panel & navigation       |
|  12. #MAIN PANEL               |  main panel                    |
|  13. #MAIN CONTENT             |  content holder                |
|  14. #PAGE FOOTER              |  page footer                   |
|  15. #SHORTCUT AREA            |  dropdown shortcuts area       |
|  16. #PLUGINS                  |  all scripts and plugins       |

===================================================================

-->

<!-- #BODY -->
<!-- Possible Classes

    * 'smart-style-{SKIN#}'
    * 'smart-rtl'         - Switch theme mode to RTL
    * 'menu-on-top'       - Switch to top navigation (no DOM change required)
    * 'no-menu'			  - Hides the menu completely
    * 'hidden-menu'       - Hides the main menu but still accessable by hovering over left edge
    * 'fixed-header'      - Fixes the header
    * 'fixed-navigation'  - Fixes the main menu
    * 'fixed-ribbon'      - Fixes breadcrumb
    * 'fixed-page-footer' - Fixes footer
    * 'container'         - boxed layout mode (non-responsive: will not work with fixed-navigation & fixed-ribbon)
-->
<body class="smart-style-0 fixed-header">

<!-- #HEADER -->
<header id="header">
    <div id="logo-group">

        <!-- PLACE YOUR LOGO HERE -->
        <span id="logo"> <img src="/images/logo.png" alt="Trivago Control Panel"> </span>
        <!-- END LOGO PLACEHOLDER -->
    </div>

    <!-- #TOGGLE LAYOUT BUTTONS -->
    <!-- pulled right: nav area -->
    <div class="pull-right">

        <!-- collapse menu button -->
        <div id="hide-menu" class="btn-header pull-right">
            <span> <a href="javascript:void(0);" data-action="toggleMenu" title="Collapse Menu"><i
                            class="fa fa-reorder"></i></a> </span>
        </div>
        <!-- end collapse menu -->

        <!-- logout button -->
        <div id="logout" class="btn-header transparent pull-right">
            <span> <a href="/cp/logout" title="Выйти" data-action="userLogout"
                      data-logout-msg="You can improve your security further after logging out by closing this opened browser"><i
                            class="fa fa-sign-out"></i></a> </span>
        </div>
        <!-- end logout button -->

        <!-- fullscreen button -->
        <div id="fullscreen" class="btn-header transparent pull-right">
            <span> <a href="javascript:void(0);" data-action="launchFullscreen" title="Full Screen"><i
                            class="fa fa-arrows-alt"></i></a> </span>
        </div>
        <!-- end fullscreen button -->

    </div>
    <!-- end pulled right: nav area -->

</header>
<!-- END HEADER -->

<!-- #NAVIGATION -->
<!-- Left panel : Navigation area -->
<!-- Note: This width of the aside area can be adjusted through LESS/SASS variables -->
<aside id="left-panel">

    <!-- User info -->
    <div class="login-info">
				<span> <!-- User image size is adjusted inside CSS, it should stay as is -->

					<a href="javascript:void(0);" id="show-shortcut" data-action="toggleShortcut">
						<img src="/admin/img/avatars/sunny.png" alt="me" class="online"/>
						<span>
							{{$user->name}}
                            <small>({{$user->email}})</small>
						</span>
						<i class="fa fa-angle-down"></i>
					</a>

				</span>
    </div>
    <!-- end user info -->

    <!-- NAVIGATION : This navigation is also responsive

    To make this navigation dynamic please make sure to link the node
    (the reference to the nav > ul) after page load. Or the navigation
    will not initialize.
    -->
    <nav>
        <!--
        NOTE: Notice the gaps after each icon usage <i></i>..
        Please note that these links work a bit different than
        traditional href="" links. See documentation for details.
        -->

        <ul>

            <li {!! Request::is('cp') ? ' class="active"' : '' !!}>
                <a href="{{URL::route('admin.index')}}"><i class="fa fa-fw fa-home"></i> Dashboard</a>
            </li>

            @if($user->hasAccess('admin.orders.list'))
                <li {!! Request::is('cp/orders*') ? ' class="active"' : '' !!}>
                    <a href="{{URL::route('admin.orders.list')}}"><i class="fa fa-fw fa-bell"></i> Заказы</a>
                </li>
            @endif

            @if($user->hasAccess('admin.order_messages.list'))
                <li {!! Request::is('cp/order-messages*') ? ' class="active"' : '' !!}>
                    <a href="{{URL::route('admin.order_messages.list')}}"><i class="fa fa-fw fa-envelope"></i>
                        Комментарии к заказу</a>
                </li>
            @endif

            <li>
                <a href="#">
                    <i class="fa fa-fw fa-ticket"></i> <span class="menu-item-parent">ЖД</span>
                </a>

                <ul class="treeview-menu">

                    @if($user->hasAccess('admin.trains.list'))
                        <li {!! Request::is('cp/railway/trains') && !Request::is('cp/orders-railway/trains-car*') ? ' class="active"' : '' !!}>
                            <a href="{{URL::route('admin.trains.list')}}"><i class="fa fa-list"></i> Поезда</a></li>
                    @endif

                    @if($user->hasAccess('admin.trainscar.list'))
                        <li {!! Request::is('cp/railway/trains-car*') ? ' class="active"' : '' !!}>
                            <a href="{{URL::route('admin.trainscar.list')}}"><i class="fa fa-list"></i> Тип вагонов</a>
                        </li>
                    @endif

                    @if($user->hasAccess('admin.stations.list'))
                        <li {!! Request::is('cp/railway/stations*') ? ' class="active"' : '' !!}>
                            <a href="{{URL::route('admin.stations.list')}}"><i class="fa fa-list"></i> Станции</a>
                        </li>
                    @endif
                </ul>
            </li>

            <li>
                <a href="#">
                    <i class="fa fa-fw fa-ticket"></i> <span class="menu-item-parent">Отели</span>
                </a>

                <ul class="treeview-menu">

                    @if($user->hasAccess('admin.hotel.list'))
                        <li {!! Request::is('cp/hotel/hotel*')? ' class="active"' : '' !!}>
                            <a href="{{URL::route('admin.hotel.list')}}"><i class="fa fa-list"></i> Список отелей</a>
                        </li>
                    @endif

                    @if($user->hasAccess('admin.hotels_attributes.list'))
                        <li {!! Request::is('cp/hotel/attributes*') ? ' class="active"' : '' !!}>
                            <a href="{{URL::route('admin.hotels_attributes.list')}}"><i class="fa fa-list"></i> Атрибуты</a>
                        </li>
                    @endif

                    @if($user->hasAccess('admin.hotels_regions.list'))
                        <li {!! Request::is('cp/hotel/regions*') ? ' class="active"' : '' !!}>
                            <a href="{{URL::route('admin.hotels_regions.list')}}"><i class="fa fa-list"></i> Регионы</a>
                        </li>
                    @endif

                    @if($user->hasAccess('admin.orders_hotels.list'))
                        <li {!! Request::is('cp/hotel/orders*') ? ' class="active"' : '' !!}>
                            <a href="{{URL::route('admin.orders_hotels.list')}}"><i class="fa fa-list"></i> Заказы</a>
                        </li>
                    @endif

                </ul>
            </li>

            @if($user->hasAccess('admin.pages.list'))
                <li class="">
                    <a href="#">
                        <i class="fa fa-fw fa-folder"></i> <span class="menu-item-parent">Управление контентом</span>
                    </a>

                    <ul class="treeview-menu">
                        <li {!! Request::is('cp/pages/menu*') ? ' class="active"' : '' !!}>
                            <a href="{{ URL::route('admin.menu.list') }}">
                                <i class="fa fa-fw fa-list"></i> <span class="menu-item-parent">Меню</span>
                            </a>
                        </li>
                        <li {!! Request::is('cp/pages') ? ' class="active"' : '' !!}><a
                                    href="{{ URL::route('admin.pages.list') }}"><i class="fa fa-list"></i> Страницы и
                                разделы</a></li>
                        <li {!! Request::is('cp/pages/offices*') ? ' class="active"' : '' !!}><a
                                    href="{{ URL::route('admin.offices.list') }}"><i class="fa fa-list"></i>Офисы</a>
                        </li>
                    </ul>
                </li>
            @endif

            @if($user->hasAccess('admin.tmanager.list'))
                <li class="">
                    <a href="#">
                        <i class="fa fa-fw fa-language"></i> <span class="menu-item-parent">Языки и локализация</span>
                    </a>

                    <ul class="treeview-menu">
                        <li {!! Request::is('cp/languages*') ? ' class="active"' : '' !!}><a
                                    href="{{ URL::route('admin.languages.list') }}"><i class="fa fa-list"></i> Языки</a>
                        </li>
                        <li {!! Request::is('cp/tmanager*') ? ' class="active"' : '' !!}><a
                                    href="{{ URL::route('admin.tmanager.list') }}"><i class="fa fa-list"></i> Менеджер
                                переводов</a></li>
                    </ul>
                </li>
            @endif

            @if($user->hasAccess('admin.portalusers.list'))

                <li class="">
                    <a href="#">
                        <i class="fa fa-fw fa-users"></i> <span class="menu-item-parent">Пользователи портала</span>
                    </a>

                    <ul class="treeview-menu">
                        <li {!! Request::is('cp/portal-users*') ? ' class="active"' : '' !!}><a
                                    href="{{ URL::route('admin.portalusers.list') }}"><i class="fa fa-list"></i> Список
                                пользователей</a></li>
                        <li {!! Request::is('cp/portal-users-role*') ? ' class="active"' : '' !!}><a
                                    href="{{ URL::route('admin.portal_users_role.list') }}"><i class="fa fa-list"></i>
                                Список ролей</a></li>
                    </ul>
                </li>

            @endif

            @if($user->hasAccess('admin.users.list'))
                <li class="">
                    <a href="#">
                        <i class="fa fa-fw fa-users"></i> <span class="menu-item-parent">Пользователи админки</span>
                    </a>

                    <ul class="treeview-menu">
                        <li {!! Request::is('cp/user*') ? ' class="active"' : '' !!}><a
                                    href="{{ URL::route('admin.users.list') }}"><i class="fa fa-list"></i> Список
                                пользователей</a></li>
                        <li {!! Request::is('cp/role*') ? ' class="active"' : '' !!}><a
                                    href="{{ URL::route('admin.role.list') }}"><i class="fa fa-list"></i> Список
                                ролей</a></li>
                    </ul>
                </li>
            @endif

            @if($user->hasAccess('admin.logs.list'))
                <li {!! Request::is('cp/logs*') ? ' class="active"' : '' !!}>
                    <a href="{{URL::route('admin.logs.list')}}"><i class="fa fa-fw fa-bar-chart-o"></i> Логи</a>
                </li>
            @endif

            @if($user->hasAccess('admin.settings.list'))
                <li {!! Request::is('cp/settings') ? ' class="active"' : '' !!}>
                    <a href="{{URL::route('admin.settings.list')}}"><i class="fa fa-fw fa-gears"></i>
                        <span class="menu-item-parent">Настройки приложения</span>
                    </a>
                </li>
            @endif

        </ul>
    </nav>

    <span class="minifyme" data-action="minifyMenu"> <i class="fa fa-arrow-circle-left hit"></i> </span>

</aside>
<!-- END NAVIGATION -->

<!-- #MAIN PANEL -->
<div id="main" role="main">

    <!-- RIBBON -->
    <div id="ribbon">

        <!-- breadcrumb -->
        <ol class="breadcrumb">
            <li>Панель управления</li>
            <li>@yield('title')</li>
        </ol>
        <!-- end breadcrumb -->

        <!-- You can also add more buttons to the
        ribbon for further usability

        Example below:

        <span class="ribbon-button-alignment pull-right" style="margin-right:25px">
            <a href="#" id="search" class="btn btn-ribbon hidden-xs" data-title="search"><i class="fa fa-grid"></i> Change Grid</a>
            <span id="add" class="btn btn-ribbon hidden-xs" data-title="add"><i class="fa fa-plus"></i> Add</span>
            <button id="search" class="btn btn-ribbon" data-title="search"><i class="fa fa-search"></i> <span class="hidden-mobile">Search</span></button>
        </span> -->

    </div>
    <!-- END RIBBON -->

    <!-- #MAIN CONTENT -->

    <div id="content">
        <!-- col -->
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                @yield('content')
            </div>
        </div>
        <!-- end col -->

        <!-- end row -->
    </div>
    <!-- END #MAIN CONTENT -->

</div>
<!-- END #MAIN PANEL -->

<!-- #PAGE FOOTER -->
<div class="page-footer">
{{--<div class="row">
    <div class="col-xs-12 col-sm-6">
        <span class="txt-color-white">SmartAdmin 1.9.x <span class="hidden-xs"> - Web Application Framework</span> © 2017-2019</span>
    </div>
</div>--}}
<!-- end row -->
</div>
<!-- END FOOTER -->


<!-- PACE LOADER - turn this on if you want ajax loading to show (caution: uses lots of memory on iDevices)-->
<script data-pace-options='{ "restartOnRequestAfter": true }' src="/admin/js/plugin/pace/pace.min.js"></script>

<!-- Link to Google CDN's jQuery + jQueryUI; fall back to local -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script>
    if (!window.jQuery) {
        document.write('<script src="/admin/js/libs/jquery-3.2.1.min.js"><\/script>');
    }
</script>

<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script>
    if (!window.jQuery.ui) {
        document.write('<script src="/admin/js/libs/jquery-ui.min.js"><\/script>');
    }
</script>

<!-- IMPORTANT: APP CONFIG -->
{!! Html::script('/admin/js/app.config.js') !!}



<!-- JS TOUCH : include this plugin for mobile drag / drop touch events-->
{!! Html::script('/admin/js/plugin/jquery-touch/jquery.ui.touch-punch.min.js') !!}


<!-- BOOTSTRAP JS -->
{!! Html::script('/admin/js/bootstrap/bootstrap.min.js') !!}

<!-- CUSTOM NOTIFICATION -->
{!! Html::script('/admin/js/notification/SmartNotification.min.js') !!}

<!-- JARVIS WIDGETS -->
{!! Html::script('/admin/js/smartwidgets/jarvis.widget.min.js') !!}


<!-- EASY PIE CHARTS -->
{!! Html::script('/admin/js/plugin/easy-pie-chart/jquery.easy-pie-chart.min.js') !!}


<!-- SPARKLINES -->
{!! Html::script('/admin/js/plugin/sparkline/jquery.sparkline.min.js') !!}


<!-- JQUERY VALIDATE -->
{!! Html::script('/admin/js/plugin/jquery-validate/jquery.validate.min.js') !!}


<!-- JQUERY MASKED INPUT -->
{!! Html::script('/admin/js/plugin/masked-input/jquery.maskedinput.min.js') !!}

<!-- JQUERY SELECT2 INPUT -->
{!! Html::script('/admin/js/plugin/select2/select2.min.js') !!}


<!-- JQUERY UI + Bootstrap Slider -->
{!! Html::script('/admin/js/plugin/bootstrap-slider/bootstrap-slider.min.js') !!}


<!-- browser msie issue fix -->
{!! Html::script('/admin/js/plugin/msie-fix/jquery.mb.browser.min.js') !!}


<!-- FastClick: For mobile devices -->
{!! Html::script('/admin/js/plugin/fastclick/fastclick.min.js') !!}

<!--[if IE 8]>
<h1>Your browser is out of date, please update your browser by going to www.microsoft.com/download</h1>

<![endif]-->

<!-- MAIN APP JS FILE -->
{!! Html::script('/admin/js/app.min.js') !!}

<!-- ENHANCEMENT PLUGINS : NOT A REQUIREMENT -->
<!-- Voice command : plugin -->

{!! Html::script('/admin/js/speech/voicecommand.min.js') !!}

<!-- SmartChat UI : plugin -->

{!! Html::script('/admin/js/smart-chat-ui/smart.chat.ui.min.js') !!}
{!! Html::script('/admin/js/smart-chat-ui/smart.chat.manager.min.js') !!}

<!-- PAGE RELATED PLUGIN(S) -->

{!! Html::script('/admin/js/plugin/datatables/jquery.dataTables.min.js') !!}

{!! Html::script('/admin/js/plugin/datatables/dataTables.colVis.min.js') !!}

{!! Html::script('/admin/js/plugin/datatables/dataTables.tableTools.min.js') !!}

{!! Html::script('/admin/js/plugin/datatables/dataTables.bootstrap.min.js') !!}

{!! Html::script('/admin/js/plugin/datatable-responsive/datatables.responsive.min.js') !!}

{!! Html::script('/admin/js/plugin/daterangepicker/moment.min.js') !!}

{!! Html::script('/admin/js/plugin/daterangepicker/daterangepicker.js') !!}

{!! Html::script('/admin/js/plugin/sweetalert/sweetalert-dev.js') !!}

{!! Html::script('/admin/js/plugin/jquery-treeview-master/jquery.treeview.js') !!}

{!! Html::script('/admin/js/plugin/uploadpreview/jquery.uploadPreview.min.js') !!}

{!! Html::script('/admin/js/plugin/uploadpreview/jquery.uploadPreview.min.js') !!}

{!! Html::script('/admin/js/plugin/uploadpreview/jquery.uploadPreview.min.js') !!}


@yield('js')

</body>

</html>