@extends('core::app.layout-flat')

@section('scripts')
<script src="{{asset('themes/appui-backend/js/pages/readyReminder.js')}}"></script>
<script>$(function(){ ReadyReminder.init(); });</script>
@stop

@section('content')
<div id="login-container">
    <!-- Reminder Header -->
    <h1 class="h2 text-light text-center push-top-bottom animation-slideDown">
        <strong>{{strtoupper(config('point.client.name'))}}</strong>
    </h1>
    <!-- END Reminder Header -->

    <!-- Reminder Block -->
    <div class="block animation-fadeInQuickInv">
        <!-- Reminder Title -->
        <div class="block-title">
            <div class="block-options pull-right">
                <a href="{{ url('/auth/login') }}" class="btn btn-effect-ripple btn-primary" data-toggle="tooltip" data-placement="left" title="Back to login"><i class="fa fa-user"></i></a>
            </div>
            <h2>Reset Password</h2>
        </div>
        <!-- END Reminder Title -->

        <!-- Reminder Form -->
        <form id="form-reminder" action="/password/reset" method="post" class="form-horizontal">
            {!! csrf_field() !!}
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <div class="col-xs-12">
                    <input type="text" id="email" name="email" class="form-control" placeholder="Enter your email.." value="{{ old('email') }}">
                </div>
            </div>
            <div class="form-group">
                <div class="col-xs-12">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Your password..">
                </div>
            </div>
            <div class="form-group">
                <div class="col-xs-12">
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Your password..">
                </div>
            </div>
            <div class="form-group form-actions">
                <div class="col-xs-12 text-right">
                    <button type="submit" class="btn btn-effect-ripple btn-sm btn-primary"><i class="fa fa-check"></i> Reset My Password</button>
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
        <!-- END Reminder Form -->
    </div>
    <!-- END Reminder Block -->

    <!-- Footer -->
    <footer class="text-muted text-center animation-pullUp">
        <small><span id="year-copy"></span> &copy; {{env('SOFTWARE_NAME')}}</small>
    </footer>
    <!-- END Footer -->
</div>
@stop
