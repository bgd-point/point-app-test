@extends('core::app.layout')

@section('content')
<div id="page-content">
    @if(auth()->user()->may('menu.point.finance.cashier'))
    <ul class="breadcrumb breadcrumb-top">
         <li><a href="{{url('finance')}}">Finance</a></li>
         <li>Choose payment</li>
    </ul>

    <h2 class="sub-header">
        Choose payment
    </h2>

    <div classs="row">
        @if(auth()->user()->may('create.point.finance.cashier.cash') && ($payment_reference->payment_type == 'cash' || $payment_reference->payment_type == 'all'))
            <div class="col-md-4 col-lg-3">
                <a href="{{url('finance/point/cash/'.$payment_reference->payment_flow.'/create/'.$payment_reference->id) }}" class="widget widget-button">
                    <div class="widget-content text-right clearfix">
                        <i class="fa fa-4x fa-usd push-bit pull-left"></i>
                        <h4 class="widget-heading"><strong>Cash</strong></h4>
                        <span class="text-muted"></span>
                    </div>
                </a>
            </div>
        @endif

        @if(auth()->user()->may('create.point.finance.cashier.bank') && ($payment_reference->payment_type == 'bank' || $payment_reference->payment_type == 'all'))
            <div class="col-md-4 col-lg-3">
                <a href="{{url('finance/point/bank/'.$payment_reference->payment_flow.'/create/'.$payment_reference->id)}}" class="widget widget-button">
                    <div class="widget-content text-right clearfix">
                        <i class="fa fa-4x fa-credit-card push-bit pull-left"></i>
                        <h4 class="widget-heading"><strong>Bank</strong></h4>
                        <span class="text-muted"></span>
                    </div>
                </a>
            </div>
        @endif

        @if(auth()->user()->may('create.point.finance.cashier.cheque') && ($payment_reference->payment_type == 'cheque' || $payment_reference->payment_type == 'all'))
            <div class="col-md-4 col-lg-3">
                <a href="{{url('finance/point/cheque/'.$payment_reference->payment_flow.'/create/'.$payment_reference->id)}}" class="widget widget-button">
                    <div class="widget-content text-right clearfix">
                        <i class="fa fa-4x fa-pencil-square-o push-bit pull-left"></i>
                        <h4 class="widget-heading"><strong>CHEQUE/WESEL</strong></h4>
                        <span class="text-muted"></span>
                    </div>
                </a>
            </div>
        @endif
    </div>
    @endif
</div>
@stop
