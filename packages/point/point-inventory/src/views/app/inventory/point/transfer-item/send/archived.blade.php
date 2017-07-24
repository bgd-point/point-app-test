@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-inventory::app/inventory/point/transfer-item/_breadcrumb')
        <li><a href="{{ url('inventory/point/transfer-item/send/'.$transfer_item->id) }}">{{ $transfer_item->formulir->form_number }}</a></li>
        <li>Archived</li>
    </ul>
    <h2 class="sub-header">transfer item</h2>
    @include('point-inventory::app.inventory.point.transfer-item._menu')

    @include('core::app.error._alert')

    <div class="block full">  
        <div class="form-horizontal form-bordered">
            <div class="form-group">
                <div class="col-md-12">
                    <div class="alert alert-danger alert-dismissable">
                        <h1 class="text-center"><strong>Archived</strong></h1>
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
                <label class="col-md-3 control-label">Form Number</label>
                <div class="col-md-6 content-show">
                    {{ $transfer_item_archived->formulir->archived }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Form Date</label>
                <div class="col-md-6 content-show">
                    {{ date_format_view($transfer_item_archived->formulir->form_date) }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">To Warehouse</label>
                <div class="col-md-6 content-show">
                    {{ $transfer_item_archived->warehouseFrom->codeName }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">From Warehouse</label>
                <div class="col-md-6 content-show">
                    {{ $transfer_item_archived->warehouseTo->codeName }}
                </div>
            </div>
            <fieldset>
                <div class="form-group">
                    <div class="col-md-12">
                        <legend><i class="fa fa-angle-right"></i> Item</legend>                    
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="table-responsive"> 
                            <table id="item-datatable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>ITEM</th>
                                        <th>QTY SEND</th>
                                        <th>QTY RECEIVED</th>
                                    </tr>
                                </thead>
                                <tbody class="manipulate-row">
                                @foreach($transfer_item_archived->items as $transfer_item_detail)
                                <tr>
                                    <td></td>
                                    <td>{{ $transfer_item_detail->item->codeName }}</td>
                                    <td class="text-center">{{ number_format_quantity($transfer_item_detail->qty_send) }} {{$transfer_item_detail->unit}}</td>
                                    <td class="text-center">{{ number_format_quantity($transfer_item_detail->qty_received) }} {{$transfer_item_detail->unit}}</td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table> 
                        </div>
                    </div>                                           
                </div>
            </fieldset>

            <fieldset>
                <div class="form-group">
                    <div class="col-md-12">
                        <legend><i class="fa fa-angle-right"></i> Authorized User</legend>
                    </div>
                </div>  
                <div class="form-group">
                    <label class="col-md-3 control-label">Form Creator</label>
                    <div class="col-md-6 content-show">
                        {{ $transfer_item_archived->formulir->createdBy->name }}
                    </div>
                </div>                  
                <div class="form-group">
                    <label class="col-md-3 control-label">Request Approval To</label>
                    <div class="col-md-6 content-show">
                        {{ $transfer_item_archived->formulir->approvalTo->name }}
                    </div>
                </div> 
            </fieldset>

            <fieldset>
                <div class="form-group">
                    <div class="col-md-12">
                        <legend><i class="fa fa-angle-right"></i> Status</legend>                    
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Approval Status</label>
                    <div class="col-md-6 content-show">
                        @include('framework::app.include._approval_status_label', [
                            'approval_status' => $transfer_item_archived->formulir->approval_status,
                            'approval_message' => $transfer_item_archived->formulir->approval_message,
                            'approval_at' => $transfer_item_archived->formulir->approval_at,
                            'approval_to' => $transfer_item_archived->formulir->approvalTo->name,
                        ])
                    </div>
                </div>                  
                <div class="form-group">
                    <label class="col-md-3 control-label">Form Status</label>
                    <div class="col-md-6 content-show">
                        @include('framework::app.include._form_status_label', ['form_status' => $transfer_item_archived->formulir->form_status])
                    </div>
                </div> 
            </fieldset>
        </div>
    </div>    
</div>
@stop
