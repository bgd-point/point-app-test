@extends('core::app.layout') 

@section('content')
<div id="page-content">
    <div class="row">
        <div class="col-md-4 col-lg-3">
            <a href="{{url('facility/ksp/loan-simulator')}}" class="widget widget-button">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-calculator push-bit pull-left"></i>
                    <h4 class="widget-heading"><strong>Loan Simulator</strong></h4>
                    <span class="text-muted"></span>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="{{url('facility/ksp/loan-application')}}" class="widget widget-button">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-file-text push-bit pull-left"></i>
                    <h4 class="widget-heading"><strong>Loan Application</strong></h4>
                   <span class="text-muted"></span>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="#" class="widget widget-button">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-file-text-o push-bit pull-left"></i>
                    <h4 class="widget-heading"><strong>Payment Collection</strong></h4>
                    <span class="text-muted" style="color:red"><i class="fa fa-warning"></i> UNDER CONSTRUCTION</span>
                </div>
            </a>
        </div>
    </div>
</div>
@stop
