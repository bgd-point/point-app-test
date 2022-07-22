@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-inventory::app/inventory/point/inventory-usage/_breadcrumb')
        <li><a href="{{ url('inventory/point/inventory-usage/'.$inventory_usage->id) }}">{{ $inventory_usage->formulir->form_number }}</a></li>
        <li>Archived</li>
    </ul>
    <h2 class="sub-header">Inventory Usage</h2>
    @include('point-inventory::app.inventory.point.inventory-usage._menu')

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
                    {{ $inventory_usage_archived->formulir->archived }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Form Date</label>
                <div class="col-md-6 content-show">
                    {{ date_format_view($inventory_usage_archived->formulir->form_date, true) }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Warehouse</label>
                <div class="col-md-6 content-show">
                    {{ $inventory_usage_archived->warehouse->codeName }}
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
                                        <th>STOCK BEFORE USAGE</th>
                                        <th>QUANTITY USAGE</th>
                                        <th>NOTES</th>
                                        <th>ALLOCATION</th>
                                        <th>ACCOUNT</th>
                                    </tr>
                                </thead>
                                <tbody class="manipulate-row">
                                @foreach($inventory_usage_archived->listInventoryUsage as $inventory_usage_item)
                                <tr>
                                    <td></td>
                                    <td>{{ $inventory_usage_item->item->codeName }}</td>
                                    <td class="text-center">{{ number_format_quantity($inventory_usage_item->stock_in_database) }} {{$inventory_usage_item->unit}}</td>
                                    <td class="text-center">{{ number_format_quantity($inventory_usage_item->quantity_usage) }} {{$inventory_usage_item->unit}}</td>
                                    <td>{{ $inventory_usage_item->usage_notes }}</td>
                                    <td>{{ $inventory_usage_item->allocation->name }}</td>
                                    <td>{{ $inventory_usage_item->coa->coa_number }} {{ $inventory_usage_item->coa->name }}</td>
                                </tr>
                                @endforeach
                                </tbody> 
                                <tfoot>
                                    <tr>                                            
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
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
                        {{ $inventory_usage_archived->formulir->createdBy->name }}
                    </div>
                </div>                  
                <div class="form-group">
                    <label class="col-md-3 control-label">Request Approval To</label>
                    <div class="col-md-6 content-show">
                        {{ $inventory_usage_archived->formulir->approvalTo->name }}
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
                            'approval_status' => $inventory_usage_archived->formulir->approval_status,
                            'approval_message' => $inventory_usage_archived->formulir->approval_message,
                            'approval_at' => $inventory_usage_archived->formulir->approval_at,
                            'approval_to' => $inventory_usage_archived->formulir->approvalTo->name,
                        ])
                    </div>
                </div>                  
                <div class="form-group">
                    <label class="col-md-3 control-label">Form Status</label>
                    <div class="col-md-6 content-show">
                        @include('framework::app.include._form_status_label', ['form_status' => $inventory_usage_archived->formulir->form_status])
                    </div>
                </div> 
            </fieldset>
        </div>
    </div>    
</div>
@stop
