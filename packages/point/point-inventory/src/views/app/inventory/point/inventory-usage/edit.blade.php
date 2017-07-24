@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-inventory::app/inventory/point/inventory-usage/_breadcrumb')
        <li><a href="{{ url('inventory/point/inventory-usage/'.$inventory_usage->id) }}">{{ $inventory_usage->formulir->form_number }}</a></li>
        <li>Edit</li>
    </ul>
    <h2 class="sub-header">Inventory usage</h2>
    @include('point-inventory::app.inventory.point.inventory-usage._menu')
    @include('core::app.error._alert')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{url('inventory/point/inventory-usage/'.$inventory_usage->formulir_id)}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}
                <input name="_method" type="hidden" value="PUT">

                <div class="form-group">
                    <label class="col-md-3 control-label">Reason to edit *</label>
                    <div class="col-md-6">
                        <input type="text" name="edit_notes" class="form-control" value="" autofocus>
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
                    <label class="col-md-3 control-label">Form Date *</label>
                    <div class="col-md-3">
                        <input type="text" id="form_date" name="form_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{ date(date_format_get(), strtotime($inventory_usage->formulir->form_date)) }}">
                    </div>
                    <div class="col-md-3">
                        <div class="input-group bootstrap-timepicker">
                            <input type="text" id="time" name="time" class="form-control timepicker" value="{{date('H:i', strtotime($inventory_usage->formulir->form_date))}}">
                            <span class="input-group-btn">
                                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary"><i class="fa fa-clock-o"></i></a>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Warehouse *</label>
                    <div class="col-md-6 content-show">
                        {{ Point\Framework\Models\Master\Warehouse::find($inventory_usage->warehouse_id)->name}}
                        <input type="hidden" id="warehouse-id" name="warehouse_id" value="{{ $inventory_usage->warehouse_id }}">
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
                                            <th style="min-width:80px;"></th>
                                            <th style="min-width:250px;">ITEM *</th>
                                            <th style="min-width:160px;">STOCK BEFORE <br>USAGE*</th>
                                            <th style="min-width:160px;">QUANTITY <br>USAGE *</th>
                                            <th style="min-width:250px;">NOTES *</th>
                                            <th style="min-width:250px;">ALLOCATION *</th>
                                        </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                        <?php $counter=0; ?>
                                        @foreach($inventory_usage->listInventoryUsage as $inventory_usage_item)
                                        <tr>
                                            <td><a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a></td>
                                            <td>
                                                <select id="item-id-{{$counter}}" name="item_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem(this.value, {{$counter}})">
                                                    <option selected value="{{$inventory_usage_item->item_id}}">{{$inventory_usage_item->item->codeName}}</option>
                                                </select>
                                            </td>
                                            <td>
                                                <?php $unit = Point\Framework\Models\Master\Item::defaultUnit($inventory_usage_item->item_id); ?>
                                                <div class="input-group">
                                                    <input type="text" id="quantity-{{$counter}}" name="stock_exist[]" class="form-control text-right format-quantity"
                                                    value="{{ (inventory_get_closing_stock(date('Y-m-01 00:00:00'), $inventory_usage->formulir->form_date, $inventory_usage_item->item_id, $inventory_usage->warehouse_id))+$inventory_usage_item->quantity_usage }}" readonly/>
                                                    <span id="unit-id-{{$counter}}" class="input-group-addon">{{ $unit->name }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="text" name="quantity_usage[]" class="form-control format-quantity text-right" value="{{$inventory_usage_item->quantity_usage }}" />
                                                    <span id="unit-id2-{{$counter}}" class="input-group-addon">{{ $unit->name }}</span>
                                                </div>
                                            </td>
                                            <td class="text-right"><input type="text" name="usage_notes[]" class="form-control" value="{{ $inventory_usage_item->usage_notes }}" /></td>
                                            <td>
                                                <div class="@if(access_is_allowed_to_view('create.allocation')) input-group @endif">
                                                    <select id="allocation-id-{{$counter}}" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder=" Choose..">
                                                        @foreach($list_allocation as $allocation)
                                                            <option value="{{$allocation->id}}" @if($allocation->id == $inventory_usage_item->allocation_id) selected @endif>{{$allocation->name}}</option>
                                                        @endforeach
                                                    </select>
                                                    @if(access_is_allowed_to_view('create.allocation'))
                                                        <span class="input-group-btn">
                                                            <a href="#modal-allocation" onclick="resetAjaxAllocation({{$counter}})" class="btn btn-effect-ripple btn-primary" data-toggle="modal">
                                                                <i class="fa fa-plus"></i>'
                                                            </a>
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        <?php $counter++; ?>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td><input type="button" id="addItemRow" class="btn btn-primary" value="Add Item"></td>
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
                        <div class="col-md-6">
                            <select name="approval_to" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                <option value="{{$inventory_usage->formulir->approval_to}}">{{ $inventory_usage->formulir->approvalTo->name }}</option>
                                @foreach($list_user_approval as $user_approval)

                                @if($user_approval->may('approval.point.inventory.stock.usage'))

                                @if($inventory_usage->formulir->approval_to != $user_approval->id)
                                <option value="{{$user_approval->id}}">{{$user_approval->name}}</option>
                                @endif

                                @endif

                                @endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>

                <div class="form-group">
                    <div class="col-md-6 col-md-offset-3">
                        <button type="submit" class="btn btn-effect-ripple btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@include('framework::app.master.allocation._create')
@include('framework::scripts.item')
@stop

@section('scripts')
<script>
var counter = {{$counter}};
var item_table = initDatatable('#item-datatable');
initFunctionRemoveInDatatable('#item-datatable', item_table);

$('#addItemRow').on( 'click', function () {

    item_table.row.add( [
        '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
        '<select id="item-id-'+counter+'" name="item_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem(this.value, '+counter+');" >'
            +'<option></option>'
        +'</select>',
        '<div class="input-group">'
            +'<input type="text" id="quantity-'+counter+'" name="stock_exist[]" class="form-control text-right format-quantity" value=0 readonly/>'
            +'<span class="input-group-addon unit-'+counter+'"></span>'
        +'</div>',
        '<div class="input-group">'
            +'<input type="text" name="quantity_usage[]" class="form-control format-quantity text-right" value=0 />'
            +'<span class="input-group-addon unit-'+counter+'"></span>'
        +'</div>',
        '<input type="text" name="usage_notes[]" class="form-control" value="" />',
        '<div class="@if(access_is_allowed_to_view("create.allocation")) input-group @endif"><select id="allocation-id-'+counter+'" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
        @foreach($list_allocation as $allocation)
        +'<option value="{{$allocation->id}}">{{$allocation->name}}</option>'
        @endforeach
        +'</select>'
        @if(access_is_allowed_to_view('create.allocation'))
        +'<span class="input-group-btn">'
        +'<a href="#modal-allocation" onclick=resetAjaxAllocation('+counter+') class="btn btn-effect-ripple btn-primary" data-toggle="modal">'
        +'<i class="fa fa-plus"></i>'
        +'</a>'
        +'</span>'
        @endif
        +'</div>',
    ] ).draw( false );

    initFormatNumber();
    initSelectize('#item-id-'+counter);
    reloadItemInSelectize("#item-id-"+counter);
    initSelectize('#allocation-id-'+counter);
    reloadAllocationInSelectize('#allocation-id-'+counter);

    counter++;
} );

function selectItem(item_id, counter) {
    for (var i = 0; i < counter; i++) {
        id = $('#item-id-'+i).val();
        if(id == item_id && counter != i){
            swal("Failed", "Item is already, please choose another item");
            selectizeInFocus('#item-id-'+counter);
            return false;
            break;

        }
    };
    getItemUnit(item_id, ".unit-"+counter , "html");
    getItemQuantity(item_id, $('#warehouse-id').val(), $("#form_date").val(), $("#time").val(), "#quantity-"+counter, 'input');
}

function reloadItemInSelectize(item_id) {
    $.ajax({
        url: "{{URL::to('master/item/get-stock')}}",
        data: {
            warehouse_id: $("#warehouse-id").val(),
            form_date: $("#form_date").val(),
            time: $("#time").val(),
        },
        success: function(data) {
            var items = $(item_id)[0].selectize;
            items.clear();
            items.load(function(callback) {
                callback(eval(JSON.stringify(data.lists)));
            });
        }
    });
}

function selectizeInFocus(item_id) {
    $(item_id)[0].selectize.clear().focus();
}

$(function(){
    if( counter > 0 ) {
        for(var i = 0; i < counter; i++){
            if($('#item-id-'+i).length != 0){
                reloadItem('#item-id-'+i, false);
            }
        }
    }
});

</script>
@stop
