@extends('core::app.layout-flat')

@section('content')
    <!-- Error Container -->
    <div id="error-container">
        <div class="row text-center">
            <div class="col-md-6 col-md-offset-3">
                <h1 class="text-light animation-fadeInQuick"><strong>404 Not Found</strong></h1>
                <h2 class="text-muted animation-fadeInQuickInv"><em>This page is not available, back to <a
                                href="//{{config('point.client.slug')}}.app.point.red">Home</a> </em></h2>
            </div>
        </div>
    </div>
    <!-- END Error Container -->
@stop
