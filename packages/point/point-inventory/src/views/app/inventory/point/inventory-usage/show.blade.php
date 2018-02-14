@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-inventory::app/inventory/point/inventory-usage/_breadcrumb')
        <li>{{ $inventory_usage->formulir->form_number }}</li>
    </ul>
    <h2 class="sub-header">Inventory Usage</h2>
    @include('point-inventory::app.inventory.point.inventory-usage._menu')
    @include('core::app.error._alert')

    <div class="block full">
        <!-- Block Tabs Title -->
        <div class="block-title">
            <ul class="nav nav-tabs" data-toggle="tabs">
                <li class="active"><a href="#block-tabs-home">Form</a></li>
                <li><a href="#block-tabs-settings"><i class="gi gi-settings"></i></a></li>
            </ul>
        </div>
        <!-- END Block Tabs Title -->

        <!-- Tabs Content -->
        <div class="tab-content">
            <div class="tab-pane active" id="block-tabs-home">
                <div class="form-horizontal form-bordered">
                    <fieldset>
                        <div class="form-group pull-right">
                            <div class="col-md-12">
                                @include('framework::app.include._approval_status_label', [
                                    'approval_status' => $inventory_usage->formulir->approval_status,
                                    'approval_message' => $inventory_usage->formulir->approval_message,
                                    'approval_at' => $inventory_usage->formulir->approval_at,
                                    'approval_to' => $inventory_usage->formulir->approvalTo->name,
                                ])
                                @include('framework::app.include._form_status_label', ['form_status' => $inventory_usage->formulir->form_status])
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div> 
                    </fieldset>
                    @if($revision > 0)
                    <div class="form-group">
                        <label class="col-md-3 control-label">Revision</label>
                        <div class="col-md-6 content-show">
                            {{ $revision }}
                        </div>
                    </div>
                    @endif
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Number</label>
                        <div class="col-md-6 content-show">
                            {{ $inventory_usage->formulir->form_number }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date</label>
                        <div class="col-md-6 content-show">
                            {{ date_format_view($inventory_usage->formulir->form_date, false) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Employee</label>
                        <div class="col-md-6 content-show">
                            @if($inventory_usage->employee_id != null)
                            {{ $inventory_usage->employee->codeName }}
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Warehouse</label>
                        <div class="col-md-6 content-show">
                            {{ $inventory_usage->warehouse->codeName }}
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
                                                <th style="min-width: 250px;">ITEM</th>
                                                <th class="text-right">STOCK BEFORE USAGE</th>
                                                <th class="text-right">QUANTITY USAGE</th>
                                                <th>NOTES</th>
                                                <th>ALLOCATION</th>
                                            </tr>
                                        </thead>
                                        <tbody class="manipulate-row">
                                        @foreach($inventory_usage->listInventoryUsage as $stock_usage_item)
                                        <tr>
                                            <td>{{ $stock_usage_item->item->codeName }}</td>
                                            <td class="text-right">{{ number_format_quantity($stock_usage_item->stock_in_database) }} {{$stock_usage_item->unit}}</td>
                                            <td class="text-right">{{ number_format_quantity($stock_usage_item->quantity_usage) }} {{$stock_usage_item->unit}}</td>
                                            <td>{{ $stock_usage_item->usage_notes }}</td>
                                            <td>{{ $stock_usage_item->allocation->name }}</td>
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
                                {{ $inventory_usage->formulir->createdBy->name }}
                            </div>
                        </div>                  
                        <div class="form-group">
                            <label class="col-md-3 control-label">Request Approval To</label>
                            <div class="col-md-6 content-show">
                                {{ $inventory_usage->formulir->approvalTo->name }}
                            </div>
                        </div> 
                    </fieldset>
                </div>
            </div>

            <div class="tab-pane" id="block-tabs-settings">
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Action</legend>                    
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            @if(formulir_view_edit($inventory_usage->formulir, 'update.point.inventory.usage'))
                            <a href="{{url('inventory/point/inventory-usage/'.$inventory_usage->id.'/edit')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                            @endif
                            @if(formulir_view_cancel($inventory_usage->formulir, 'delete.point.inventory.usage'))
                            <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                               onclick="secureCancelForm('{{url('formulir/cancel')}}',
                                    '{{ $inventory_usage->formulir_id }}',
                                    'delete.point.inventory.usage')"><i class="fa fa-times"></i> Cancel Form</a>
                            @endif
                                <a class="btn btn-effect-ripple btn-info"
                                        href="{{url('inventory/point/inventory-usage/'.$inventory_usage->id.'/export')}}">Print</a>
                        </div>
                    </div>
                </fieldset>

                @if(formulir_view_approval($inventory_usage->formulir, 'approval.point.inventory.usage'))
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6">
                            <form action="{{url('inventory/point/inventory-usage/'.$inventory_usage->id.'/approve')}}" method="post">
                                {!! csrf_field() !!}

                                <div class="input-group">
                                    <input type="text" name="approval_message" class="form-control" placeholder="Message">
                                    <span class="input-group-btn">
                                        <input type="submit" class="btn btn-primary" value="Approve">
                                    </span>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form action="{{url('inventory/point/inventory-usage/'.$inventory_usage->id.'/reject')}}" method="post">
                                {!! csrf_field() !!}

                                <div class="input-group">
                                    <input type="text" name="approval_message" class="form-control" placeholder="Message">
                                    <span class="input-group-btn">
                                        <input type="submit" class="btn btn-danger" value="Reject">
                                    </span>
                                </div>
                            </form>
                        </div>
                    </div>
                </fieldset>                
                @endif
                
                @if($list_inventory_usage_archived->count() > 0)
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Archived Form</legend>                    
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12 content-show">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th></th> 
                                            <th>Form Date</th>
                                            <th>Form Number</th>
                                            <th>Created By</th>
                                            <th>Updated By</th>
                                            <th>Reason</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $count=0;?>
                                        @foreach($list_inventory_usage_archived as $inventory_usage_archived)
                                        <tr>
                                            <td class="text-center">
                                                <a href="{{ url('inventory/point/inventory-usage/'.$inventory_usage_archived->id.'/archived') }}" data-toggle="tooltip" title="Show" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-file"></i> {{ 'Revision ' . $count++ }}</a>
                                            </td>
                                            <td>{{ date_format_view($inventory_usage_archived->formulir->form_date) }}</td>
                                            <td>{{ $inventory_usage->formulir->form_number }}</td>
                                            <td>{{ $inventory_usage_archived->formulir->createdBy->name }}</td>
                                            <td>{{ $inventory_usage_archived->formulir->updatedBy->name }}</td>
                                            <td>{{ $inventory_usage_archived->formulir->edit_notes }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody> 
                                </table>
                            </div>
                        </div>
                    </div>
                </fieldset>
                @endif
            </div>
        </div>
        <!-- END Tabs Content -->
    </div>    
</div>
@stop
