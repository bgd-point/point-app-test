@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            <li><a href="{{ url('purchasing') }}">Purchasing</a></li>
            <li>Menu</li>
        </ul>

        @if(auth()->user()->may('read.point.purchasing.service.invoice')
         || auth()->user()->may('read.point.purchasing.service.downpayment')
         || auth()->user()->may('read.point.purchasing.service.payment.order'))
        
            <h2 class="sub-header">
                Service
            </h2>

            <div class="row">
                @if(auth()->user()->may('read.point.purchasing.service.invoice'))
                    <div class="col-md-4 col-lg-3">
                        <a href="{{url('purchasing/point/service/invoice')}}" class="widget widget-button">
                            <div class="widget-content text-right clearfix">
                                <i class="fa fa-4x fa-fax push-bit pull-left"></i>
                                <h4 class="widget-heading"><strong>invoice</strong></h4>
                                <span class="text-muted"></span>
                            </div>
                        </a>
                    </div>
                @endif
                @if(auth()->user()->may('read.point.purchasing.service.downpayment'))
                    <div class="col-md-4 col-lg-3">
                        <a href="{{url('purchasing/point/service/downpayment')}}" class="widget widget-button">
                            <div class="widget-content text-right clearfix">
                                <i class="fa fa-4x fa-file-o push-bit pull-left"></i>
                                <h4 class="widget-heading"><strong>downpayment</strong></h4>
                                <span class="text-muted"></span>
                            </div>
                        </a>
                    </div>
                @endif
                @if(auth()->user()->may('read.point.purchasing.service.payment.order'))
                    <div class="col-md-4 col-lg-3">
                        <a href="{{url('purchasing/point/service/payment-order')}}" class="widget widget-button">
                            <div class="widget-content text-right clearfix">
                                <i class="fa fa-4x fa-file-text push-bit pull-left"></i>
                                <h4 class="widget-heading"><strong>payment order</strong></h4>
                                <span class="text-muted"></span>
                            </div>
                        </a>
                    </div>
                @endif
            </div>
        @endif

        @if(auth()->user()->may('read.point.purchasing.service.report'))
            <h2 class="sub-header">
                Report
            </h2>
            <div class="row">
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('purchasing/point/service/report')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-file-text push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>Report</strong></h4>
                            <span class="text-muted">Daily Report</span>
                        </div>
                    </a>
                </div>
            </div>
        @endif
    </div>
@stop
