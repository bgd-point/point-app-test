@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            <li><a href="{{ url('purchasing') }}">Purchasing</a></li>
            <li>Menu</li>
        </ul>

        <h2 class="sub-header">
            Purchasing
        </h2>

        <div class="row">
            @if(auth()->user()->may('read.point.purchasing.requisition'))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('purchasing/point/purchase-requisition')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-check-square-o push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>purchase requisition</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
            @endif
            @if(auth()->user()->may('read.point.purchasing.cash.advance'))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('purchasing/point/cash-advance')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-check-square-o push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>cash advance</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
            @endif
            @if(auth()->user()->may('read.point.purchasing.order'))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('purchasing/point/purchase-order')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-pencil-square-o push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>purchase order</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
            @endif
            @if(auth()->user()->may('read.point.purchasing.downpayment'))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('purchasing/point/downpayment')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-file-o push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>downpayment</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
            @endif
            @if(auth()->user()->may('read.point.purchasing.goods.received'))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('purchasing/point/goods-received')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-truck push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>goods received</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
            @endif
            @if(auth()->user()->may('read.point.purchasing.invoice'))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('purchasing/point/invoice')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-fax push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>invoice</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
            @endif

            {{--@if(auth()->user()->may('read.point.purchasing.return'))--}}
                {{--<div class="col-md-4 col-lg-3">--}}
                    {{--<a href="{{url('purchasing/point/retur')}}" class="widget widget-button">--}}
                        {{--<div class="widget-content text-right clearfix">--}}
                            {{--<i class="fa fa-4x fa-file push-bit pull-left"></i>--}}
                            {{--<h4 class="widget-heading"><strong>return</strong></h4>--}}
                            {{--<span class="text-muted"></span>--}}
                        {{--</div>--}}
                    {{--</a>--}}
                {{--</div>--}}
            {{--@endif--}}

            @if(auth()->user()->may('read.point.purchasing.payment.order'))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('purchasing/point/payment-order')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-file-text push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>payment order</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
            @endif
        </div>
    </div>
@stop
