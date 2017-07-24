@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-inventory::app/inventory/point/transfer-item/_breadcrumb')
        <li><a href="{{ url('inventory/point/transfer-item/send/'.$transfer_item->id) }}">{{ $transfer_item->formulir->form_number }}</a></li>
        <li>Edit</li>
    </ul>
    <h2 class="sub-header">Transfer Item</h2>
    @include('point-inventory::app.inventory.point.transfer-item._menu')
    @include('core::app.error._alert')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{url('inventory/point/transfer-item/send/'.$transfer_item->formulir_id)}}" method="post" class="form-horizontal form-bordered">
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
                        <input type="text" id="form_date" name="form_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{ date(date_format_get(), strtotime($transfer_item->formulir->form_date)) }}">
                    </div>
                    <div class="col-md-3">
                        <div class="input-group bootstrap-timepicker">
                            <input type="text" id="time" name="time" class="form-control timepicker" value="{{date('H:i', strtotime($transfer_item->formulir->form_date))}}">
                            <span class="input-group-btn">
                                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary"><i class="fa fa-clock-o"></i></a>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">From Warehouse *</label>
                    <div class="col-md-6 content-show">
                        {{ Point\Framework\Models\Master\Warehouse::find($transfer_item->warehouse_sender_id)->codeName}}
                        <input type="hidden" id="warehouse-id" name="warehouse_id" value="{{ $transfer_item->warehouse_sender_id }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label">To Warehouse *</label>
                    <div class="col-md-6 content-show">
                        {{ Point\Framework\Models\Master\Warehouse::find($transfer_item->warehouse_receiver_id)->codeName}}
                        <input type="hidden" id="warehouse-id" name="warehouse_to" value="{{ $transfer_item->warehouse_receiver_id }}">
                    </div>
                </div>
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Detail Item</legend>
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
                                            <th style="min-width:160px;">STOCK IN WAREHOUSE *</th>
                                            <th style="min-width:160px;">QUANTITY TRANSFER *</th>
                                        </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                        <?php $counter=0; ?>
                                        @foreach($transfer_item->items as $transfer_item_detail)
                                        <tr>
                                            <td><a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a></td>
                                            <td>
                                                <select id="item-id-{{$counter}}" name="item_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem(this.value, {{$counter}})">
                                                    <option selected value="{{$transfer_item_detail->item_id}}">{{$transfer_item_detail->item->codeName}}</option>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="text" id="quantity-{{$counter}}" name="stock_exist[]" class="form-control text-right format-quantity" 
                                                    value="{{ $transfer_item->formulir->approval_status == 1 ? inventory_get_available_stock($transfer_item->formulir->form_date, $transfer_item_detail->item_id, $transfer_item->warehouse_sender_id) + $transfer_item_detail->qty_send : inventory_get_available_stock($transfer_item->formulir->form_date, $transfer_item_detail->item_id, $transfer_item->warehouse_sender_id) }}" readonly/>
                                                    <span id="unit-id-{{$counter}}" class="input-group-addon">{{ $transfer_item_detail->unit }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="text" name="qty_send[]" class="form-control format-quantity text-right" value="{{$transfer_item_detail->qty_send }}" />
                                                    <span id="unit-id2-{{$counter}}" class="input-group-addon">{{ $transfer_item_detail->unit }}</span>
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
                            {{ $transfer_item->formulir->createdBy->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Request Approval To</label>
                        <div class="col-md-6">
                            <select name="approval_to" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                <option value="{{$transfer_item->formulir->approval_to}}">{{ $transfer_item->formulir->approvalTo->name }}</option>
                                @foreach($list_user_approval as $user_approval)

                                @if($user_approval->may('approval.point.inventory.transfer.item'))

                                @if($transfer_item->formulir->approval_to != $user_approval->id)
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
<style>
    tbody.manipulate-row:after {
      content: '';
      display: block;
      height: 100px;
    }
</style>
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
            +'<input type="text" name="qty_send[]" class="form-control format-quantity text-right" value=0 />'
            +'<span class="input-group-addon unit-'+counter+'"></span>'
        +'</div>'
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

function reloadItemInSelectizeIfValidationFail() {
    $.ajax({
        url: "{{URL::to('master/item/get-stock')}}",
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
