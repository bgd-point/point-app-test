@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            <li><a href="{{ url('sales') }}">Sales</a></li>
            <li>Menu</li>
        </ul>

        <h2 class="sub-header">
            Sales
        </h2>

        <div class="row">
            @if(client_has_addon('premium') && auth()->user()->may('read.point.sales.quotation'))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('sales/point/indirect/sales-quotation')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-check-square-o push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>sales quotation</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
            @endif
            @if(client_has_addon('premium') && auth()->user()->may('read.point.sales.order'))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('sales/point/indirect/sales-order')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-list-ol push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>sales order</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
            @endif
            @if(client_has_addon('pro') && auth()->user()->may('read.point.sales.downpayment') ||
                client_has_addon('premium') && auth()->user()->may('read.point.sales.downpayment') )
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('sales/point/indirect/downpayment')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-file-o push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>downpayment</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
            @endif
            @if(client_has_addon('premium') && auth()->user()->may('read.point.sales.delivery.order'))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('sales/point/indirect/delivery-order')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-truck push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>delivery order</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
            @endif
            @if(auth()->user()->may('read.point.sales.invoice'))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('sales/point/indirect/invoice')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-fax push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>invoice</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
            @endif
            {{--@if(client_has_addon('pro') && auth()->user()->may('read.point.sales.return') ||--}}
                {{--client_has_addon('premium') && auth()->user()->may('read.point.sales.downpayment'))--}}
                {{--<div class="col-md-4 col-lg-3">--}}
                    {{--<a href="{{url('sales/point/indirect/retur')}}" class="widget widget-button">--}}
                        {{--<div class="widget-content text-right clearfix">--}}
                            {{--<i class="fa fa-4x fa-file push-bit pull-left"></i>--}}
                            {{--<h4 class="widget-heading"><strong>return</strong></h4>--}}
                            {{--<span class="text-muted"></span>--}}
                        {{--</div>--}}
                    {{--</a>--}}
                {{--</div>--}}
            {{--@endif--}}
            @if(auth()->user()->may('read.point.sales.payment.collection'))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('sales/point/indirect/payment-collection')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-file-text push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>payment collection</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
            @endif
        </div>

        <h2 class="sub-header">
            Report
        </h2>

        <div class="row">
            @if(client_has_addon('premium') && auth()->user()->may('read.point.sales.report'))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('sales/point/indirect/report')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-file-text push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>Report</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
                <!-- <div class="col-md-4 col-lg-3">
                    <a href="{{url('sales/point/indirect/retur-report')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-file-text push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>Retur</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div> -->
            @endif
        </div>
    </div>
@stop
