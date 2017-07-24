@extends('core::app.layout-flat')

@section('content')
    <!-- Error Container -->
    <div id="error-container">
        <div class="row text-center">
            <div class="col-md-6 col-md-offset-3">
                <h1 class="text-light animation-fadeInQuick"><strong>Exceptions</strong></h1>
                <h2 class="text-muted animation-fadeInQuickInv"><em>{{ $messages }}</em></h2>
            </div>
            <div class="col-md-4 col-md-offset-4">
                <a href="{{url('/')}}" class="btn btn-effect-ripple btn-default"><i class="fa fa-arrow-left"></i> Go back</a>
            </div>
        </div>
    </div>
    <!-- END Error Container -->
@stop
