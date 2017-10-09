@extends('core::app.layout-flat')

@section('content')
<div id="login-container">
    <!-- Login Header -->
    <h1 class="h2 text-light text-center push-top-bottom animation-slideDown">
        <i class="fa fa-cube"></i> <strong>{{config('point.client.name')}}</strong>
    </h1>
    <!-- END Login Header -->

    <!-- Login Block -->
    <div class="block animation-fadeInQuickInv">
        <!-- Login Title -->
        <div class="block-title">
            <h2>Print Approval Status</h2>
        </div>

        <div class="form-horizontal">
          
            <div class="form-group">
                <div class="col-xs-12 text-center">
                    <h3>{{ $invoice->form_number ? $invoice->form_number : $invoice->archived }}</h3>
                </div>
            </div>
            <div class="form-group">
                <div class="col-xs-12 text-center">
                    @if($invoice->approval_print_status == 1)
                    <button class="btn btn-effect-ripple btn-lg btn-success" onclick="window.close()"><i class="fa fa-file"></i> APPROVED</button>
                    @elseif($invoice->approval_print_status == -1)
                    <button class="btn btn-effect-ripple btn-lg btn-danger" onclick="window.close()"><i class="fa fa-file"></i> REJECTED</button>
                    @elseif($invoice->approval_print_status == 0)
                    <button class="btn btn-effect-ripple btn-lg btn-warning" onclick="window.close()"><i class="fa fa-file"></i> PENDING</button>
                    @endif
                    <br/>
                    click status button to close window
                </div> 
            </div>
            <div class="form-group form-actions"> 
                <div class="col-xs-12 text-right">
                </div> 
            </div> 
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-muted text-center animation-pullUp">
        <small><span id="year-copy"></span> &copy; {{env('SOFTWARE_NAME')}}</small>
    </footer>
    <!-- END Footer -->
</div>
@stop
