@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            <li><a href="{{ url('sales') }}">Sales</a></li>
            <li>Menu</li>
        </ul>

        <h2 class="sub-header">
            Service
        </h2>

        <div class="row">
            @if(auth()->user()->may('read.point.sales.service.invoice'))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('sales/point/service/invoice')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-fax push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>invoice</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
            @endif
            @if(client_has_addon('pro') && auth()->user()->may('read.point.sales.service.invoice') ||
                client_has_addon('premium') && auth()->user()->may('read.point.sales.service.invoice') )
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('sales/point/service/downpayment')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-file-o push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>downpayment</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
            @endif
            @if(auth()->user()->may('read.point.sales.service.payment.collection'))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('sales/point/service/payment-collection')}}" class="widget widget-button">
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
            <div class="col-md-4 col-lg-3">
                <a href="{{url('sales/point/service/report')}}" class="widget widget-button">
                    <div class="widget-content text-right clearfix">
                        <i class="fa fa-4x fa-building push-bit pull-left"></i>
                        <h4 class="widget-heading"><strong>Report</strong></h4>
                        <span class="text-muted"></span>
                    </div>
                </a>
            </div>
        </div>
    </div>
@stop
