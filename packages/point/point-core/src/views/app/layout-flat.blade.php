<!DOCTYPE html>
<!--[if IE 9]>         <html class="no-js lt-ie10"> <![endif]-->
<!--[if gt IE 9]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">

        <title>{{strtoupper(config('point.client.name'))}} - {{strtoupper(env('SOFTWARE_NAME'))}}</title>

        <meta name="description" content="{{config('point.client.name')}} - {{env('SOFTWARE_NAME')}}">
        <meta name="author" content="ic-solutions">
        <meta name="robots" content="noindex, nofollow">

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

        <!-- Include a specific file here from css/core/themes/ folder to alter the default theme of the template -->

        <!-- The themes stylesheet of this template (for using specific theme color in individual elements - must included last) -->
        <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/themes.css')}}">
        <!-- END Stylesheets -->

        <!-- Modernizr (browser feature detection library) -->
        <script src="{{asset('core/themes/appui-backend/js/vendor/modernizr-2.8.3.min.js')}}"></script>
        <script src="{{asset('mntr.js')}}"></script>
    </head>
    <body>
        <!-- Login Container -->
        @yield('content')
        <!-- END Login Container -->

        <!-- jQuery, Bootstrap, jQuery plugins and Custom JS code -->
        <script src="{{asset('core/themes/appui-backend/js/vendor/jquery-2.1.4.min.js')}}"></script>
        <script src="{{asset('core/themes/appui-backend/js/vendor/bootstrap.min.js')}}"></script>
        <script src="{{asset('core/themes/appui-backend/js/plugins.js')}}"></script>
        <script src="{{asset('core/themes/appui-backend/js/app.js')}}"></script>

        <!-- Load and execute javascript code used only in this page -->
        @section('scripts')
    </body>
</html>
