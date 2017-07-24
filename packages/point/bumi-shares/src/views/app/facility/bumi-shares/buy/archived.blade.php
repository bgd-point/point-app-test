@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-shares::app/facility/bumi-shares/_breadcrumb')
        <li><a href="{{ url('facility/bumi-shares/buy') }}">Buy</a></li>
        <li><a href="{{ url('facility/bumi-shares/buy/'.$shares_buy->id) }}">{{ $shares_buy->formulir->form_number }}</a></li>
        <li>Archived</li>
    </ul>

    <h2 class="sub-header">Buy Shares</h2>
    @include('bumi-shares::app.facility.bumi-shares.buy._menu')

    <div class="block full">  
        <div class="form-horizontal form-bordered">

            @if($buy_archived->archived != null)
            <div class="form-group">
                <div class="col-md-12">                            
                    <div class="alert alert-danger alert-dismissable">
                        <h1 class="text-center"><strong>Archived</strong></h1>
                    </div>
                </div>
            </div>
            @endif

            <fieldset>
                <div class="form-group pull-right">
                    <div class="col-md-12">
                        @include('framework::app.include._approval_status_label', [
                            'approval_status' => $shares_buy->formulir->approval_status,
                            'approval_message' => $shares_buy->formulir->approval_message,
                            'approval_at' => $shares_buy->formulir->approval_at,
                            'approval_to' => $shares_buy->formulir->approvalTo->name,
                        ])
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <div class="form-group">
                    <div class="col-md-12">
                        <legend><i class="fa fa-angle-right"></i> Form</legend>
                    </div>
                </div> 
            </fieldset>
            <div class="form-group">
                <label class="col-md-3 control-label">Form Number</label>
                <div class="col-md-6 content-show">
                    {{ $buy_archived->archived }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Form Date</label>
                <div class="col-md-6 content-show">
                    {{ \DateHelper::formatView($buy_archived->formulir->form_date, true) }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Broker</label>
                <div class="col-md-6 content-show">
                    {{ $buy_archived->broker->name }} ({{ number_format_quantity($buy_archived->fee) }} %)
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Shares</label>
                <div class="col-md-6 content-show">
                    {{ $buy_archived->shares->name }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Owner</label>
                <div class="col-md-6 content-show">
                    {{ $buy_archived->owner->name }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Group</label>
                <div class="col-md-6 content-show">
                    {{ $buy_archived->ownerGroup->name }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Notes</label>
                <div class="col-md-6 content-show">
                    {{ $buy_archived->notes }}
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label">Quantity</label>
                <div class="col-md-6 content-show">
                    {{ number_format_quantity($buy_archived->quantity) }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Price</label>
                <div class="col-md-6 content-show">
                    {{ \NumberHelper::formatPrice($buy_archived->price) }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Total</label>
                <div class="col-md-6 content-show">
                    {{ \NumberHelper::formatPrice($buy_archived->quantity * $buy_archived->price + ($buy_archived->quantity * $buy_archived->price * $buy_archived->fee / 100)) }}
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
                        {{ $buy_archived->formulir->createdBy->name }}
                    </div>
                </div>                  
                <div class="form-group">
                    <label class="col-md-3 control-label">Approval To</label>
                    <div class="col-md-6 content-show">
                        {{ $buy_archived->formulir->approvalTo->name }}
                    </div>
                </div> 
            </fieldset>
        </div>
    </div>    
</div>
@stop 

@section('scripts')
<style>
    tbody.manipulate-row:after {
      content: '';
      display: block;
      height: 100px;
    }
</style>
<script>
var item_table = $('#item-datatable').DataTable({
        bSort: false,
        bPaginate: false,
        bInfo: false,
        bFilter: false,
        bScrollCollapse: false,
        scrollX: true
    }); 
</script>
@stop
