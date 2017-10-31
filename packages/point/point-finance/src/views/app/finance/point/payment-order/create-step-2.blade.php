@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
         @include('point-finance::app.finance.point.payment-order._breadcrumb')
         <li>Create step 2</li>
    </ul>
    <h2 class="sub-header">Payment Order</h2>
    @include('point-finance::app.finance.point.payment-order._menu')

    @include('core::app.error._alert')

    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('finance/point/payment-order/create-step-3')}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}
                
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Payment Order</legend>
                        </div>
                    </div> 
                </fieldset>                
                <div class="form-group">
                    <label class="col-md-3 control-label">Payment date *</label>
                    <div class="col-md-3">
                        <input type="text" name="payment_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{date(date_format_get(), strtotime(\Carbon::now()))}}">
                    </div>
                    <div class="col-md-3">
                        <div class="input-group bootstrap-timepicker">
                            <input type="text" id="time" name="time" class="form-control timepicker">
                            <span class="input-group-btn">
                                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary"><i class="fa fa-clock-o"></i></a>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Payment To *</label>
                    <div class="col-md-6 content-show">
                        <input type="hidden" name="person_id" value="{{$person->id}}">
                        {!! get_url_person($person->id) !!}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Payment Type *</label>
                    <div class="col-md-6">
                        <select id="payment_type" name="payment_type" class="selectize" style="width: 100%;" data-placeholder="Please choose">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>
                    <div class="col-md-6">
                        <input type="text" name="notes" class="form-control"value="{{old('notes')}}">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="block full">
                            <div class="block-title">
                                <ul class="nav nav-tabs" data-toggle="tabs">
                                    <li><a href="#block-tabs-detail"></a></li>
                                </ul>
                            </div>
                            <div class="tab-content">
                                <div class="tab-pane active" id="block-tabs-detail">
                                    <div class="table-responsive" style="overflow-x:visible" >
                                        <table id="item-datatable" class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th style="min-width: 115px">Account *</th>
                                                    <th style="min-width: 115px">Notes*</th>
                                                    <th style="min-width: 115px" class="text-right">Amount *</th>
                                                    <th style="min-width: 150px" class="text-center">Allocation *</th>
                                                </tr>
                                            </thead>
                                            <tbody class="manipulate-row">
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="2"><input type="button" id="addItemRow" class="btn btn-primary" value="add account"></td>
                                                    <td class="text-right"><h4><b>Total Expense</b></h4></td>
                                                    <td class="text-right">
                                                        <input readonly type="hidden" id="total" name="total" class="form-control format-price text-right" value="0" />
                                                        <h4><strong><span id="totalAmount" class="format-price text-right"></span></strong></h4>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Authorized User</legend>
                        </div>
                    </div>  
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form creator *</label>
                        <div class="col-md-6 content-show">
                            {{auth()->user()->name}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Ask approval to *</label>
                        <div class="col-md-6">
                            <select id="approval-to" name="approval_to" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                <option></option>
                                @foreach($list_user_approval as $user_approval)
                                @if($user_approval->may('approval.point.finance.payment.order'))
                                <option value="{{$user_approval->id}}" @if(old('user_approval') == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
                                @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>

                <div class="form-group">
                    <div class="col-md-6 col-md-offset-3">
                        <button type="submit" class="btn btn-effect-ripple btn-primary">Review</button>
                    </div>
                </div>
            </form>
        </div>
    </div>  
</div>

@include('framework::app.master.allocation._create')
@include('point-finance::app.finance.point.payment-order._create-coa-expense')
@stop

@section('scripts')
<script>
var item_table = initDatatable('#item-datatable');
var counter = $("#item-datatabl").dataTable().fnGetNodes().length;
$('#addItemRow').on( 'click', function () {
    item_table.row.add( [
        '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
        '<div class="@if(access_is_allowed_to_view("create.coa")) input-group @endif"><select id="coa-id-'+counter+'" name="coa_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." required="">'
            +'<option ></option>'
            @foreach($list_coa as $coa)
            +'<option value="{{$coa->id}}">{{ $coa->account }}</option>'
           @endforeach
        +'</select>'
        @if(access_is_allowed_to_view('create.coa'))
        +'<span class="input-group-btn">'
            +'<a href="#modal-coa-expense" onclick=resetForm('+counter+') class="btn btn-effect-ripple btn-primary" data-toggle="modal">'
                +'<i class="fa fa-plus"></i>'
            +'</a>'
        +'</span>'
        @endif
        +'</div>',
        '<input type="text" name="detail_notes[]" required="" class="form-control" value="" />',
        '<input type="text" id="amount-'+counter+'" required="" name="amount[]" class="form-control format-quantity row-total text-right calculate" value="0" class="amountId" />',
        '<div class="@if(access_is_allowed_to_view("create.allocation")) input-group @endif"><select required="" id="allocation-id-'+counter+'" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
            @foreach($list_allocation as $allocation)
            +'<option value="{{$allocation->id}}">{{$allocation->name}}</option>'
            @endforeach
        +'</select>'
        @if(access_is_allowed_to_view('create.allocation'))
        +'<span class="input-group-btn">'
            +'<a href="#modal-allocation" onclick=resetForm('+counter+') class="btn btn-effect-ripple btn-primary" data-toggle="modal">'
                +'<i class="fa fa-plus"></i>'
            +'</a>'
        +'</span>'
        @endif
        +'</div>',
    ] ).draw( false );

    initSelectize('#coa-id-'+counter);
    initSelectize('#allocation-id-'+counter);
    initFormatNumber();
    $("textarea").on("click", function () {
       $(this).select();
    });
    $("input[type='text']").on("click", function () {
       $(this).select();
    });
    $('.calculate').keyup(function(){ 
        calculate();
    });

    reloadAllocation(counter);
    reloadCoaExpense(counter);
    counter++;
} );

$('#item-datatable tbody').on( 'click', '.remove-row', function () {
    item_table.row( $(this).parents('tr') ).remove().draw();
    calculate();
} );

$(document).on("keypress", 'form', function (e) {
    var code = e.keyCode || e.which;
    if (code == 13) {
        e.preventDefault();
        return false;
    }
});

$('.calculate').keyup(function(){ 
    calculate();
});

$(function() {
    calculate();
});

function calculate() {
    var rows = $("#item-datatable").dataTable().fnGetNodes();
    var total = 0;
    for(var i=0;i<rows.length;i++)
    {       
        total += dbNum($(rows[i]).find(".row-total").val());
    }    
    $('#total').val(appNum(total));
    $('#totalAmount').html(appNum(total));
}

function addNewAllocationInSelectize(index, result) {
    var selectize = $("#allocation-id-"+index)[0].selectize;
    selectize.addOption({value:result.code,text:result.name});
    selectize.addItem(result.code);

    for (var i = 0; i < counter; i++) {
        if(i != index){
            if($('#allocation-id-'+i).length != 0){
                var allocation = $('#allocation-id-'+i)[0].selectize;
                allocation.addOption({value:result.code,text:result.name});    
            }
        }
    };
}            

function reloadAllocation(counter)
{
    $.ajax({
        url: "{{URL::to('master/allocation/list')}}",
        success: function(data) {
            console.log(data);
            var allocation = $('#allocation-id-'+counter)[0].selectize;
            allocation.load(function(callback) {
                callback(eval(JSON.stringify(data.lists)));
            });

        }, error: function(data) {
            
        }
    });
}

function resetForm(index){
    $("#button-coa").html("Save");
    $("#name-coa").val("");
    $("#index").val(index);
    $("#button-allocation").html("Save");
    $("#name-allocation").val("");

}

function addNewCoaInSelectize(index, result) {
    var selectize = $("#coa-id-"+index)[0].selectize;
    selectize.addOption({value:result.code,text:result.name});
    selectize.addItem(result.code);

    for (var i = 0; i < counter; i++) {
        if(i != index){
            if($('#coa-id-'+i).length != 0){
                var coa = $('#coa-id-'+i)[0].selectize;
                coa.addOption({value:result.code,text:result.name});
            }
        }

    };
}

function reloadCoaExpense(counter)
{
    $.ajax({
        url: '{{url("master/coa/ajax/list/position/expense")}}',
        success: function(data) {
            console.log(data);
            var coa = $('#coa-id-'+counter)[0].selectize;
            coa.load(function(callback) {
                callback(eval(JSON.stringify(data.lists)));
            });

        }, error: function(data) {
            swal('Failed', 'Something went wrong');
        }
    });
}

</script>
@stop
