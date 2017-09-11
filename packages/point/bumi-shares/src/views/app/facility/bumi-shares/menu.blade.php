@extends('core::app.layout') 

@section('content')
<div id="page-content">
    <h2 class="sub-header">Master</h2>

    <div class="row">
        <div class="col-md-4 col-lg-3">
            <a href="{{url('facility/bumi-shares/broker')}}" class="widget widget-button">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-user-secret push-bit pull-left"></i> 
                    <h4 class="widget-heading"><strong>Broker</strong></h4>
                    <span class="text-muted"></span>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="{{url('facility/bumi-shares/owner-group')}}" class="widget widget-button">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-group push-bit pull-left"></i> 
                    <h4 class="widget-heading"><strong>Group</strong></h4>
                   <span class="text-muted"></span>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="{{url('facility/bumi-shares/owner')}}" class="widget widget-button">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-user push-bit pull-left"></i> 
                    <h4 class="widget-heading"><strong>Owner</strong></h4>
                    <span class="text-muted"></span>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="{{url('facility/bumi-shares/shares')}}" class="widget widget-button">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-file-text-o push-bit pull-left"></i>
                    <h4 class="widget-heading"><strong>Shares</strong></h4>
                    <span class="text-muted"></span>
                </div>
            </a>
        </div>
    </div>

    <h2 class="sub-header">Trading</h2>

    <div class="row">
        <div class="col-md-4 col-lg-3">
            <a href="{{url('facility/bumi-shares/buy')}}" class="widget widget-button">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-download push-bit pull-left"></i> 
                    <h4 class="widget-heading"><strong>Buy</strong></h4>
                    <span class="text-muted"></span>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="{{url('facility/bumi-shares/sell')}}" class="widget widget-button">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-upload push-bit pull-left"></i> 
                    <h4 class="widget-heading"><strong>Sell</strong></h4>
                    <span class="text-muted"></span>
                </div>
            </a>
        </div>
    </div>

    <h2 class="sub-header">Report</h2>

    <div class="row">
        <div class="col-md-4 col-lg-3">
            <a href="{{url('facility/bumi-shares/report/stock')}}" class="widget widget-button">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-file-text-o push-bit pull-left"></i> 
                    <h4 class="widget-heading"><strong>Stock</strong></h4>
                    <span class="text-muted"></span>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="{{url('facility/bumi-shares/report/sell')}}" class="widget widget-button">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-file-text-o push-bit pull-left"></i> 
                    <h4 class="widget-heading"><strong>Sell</strong></h4>
                    <span class="text-muted"></span>
                </div>
            </a>
        </div>
        
    </div>
</div>
@stop
