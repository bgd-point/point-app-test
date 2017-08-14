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
                    <label class="col-md-2 control-label"></label>
                    <div class="col-md-10">
                        @if($cheque_detail->rejected_counter != 3)
                        <a href="{{url('finance/point/cheque/disbursement?id='.$cheque_detail->id)}}" class="btn btn-effect-ripple btn-primary">Disbursement</a>
                        <a href="{{url('finance/point/cheque/reject?id='.$cheque_detail->id)}}" class="btn btn-effect-ripple btn-danger">Reject</a>
                        @else
                        <a href="{{url('finance/point/cheque/create-new/'.$cheque_detail->id)}}" class="btn btn-effect-ripple btn-info">Create New</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop