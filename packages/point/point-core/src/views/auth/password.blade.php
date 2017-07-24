@extends('core::app.layout-flat')

@section('scripts')
<script src="{{asset('core/themes/appui-backend/js/pages/readyReminder.js')}}"></script>
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
            <h2>Password Reminder</h2>
        </div>
        <!-- END Reminder Title -->
        @if(\Point\Core\Models\Setting::userChangePasswordAllowed() == "true")
        <!-- Reminder Form -->
        <form id="form-reminder" action="/password/email" method="post" class="form-horizontal">
            {!! csrf_field() !!}
            <div class="form-group">
                <div class="col-xs-12">
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email.." value="{{ old('email') }}" autofocus>
                </div>
            </div>
            <div class="form-group form-actions">
                <div class="col-xs-12 text-right">
                    <button type="submit" class="btn btn-effect-ripple btn-sm btn-primary"><i class="fa fa-check"></i> Remind Password</button>
                </div>
            </div>

            @if(Session::has('status'))
                <div class="alert alert-info">Please, check your email for the next instruction</div>
            @endif

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
        @endif
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
