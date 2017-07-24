@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-inventory::app/inventory/point/stock-correction/_breadcrumb')
        <li><a href="{{ url('inventory/point/stock-correction/'.$stock_correction->id) }}">{{ $stock_correction->formulir->form_number }}</a></li>
        <li>Archived</li>
    </ul>
    <h2 class="sub-header">Stock correction</h2>
    @include('point-inventory::app.inventory.point.stock-correction._menu')

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
                    {{ $stock_correction_archived->formulir->archived }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Form Date</label>
                <div class="col-md-6 content-show">
                    {{ date_format_view($stock_correction_archived->formulir->form_date, true) }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Warehouse</label>
                <div class="col-md-6 content-show">
                    {{ $stock_correction_archived->warehouse->codeName }}
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
                                        <th>STOCK BEFORE CORRECTION</th>
                                        <th>CORRECTION</th>
                                        <th>NOTES</th>
                                    </tr>
                                </thead>
                                <tbody class="manipulate-row">
                                @foreach($stock_correction_archived->items as $stock_correction_item)

                                <tr>
                                    <td></td>
                                    <td>{{ $stock_correction_item->item->codeName }}</td>
                                    <td>{{ number_format_quantity($stock_correction_item->stock_in_database) }} {{$stock_correction_item->unit}}</td>
                                    <td>{{ number_format_quantity($stock_correction_item->quantity_correction) }} {{$stock_correction_item->unit}}</td>
                                    <td>{{ $stock_correction_item->correction_notes }}</td>
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
                        {{ $stock_correction_archived->formulir->createdBy->name }}
                    </div>
                </div>                  
                <div class="form-group">
                    <label class="col-md-3 control-label">Request Approval To</label>
                    <div class="col-md-6 content-show">
                        {{ $stock_correction_archived->formulir->approvalTo->name }}
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
                            'approval_status' => $stock_correction_archived->formulir->approval_status,
                            'approval_message' => $stock_correction_archived->formulir->approval_message,
                            'approval_at' => $stock_correction_archived->formulir->approval_at,
                            'approval_to' => $stock_correction_archived->formulir->approvalTo->name,
                        ])
                    </div>
                </div>                  
                <div class="form-group">
                    <label class="col-md-3 control-label">Form Status</label>
                    <div class="col-md-6 content-show">
                        @include('framework::app.include._form_status_label', ['form_status' => $stock_correction_archived->formulir->form_status])
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
        bScrollCollapse: false
    }); 
</script>
@stop
