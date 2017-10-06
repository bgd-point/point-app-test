@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-finance::app.finance.point.cheque._breadcrumb')
        <li><a href="{{ url('finance/point/cheque') }}">Cheque</a></li>
        <li>Pending Cheque</li>
    </ul>
    <h2 class="sub-header">Cheque</h2>
    @include('point-finance::app.finance.point.cheque._menu')
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="form-horizontal form-bordered">
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> CHEQUE DETAIL</legend>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <label class="col-md-2 control-label">Due Date</label>
                    <div class="col-md-10 content-show">
                        {{date_format_view($cheque_detail->due_date)}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Rejected Date</label>
                    <div class="col-md-10 content-show">
                        {{date_format_view($cheque_detail->rejected_at)}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Cheque Number</label>
                    <div class="col-md-10 content-show">
                        {{$cheque_detail->number}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Bank</label>
                    <div class="col-md-10 content-show">
                        {{$cheque_detail->bank}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Amount</label>
                    <div class="col-md-10 content-show">
                        {{number_format_quantity($cheque_detail->amount, 2)}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Notes</label>
                    <div class="col-md-10 content-show">
                        {{ $cheque_detail->notes }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label"></label>
                    <div class="col-md-10">
                        <a href="{{url('finance/point/cheque/clearing?id='.$cheque_detail->id)}}" class="btn btn-effect-ripple btn-primary">Disbursement</a>
                        <a data-toggle="modal" data-target="#modal-cheque-action" class="btn btn-effect-ripple btn-info">Create New</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modal-cheque-action" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><strong>Select Payment</strong></h3>
            </div>
            <div class="modal-body">
                <a onclick="createNew('bank', {{$cheque_detail->id}})" class="btn btn-effect-ripple btn-primary col-sm-12">BANK</a>
                <a onclick="createNew('cash', {{$cheque_detail->id}})" class="btn btn-effect-ripple btn-info col-sm-12">CASH</a>
                <a href="{{url('finance/point/cheque/create-new/'.$cheque_detail->id)}}" class="btn btn-effect-ripple btn-default col-sm-12">CHEQUE / WESEL</a>
                <div class="text-center" id="message" style="margin-top:150px; word-wrap: break-word;">
                    
                </div>
                <div id="preloader" style="display:none; margin-top:125px;" class="text-center col-sm-12">
                    <i class="fa fa-spinner fa-spin" style="font-size:48px;"></i>
                </div>
                
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
</div>
@stop

@section('scripts')
<style type="text/css">
    .modal-dialog{
        position: relative;
        display: table; /* This is important */ 
        overflow-y: auto;    
        overflow-x: auto;
        width: auto;
        width: 300px;   
    }
</style>

<script type="text/javascript">
    function createNew (paymentType, id) {
        var url = '{{url()}}/finance/point/cheque/create-new-cash-bank/';
        $("#preloader").fadeIn();
        $("#message").fadeOut(0);
        $.ajax({
            url: url,
            async: true,
            data: {type: paymentType, id: id},
            success: function(result) {
                console.log(result);
                $("#preloader").fadeOut(0);
                $('#message').fadeIn();
                $('#message').html(result.message);
            },
            error: function (result) {
                console.log(result);  
            }
        })
    }
</script>
@stop