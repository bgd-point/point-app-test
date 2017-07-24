@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-shares::app/facility/bumi-shares/_breadcrumb')
        <li><a href="{{ url('facility/bumi-shares/sell') }}">Sell</a></li>
        <li><a href="{{ url('facility/bumi-shares/sell/'.$shares_sell->id) }}">{{ $shares_sell->formulir->form_number }}</a></li>
        <li>Archived</li>
    </ul>

    <h2 class="sub-header">Sell Shares</h2>
    @include('bumi-shares::app.facility.bumi-shares.sell._menu')

    <div class="block full">  
        <div class="form-horizontal form-bordered">

            <div class="form-group">
                <div class="col-md-12">                            
                    <div class="alert alert-danger alert-dismissable">
                        <h1 class="text-center"><strong>archived</strong></h1>
                    </div>
                </div>
            </div>

            <fieldset>
                <div class="form-group">
                    <div class="col-md-12">
                        <legend><i class="fa fa-angle-right"></i> Form</legend>
                    </div>
                </div> 
            </fieldset>
            <div class="form-group">
                <label class="col-md-3 control-label">Form Date</label>
                <div class="col-md-6 content-show">
                    {{ date_format_view($shares_sell_archived->formulir->form_date, true) }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Form Number</label>
                <div class="col-md-6 content-show">
                    {{ $shares_sell_archived->archived }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Broker</label>
                <div class="col-md-6 content-show">
                    {{ $shares_sell_archived->broker->name }} ({{ number_format_quantity($shares_sell_archived->fee) }} %)
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Shares</label>
                <div class="col-md-6 content-show">
                    {{ $shares_sell_archived->shares->name }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Owner</label>
                <div class="col-md-6 content-show">
                    {{ $shares_sell_archived->owner->name }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Group</label>
                <div class="col-md-6 content-show">
                    {{ $shares_sell_archived->ownerGroup->name }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Notes</label>
                <div class="col-md-6 content-show">
                    {{ $shares_sell_archived->notes }}
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label">Sell Quantity</label>
                <div class="col-md-6 content-show">
                    {{ number_format_quantity($shares_sell_archived->quantity) }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Sell Price</label>
                <div class="col-md-6 content-show">
                    {{ \NumberHelper::formatPrice($shares_sell_archived->price) }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Total</label>
                <div class="col-md-6 content-show">
                    {{ \NumberHelper::formatPrice($shares_sell_archived->quantity * $shares_sell_archived->price + ($shares_sell_archived->quantity * $shares_sell_archived->price * $shares_sell_archived->fee / 100)) }}
                </div>
            </div> 
            <fieldset>
                <div class="form-group">
                    <div class="col-md-12">
                        <legend><i class="fa fa-angle-right"></i> Authorized User</legend>
                    </div>
                </div>  
                <div class="form-group">
                    <label class="col-md-3 control-label">Form Creator</label>
                    <div class="col-md-6 content-show">
                        {{ $shares_sell_archived->formulir->createdBy->name }}
                    </div>
                </div>                  
                <div class="form-group">
                    <label class="col-md-3 control-label">Approval To</label>
                    <div class="col-md-6 content-show">
                        {{ $shares_sell_archived->formulir->approvalTo->name }}
                    </div>
                </div> 
            </fieldset>

        </div>
    </div>    
</div>
@stop
