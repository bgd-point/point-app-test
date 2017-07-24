@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        <li><a href="{{ url('facility') }}">Facility</a></li>
        <li>Bumi Deposit</li>
    </ul>

    <h2 class="sub-header">Master</h2>

    <div class="row">
        @if(auth()->user()->may('read.bumi.deposit.bank'))
        <div class="col-md-4 col-lg-3">
            <a class="widget widget-button" href="{{ url('facility/bumi-deposit/bank') }}">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-bank push-bit pull-left"></i>
                    <h4 class="widget-heading"><strong>Bank</strong></h4>
                </div>
            </a>
        </div>
        @endif
        @if(auth()->user()->may('read.bumi.deposit.group'))
        <div class="col-md-4 col-lg-3">
            <a class="widget widget-button" href="{{ url('facility/bumi-deposit/group') }}">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-group push-bit pull-left"></i>
                    <h4 class="widget-heading"><strong>Group</strong></h4>
                </div>
            </a>
        </div>
        @endif
        @if(auth()->user()->may('read.bumi.deposit.owner'))
        <div class="col-md-4 col-lg-3">
            <a class="widget widget-button" href="{{ url('facility/bumi-deposit/owner') }}">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-user push-bit pull-left"></i>
                    <h4 class="widget-heading"><strong>Owner</strong></h4>
                </div>
            </a>
        </div>
        @endif
        @if(auth()->user()->may('read.bumi.deposit.category'))
            <div class="col-md-4 col-lg-3">
                <a class="widget widget-button" href="{{ url('facility/bumi-deposit/category') }}">
                    <div class="widget-content text-right clearfix">
                        <i class="fa fa-4x fa-tag push-bit pull-left"></i>
                        <h4 class="widget-heading"><strong>Category</strong></h4>
                    </div>
                </a>
            </div>
        @endif
    </div>

    <h2 class="sub-header">Transaction</h2>

    <div class="row">
        @if(auth()->user()->may('read.bumi.deposit'))
        <div class="col-md-4 col-lg-3">
            <a class="widget widget-button" href="{{ url('facility/bumi-deposit/deposit') }}">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-briefcase push-bit pull-left"></i>
                    <h4 class="widget-heading"><strong>Deposit</strong></h4>
                </div>
            </a>
        </div>
        @endif
    </div>

    @if(auth()->user()->may('read.bumi.deposit.report'))
    <h2 class="sub-header">Report</h2>

    <div class="row">
        <div class="col-md-4 col-lg-3">
            <a class="widget widget-button" href="{{ url('facility/bumi-deposit/deposit-report') }}">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-file-text-o push-bit pull-left"></i>
                    <h4 class="widget-heading"><strong>Deposit Report</strong></h4>
                </div>
            </a>
        </div>
    </div>
    @endif
</div>
@stop
