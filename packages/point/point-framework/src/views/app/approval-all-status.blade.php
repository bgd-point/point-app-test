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
            <h2>Approval Status</h2>
        </div>

        <div class="form-horizontal">
            <div class="form-group">
                <div class="col-xs-12 text-center">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Form Number</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($array_formulir_id as $id)
                                <?php $formulir = Point\Framework\Models\Formulir::find($id); ?>
                                <tr>
                                    <td>
                                        {{ $formulir->form_number }}
                                    </td>
                                    <td>
                                        @include('framework::app.include._approval_status_label', ['approval_status' => $formulir->approval_status])
                                    </td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td colspan="2">
                                        <button class="btn btn-effect-ripple btn-primary" onclick="window.close()">CLOSE</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>    
                    </div>
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
