@extends('core::app.layout') 

@section('content')
<div id="page-content">

    <ul class="breadcrumb breadcrumb-top">
        <li><a href="{{ url('sales') }}">Sales</a></li>
        <li>Point of Sales</li>
    </ul>
    @if(auth()->user()->may('read.point.sales.pos.pricing'))
        <h2 class="sub-header">Master</h2>

        <div class="row">
            <div class="col-md-4 col-lg-3">
                <a href="{{url('sales/point/pos/pricing')}}" class="widget widget-button">
                    <div class="widget-content text-right clearfix">
                        <i class="fa fa-4x fa-file-powerpoint-o push-bit pull-left"></i>
                        <h4 class="widget-heading"><strong>Pricing</strong></h4>
                        <span class="text-muted">Point Of Sales</span>
                    </div>
                </a>
            </div>
        </div>
    @endif

    @if(auth()->user()->may('read.point.sales.pos'))
        <h2 class="sub-header">Transaction</h2>

        <div class="row">
            <div class="col-md-4 col-lg-3">
                <a href="{{url('sales/point/pos')}}" class="widget widget-button">
                    <div class="widget-content text-right clearfix">
                        <i class="fa fa-4x fa-print push-bit pull-left"></i>
                        <h4 class="widget-heading"><strong>Point of Sales</strong></h4>
                        <span class="text-muted">Point Of Sales</span>
                    </div>
                </a>
            </div>
        </div>
    @endif

    @if(auth()->user()->may('read.point.sales.pos.daily.report') || auth()->user()->may('read.point.sales.pos.report'))
        <h2 class="sub-header">Report</h2>

        <div class="row">
            @if(auth()->user()->may('read.point.sales.pos.daily.report'))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('sales/point/pos/daily-sales')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-file-text-o push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>Daily Sales</strong></h4>
                            <span class="text-muted">Point Of Sales</span>
                        </div>
                    </a>
                </div>
            @endif
            @if(auth()->user()->may('read.point.sales.pos.report'))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('sales/point/pos/sales-report')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-file-text push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>Sales Report</strong></h4>
                            <span class="text-muted">Point Of Sales</span>
                        </div>
                    </a>
                </div>
            @endif
        </div>
    @endif
</div>
@stop

