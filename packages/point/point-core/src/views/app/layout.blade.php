<!DOCTYPE html>
<!--[if IE 9]>         <html class="no-js lt-ie10"> <![endif]-->
<!--[if gt IE 9]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">

        <title>{{strtoupper(config('point.client.name'))}} - {{env('SOFTWARE_NAME')}}</title>

        <meta name="description" content="{{config('point.client.name')}} - {{env('SOFTWARE_NAME')}}">
        <meta name="author" content="point.">
        <meta name="robots" content="noindex, nofollow">
        <meta name="csrf-token" content="<?= csrf_token() ?>">

        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1.0">

        <!-- Icons -->
        <!-- The following icons can be replaced with your own, they are used by desktop and mobile browsers -->
        <link rel="shortcut icon" href="{{asset('core/themes/appui-backend/img/favicon.png')}}">
        <link rel="apple-touch-icon" href="{{asset('core/themes/appui-backend/img/icon57.png')}}" sizes="57x57">
        <link rel="apple-touch-icon" href="{{asset('core/themes/appui-backend/img/icon72.png')}}" sizes="72x72">
        <link rel="apple-touch-icon" href="{{asset('core/themes/appui-backend/img/icon76.png')}}" sizes="76x76">
        <link rel="apple-touch-icon" href="{{asset('core/themes/appui-backend/img/icon114.png')}}" sizes="114x114">
        <link rel="apple-touch-icon" href="{{asset('core/themes/appui-backend/img/icon120.png')}}" sizes="120x120">
        <link rel="apple-touch-icon" href="{{asset('core/themes/appui-backend/img/icon144.png')}}" sizes="144x144">
        <link rel="apple-touch-icon" href="{{asset('core/themes/appui-backend/img/icon152.png')}}" sizes="152x152">
        <link rel="apple-touch-icon" href="{{asset('core/themes/appui-backend/img/icon180.png')}}" sizes="180x180">
        <!-- END Icons -->

        <!-- Stylesheets -->
        <!-- Bootstrap is included in its original form, unaltered -->
        <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/bootstrap.min.css')}}">

        <!-- Related styles of various icon packs and plugins -->
        <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/plugins.css')}}">

        <!-- The main stylesheet of this template. All Bootstrap overwrites are defined in here -->
        <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/main.css')}}">

        <!-- Include a specific file here from css/themes/ folder to alter the default theme of the template -->
        @if(isset($_COOKIE['optionThemeColor']))
        <link rel="stylesheet" href="{{ $_COOKIE['optionThemeColor'] }}">
        @else
        <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/themes/amethyst.css')}}">
        @endif

        <!-- <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/themes/amethyst.css')}}"> -->
        <!-- <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/themes/classy.css')}}"> -->
        <!-- <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/themes/passion.css')}}"> -->
        <!-- <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/themes/flat.css')}}"> -->
        <!-- <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/themes/social.css')}}"> -->
        <!-- <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/themes/creme.css')}}"> -->

        <!-- The themes stylesheet of this template (for using specific theme color in individual elements - must included last) -->
        <link id="theme-color" rel="stylesheet" href="{{asset('core/themes/appui-backend/css/themes.css')}}">
        <!-- END Stylesheets -->

        <link rel="stylesheet" href="{{elixir('core/assets/css/all.css')}}">

        <link rel="stylesheet" href="{{asset('core/plugins/gritter/css/jquery.gritter.css')}}"/>
        <link rel="stylesheet" href="{{asset('core/plugins/introjs/introjs.css')}}"/>

        @yield('style')

        <style>
        * {margin: 0; padding: 0;}

        .tree ul {
            padding-top: 20px; position: relative;
            
            transition: all 0.5s;
            -webkit-transition: all 0.5s;
            -moz-transition: all 0.5s;
        }

        .tree li {
            float: left; text-align: center;
            list-style-type: none;
            position: relative;
            padding: 20px 5px 0 5px;
            
            transition: all 0.5s;
            -webkit-transition: all 0.5s;
            -moz-transition: all 0.5s;
        }

        /*We will use ::before and ::after to draw the connectors*/

        .tree li::before, .tree li::after{
            content: '';
            position: absolute; top: 0; right: 50%;
            border-top: 1px solid #ccc;
            width: 50%; height: 20px;
        }
        .tree li::after{
            right: auto; left: 50%;
            border-left: 1px solid #ccc;
        }

        /*We need to remove left-right connectors from elements without 
        any siblings*/
        .tree li:only-child::after, .tree li:only-child::before {
            display: none;
        }

        /*Remove space from the top of single children*/
        .tree li:only-child{ padding-top: 0;}

        /*Remove left connector from first child and 
        right connector from last child*/
        .tree li:first-child::before, .tree li:last-child::after{
            border: 0 none;
        }
        /*Adding back the vertical connector to the last nodes*/
        .tree li:last-child::before{
            border-right: 1px solid #ccc;
            border-radius: 0 5px 0 0;
            -webkit-border-radius: 0 5px 0 0;
            -moz-border-radius: 0 5px 0 0;
        }
        .tree li:first-child::after{
            border-radius: 5px 0 0 0;
            -webkit-border-radius: 5px 0 0 0;
            -moz-border-radius: 5px 0 0 0;
        }

        /*Time to add downward connectors from parents*/
        .tree ul ul::before{
            content: '';
            position: absolute; top: 0; left: 50%;
            border-left: 1px solid #ccc;
            width: 0; height: 20px;
        }

        .tree li a{
            border: 1px solid #ccc;
            padding: 5px 10px;
            text-decoration: none;
            color: #666;
            font-family: arial, verdana, tahoma;
            font-size: 11px;
            display: inline-block;
            
            border-radius: 5px;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            
            transition: all 0.5s;
            -webkit-transition: all 0.5s;
            -moz-transition: all 0.5s;
        }

        /*Time for some hover effects*/
        /*We will apply the hover effect the the lineage of the element also*/
        .tree li a:hover, .tree li a:hover+ul li a {
            background: #c8e4f8; color: #000; border: 1px solid #94a0b4;
        }
        /*Connector styles on hover*/
        .tree li a:hover+ul li::after, 
        .tree li a:hover+ul li::before, 
        .tree li a:hover+ul::before, 
        .tree li a:hover+ul ul::before{
            border-color:  #94a0b4;
        }
        </style>

        <style>
            * {
                text-transform: uppercase !important;
            }
        </style>
        @if(\Point\Core\Models\Setting::mouseSelectAllowed() == "false")
        <style>
            body {
                -webkit-touch-callout: none;
                -webkit-user-select: none;
                -khtml-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
            }
        </style>
        @endif

        <!-- Modernizr (browser feature detection library) -->
        <script src="{{asset('core/themes/appui-backend/js/vendor/modernizr-2.8.3.min.js')}}"></script>
        <script src="{{asset('mntr.js')}}"></script>
    </head>
    <body oncontextmenu="return {{ \Point\Core\Models\Setting::rightClickAllowed() }}">
        <!-- Page Wrapper -->
        <!-- In the PHP version you can set the following options from inc/config file -->
        <!--
            Available classes:

            'page-loading'      enables page preloader
        -->
        <div id="page-wrapper">
            <!-- Preloader -->
            <!-- Preloader functionality (initialized in js/app.js) - pageLoading() -->
            <!-- Used only if page preloader enabled from inc/config (PHP version) or the class 'page-loading' is added in #page-wrapper element (HTML version) -->
            <div class="preloader">
                <div class="inner">
                    <!-- Animation spinner for all modern browsers -->
                    <div class="preloader-spinner themed-background hidden-lt-ie10"></div>

                    <!-- Text for IE9 -->
                    <h3 class="text-primary visible-lt-ie10"><strong>Loading..</strong></h3>
                </div>
            </div>
            <!-- END Preloader -->

            <!-- Page Container -->
            <!-- In the PHP version you can set the following options from inc/config file -->
            <!--
                Available #page-container classes:

                'sidebar-light'                                 for a light main sidebar (You can add it along with any other class)

                'sidebar-visible-lg-mini'                       main sidebar condensed - Mini Navigation (> 991px)
                'sidebar-visible-lg-full'                       main sidebar full - Full Navigation (> 991px)

                'sidebar-alt-visible-lg'                        alternative sidebar visible by default (> 991px) (You can add it along with any other class)

                'header-fixed-top'                              has to be added only if the class 'navbar-fixed-top' was added on header.navbar
                'header-fixed-bottom'                           has to be added only if the class 'navbar-fixed-bottom' was added on header.navbar

                'fixed-width'                                   for a fixed width layout (can only be used with a static header/main sidebar layout)

                'enable-cookies'                                enables cookies for remembering active color theme when changed from the sidebar links (You can add it along with any other class)
            -->
            <div id="page-container" class="header-fixed-top sidebar-visible-lg-full enable-cookies">
                <!-- Alternative Sidebar -->
                @include('core::app.include.sidebar-alt')
                <!-- END Alternative Sidebar -->

                <!-- Main Sidebar -->
                @include('core::app.include.sidebar')
                <!-- END Main Sidebar -->

                <!-- Main Container -->
                <div id="main-container">
                    <!-- Header -->
                    @include('core::app.include.header')
                    <!-- END Header -->

                    <!-- Page content -->
                    <!--
                        Available classes when #page-content-sidebar is used:

                        'inner-sidebar-left'      for a left inner sidebar
                        'inner-sidebar-right'     for a right inner sidebar
                    -->
                    @yield('content')

                    <!-- END Page Content -->
                </div>

                <!-- END Main Container -->
            </div>
            <!-- END Page Container -->
        </div>
        <!-- END Page Wrapper -->

        <!-- Sounds -->
        <audio id="notif_audio">
            <source src="{!! asset('sounds/notif1.mp3') !!}" type="audio/mpeg">
        </audio>

        <!-- jQuery, Bootstrap, jQuery plugins and Custom JS code -->
        <script src="{{asset('core/themes/appui-backend/js/vendor/jquery-2.1.4.min.js')}}"></script>
        <script src="{{asset('core/themes/appui-backend/js/vendor/bootstrap.min.js')}}"></script>
        <script src="{{asset('core/themes/appui-backend/js/plugins.js')}}"></script>
        <script src="{{asset('core/themes/appui-backend/js/app.js')}}"></script>

        <script src="//cdn.socket.io/socket.io-1.3.7.js"></script>
        <script src="{{asset('core/plugins/idleTimer/idle-timer.min.js')}}"></script>
        <script src="{{asset('core/plugins/introjs/intro.js')}}"></script>
        
        <script src="{{elixir('core/assets/js/all.js')}}"></script>
        <script>$('.timepicker').timepicker({minuteStep: 1,showSeconds: false,showMeridian: false});</script>
        <script>
            var socket = io.connect( '//'+window.location.hostname+':3000');
        </script>

        <script>
            // repopulate selectize box
            var eventHandler = function(url, id) {
                return function() {
                    $.ajax({
                        url: url,
                        type: 'GET',
                        success: function(data) {
                            var selectize = $("#"+id)[0].selectize;
                            selectize.load(function(callback) {
                                selectize.clearOptions();
                                callback(eval(JSON.stringify(data.lists)));
                            });
                        }, error: function(data) {

                        }
                    });
                };
            };
        </script>

        @yield('scripts')

        <script>
            {{-- Auto Focus in Modal Dialog --}}
            function focusCaret(id){
                var inputField = document.getElementById(id);
                if (inputField != null && inputField.value.length != 0){
                    if (inputField.createTextRange){
                        var FieldRange = inputField.createTextRange();
                        FieldRange.moveStart('character',inputField.value.length);
                        FieldRange.collapse();
                        FieldRange.select();
                    } else if (inputField.selectionStart || inputField.selectionStart == '0') {
                        var elemLen = inputField.value.length;
                        inputField.selectionStart = elemLen;
                        inputField.selectionEnd = elemLen;
                        inputField.focus();
                    }
                } else {
                    inputField.focus();
                }
            }

            {{-- Notification --}}
            @for($i=0;$i<=10;$i++)
                @if(session()->has('gritter_message_'.$i))
                    $.gritter.add({
                        sticky: {{ session()->get('gritter_sticky_'.$i) }},
                        title: '{{ session()->get('gritter_title_'.$i) }}',
                        text: '{{ session()->get('gritter_message_'.$i) }}',
                        time: 3000
                    });
                    <?php session()->forget('gritter_message_'.$i); ?>
                @endif
            @endfor

            {{-- Ajax Callback Notification --}}
            function notification(title, message) {
                $.gritter.add({
                    sticky: false,
                    title: title,
                    text: message
                });
            }

            socket.on('{{config('point.client.channel')}}:GlobalNotification', function( data ) {
                $('#notif_audio')[0].play();
                $.blockUI({
                    message: data.message,
                    timeout: 0
                });

                $('.blockOverlay').attr('title','Click to exit').click($.unblockUI);
            });

            $(document).ready(function(){

                autosize($('.autosize'));

                $(function() {
                    $( document ).idleTimer( 86400000 );
                });

                $(function() {
                    $( document ).on( "idle.idleTimer", function(event, elem, obj){
                        window.location.href="{{url('/auth/logout')}}";
                    });
                });

                @if(\Point\Core\Models\Setting::where('name','=','date-input')->first()->value == 'd-m-y')
                    $('.date').inputmask("99-99-99");
                @elseif(\Point\Core\Models\Setting::where('name','=','date-input')->first()->value == 'd-m-Y')
                    $('.date').inputmask("99-99-9999");
                @elseif(\Point\Core\Models\Setting::where('name','=','date-input')->first()->value == 'd/m/y')
                    $('.date').inputmask("99/99/99");
                @elseif(\Point\Core\Models\Setting::where('name','=','date-input')->first()->value == 'd/m/Y')
                    $('.date').inputmask("99/99/9999");
                @endif
            });

            $(function(){ UiTables.init(); });
        </script>
    </body>
</html>
