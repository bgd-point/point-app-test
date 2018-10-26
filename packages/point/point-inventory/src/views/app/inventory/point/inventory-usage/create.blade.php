@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-inventory::app/inventory/point/inventory-usage/_breadcrumb')
        <li>Create</li>
    </ul>
    <h2 class="sub-header">Inventory Usage</h2>
    @include('point-inventory::app.inventory.point.inventory-usage._menu')

    @include('core::app.error._alert')

    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('inventory/point/inventory-usage')}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Form</legend>
                        </div>
                    </div> 
                </fieldset>
                <div class="form-group">
                    <label class="col-md-3 control-label">Date Usage *</label>
                    <div class="col-md-3">
                        <input type="text" id="form_date" name="form_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{ date(date_format_get(), strtotime(\Carbon::now())) }}">
                    </div>
                    <div class="col-md-3">
                        <div class="input-group bootstrap-timepicker">
                            <input type="text" id="time" name="time" class="form-control timepicker" value="{{ old('time') }}">
                            <span class="input-group-btn">
                                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary"><i class="fa fa-clock-o"></i></a>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Employee *</label>
                    <div class="col-md-6">
                        <select id="employee-id" name="employee_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                        <option ></option>
                        @foreach($list_employee as $employee)
                            <option value="{{$employee->id}}" @if(old('employee_id')==$employee->id) selected @endif> {{$employee->codeName}} </option>
                        @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Warehouse *</label>
                    <div class="col-md-6">
                        <div id="content-warehouse">
                            <select id="select-warehouse-id" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectWarehouse(this.value)">
                                <option ></option>
                                @foreach($list_warehouse as $warehouse)
                                    <option value="{{$warehouse->id}}" @if(old('warehouse_id')==$warehouse->id) selected @endif> {{$warehouse->codeName}} </option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" id="warehouse-id" name="warehouse_id" value="{{ old('warehouse_id') }}">
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
                                            <th style="width:1px!important;"></th>
                                            <th style="min-width:160px;">ITEM *</th>
                                            <th style="min-width:100px;">STOCK BEFORE USAGE *</th>
                                            <th style="min-width:100px;">QUANTITY USAGE *</th>
                                            <th style="min-width:250px;">NOTES *</th>
                                            <th style="min-width:160px;">ALLOCATION*</th>
                                            <th style="min-width:160px;">COA*</th>
                                        </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                        @for($counter=0; $counter<count(old('item_id')); $counter++)
                                        <tr>
                                            <td><a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a></td>
                                            <td>
                                                <select id="item-id-{{$counter}}" name="item_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem(this.value, {{$counter}})">
                                                <option value="{{old('item_id.'.$counter)}}">{{Point\Framework\Models\Master\Item::find(old('item_id.'.$counter))->name}}</option>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="text" id="quantity-{{$counter}}" name="stock_exist[]" class="form-control format-quantity text-right"
                                                    value="{{ old('stock_exist.'.$counter) }}" readonly/>
                                                    <span id="unit-id-{{$counter}}" class="input-group-addon">{{ old('unit1.'.$counter) }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="text" name="quantity_usage[]" class="form-control text-right" value="{{ old('quantity_usage.'.$counter) }}" />
                                                    <span id="unit-id2-{{$counter}}" class="input-group-addon">{{ old('unit2.'.$counter) }}</span>
                                                </div>
                                            </td>
                                            <td class="text-right"><input type="text" name="usage_notes[]" class="form-control" value="{{ old('usage_notes.'.$counter)}}" /></td>
                                        
                                            <td>
                                               <div class="@if(access_is_allowed_to_view('create.allocation')) input-group @endif">
                                                    <select id="allocation-id-{{$counter}}" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                                    @foreach($list_allocation as $allocation)
                                                        <option @if(old('allocation_id.'.$counter) == $allocation->id) selected @endif value="{{$allocation->id}}">{{$allocation->name}}</option>
                                                    @endforeach
                                                    </select>
                                                    @if(access_is_allowed_to_view('create.allocation'))
                                                        <span class="input-group-btn">
                                                            <a href="#modal-allocation" onclick="resetAjaxAllocation({{$counter}})" class="btn btn-effect-ripple btn-primary" data-toggle="modal">
                                                                <i class="fa fa-plus"></i>
                                                            </a>
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <select id="coa-id-{{ $counter }}" name="coa_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                                @foreach ($list_coa as $coa)
                                                    <option value="{{ $coa->id }}">
                                                        {{ $coa->coa_number }} {{ $coa->name }}
                                                    </option>
                                                @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                        @endfor
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6">
                                                <input type="button" id="addItemRow" class="btn btn-primary" value="Add Item">
                                            </td>
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
                            {{auth()->user()->name}}
                        </div>
                    </div>                  
                    <div class="form-group">
                        <label class="col-md-3 control-label">Request Approval To *</label>
                        <div class="col-md-6">
                            <select name="approval_to" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                <option></option>
                                @foreach($list_user_approval as $user_approval)
                                    @if($user_approval->may('approval.point.inventory.usage'))
                                    <option value="{{$user_approval->id}}" @if(old('approval_to') == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
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
var counter = {!! json_encode(count(old('item_id'))) !!};
var item_table = initDatatable('#item-datatable');
initFunctionRemoveInDatatable('#item-datatable', item_table);

$('#addItemRow').on( 'click', function () {
    if($("#warehouse-id").val() == ""){
        swal('Please, select warehouse');
        return false;
    }

    item_table.row.add( [
        '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
        '<select id="item-id-'+counter+'" name="item_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem(this.value, '+counter+')" >'
            +'<option></option>'
        +'</select>',
        '<div class="input-group">'
            +'<input type="text" id="quantity-'+counter+'" name="stock_exist[]" class="form-control text-right format-quantity" value=0 readonly/>'
            +'<span class="input-group-addon unit-'+counter+'"></span>'
            +'<input type="hidden" name="unit1[]" class="input-unit-'+counter+'">'
        +'</div>',
        '<div class="input-group">'
            +'<input type="text" name="quantity_usage[]" class="form-control format-quantity text-right" value=0 />'
            +'<span class="input-group-addon unit-'+counter+'"></span>'
            +'<input type="hidden" name="unit2[]" class="input-unit-'+counter+'">'
        +'</div>',
        '<input type="text" name="usage_notes[]" class="form-control" value="" />',
        '<div class="@if(access_is_allowed_to_view("create.allocation")) input-group @endif">'
            +'<select id="allocation-id-'+counter+'" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
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
        '<select id="coa-id-{{$counter}}" name="coa_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
            @foreach ($list_coa as $coa)
                +'<option value="{{ $coa->id }}">{{ $coa->coa_number }} {{ $coa->name }}</option>'
            @endforeach
        +'</select>'

    ] ).draw( false );

    initFormatNumber();
    initSelectize('#item-id-'+counter);
    initSelectize('#coa-id-'+counter);
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
    getItemUnit(item_id, '.unit-'+counter, 'html');
    getItemQuantity(item_id, $('#warehouse-id').val(), $("#form_date").val(), $("#time").val(), '#quantity-'+counter, 'input');
    getItemUnit(item_id, '.input-unit-'+counter, 'input');
}

function selectWarehouse(value) {
    var warehouse = $("#select-warehouse-id option:selected").text();
    html = "<div class='content-show'>"+warehouse+"</div>";
    $("#content-warehouse").html(html);
    $("#warehouse-id").val(value);
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

function reloadItemInSelectizeIfValidationFail() {
    $.ajax({
        url: "{{URL::to('master/item/list')}}",
        success: function(data) {
            for(var i = 0; i < counter; i++){
                if($('#item-id-'+i).length != 0){
                    var item = $('#item-id-'+i)[0].selectize;
                    item.load(function(callback) {
                        callback(eval(JSON.stringify(data.lists)));
                    });
                }
            }
        }
    });
}

function selectizeInFocus(item_id) {
    $(item_id)[0].selectize.clear().focus();
}

$(function(){
    if( counter > 0 ) {
        reloadItemInSelectizeIfValidationFail();   
    }
});

</script>
@stop
