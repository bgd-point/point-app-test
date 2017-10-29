@extends('core::app.layout-flat')

@section('scripts')
<script src="{{asset('themes/appui-backend/js/pages/readyLogin.js')}}"></script>
<script>$(function(){ ReadyLogin.init(); });</script>
@stop

@section('content')
<div id="login-container">
    <!-- Login Header -->
    <h1 class="h2 text-light text-center push-top-bottom animation-slideDown">
        <strong>{{strtoupper(config('point.client.name'))}}</strong>
    </h1>
    <!-- END Login Header -->

    <!-- Login Block -->
    <div class="block animation-fadeInQuickInv">
        <!-- Login Title -->
        <div class="block-title">
            <div class="block-options pull-right">
                <a href="{{ url('/password/email') }}" class="btn btn-effect-ripple btn-primary" data-toggle="tooltip" data-placement="left" title="Forgot your password?"><i class="fa fa-exclamation-circle"></i></a>
            </div>
            <h2>Please Login ...</h2>
        </div>
        <!-- END Login Title -->

        <!-- Login Form -->
        <form id="form-login" action="/auth/login" method="post" class="form-horizontal">
            {!! csrf_field() !!}
            <div class="form-group">
                <div class="col-xs-12">
                    <input type="text" id="name" name="name" class="form-control" placeholder="Your username.." value="{{ old('name') }}" autofocus>
                </div>
            </div>
            <div class="form-group">
                <div class="col-xs-12">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Your password.." {{ old('name') != '' ? 'autofocus' : ''}}>
                </div>
            </div>
            <div class="form-group form-actions">
                <div class="col-xs-12 text-right">
                    <button type="submit" class="btn btn-effect-ripple btn-sm btn-primary"><i class="fa fa-check"></i> Submit</button>
                </div>
            </div>

            @if(count($errors) > 0)
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </form>
        <!-- END Login Form -->
    </div>
    <!-- END Login Block -->

    <!-- Footer -->
    <footer class="text-muted text-center animation-pullUp">
        <small><span id="year-copy"></span> &copy; {{env('SOFTWARE_NAME')}}</small>
    </footer>
    <!-- END Footer -->
</div>
@stop
