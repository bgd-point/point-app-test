@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            <li><a href="{{ url('accounting') }}">Accounting</a></li>
            <li>Menu</li>
        </ul>

        <h2 class="sub-header">
            Cut Off
        </h2>

        <div class="row">
            
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('accounting/point/cut-off/account')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-calculator push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>account</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
                
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('accounting/point/cut-off/inventory')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-cubes push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>inventory</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>

                <div class="col-md-4 col-lg-3">
                    <a href="{{url('accounting/point/cut-off/receivable')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-credit-card  push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>receivable</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
                    <div class="col-md-4 col-lg-3">
                    <a href="{{url('accounting/point/cut-off/payable')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-credit-card push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>payable</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
           
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('accounting/point/cut-off/fixed-assets')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-cube push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>fixed asset</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
            
            
        </div>
    </div>
@stop
