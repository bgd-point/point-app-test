@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
         @include('point-finance::app.finance.point.cheque._breadcrumb')
         <li>Receive Payment</li>
    </ul>
    <h2 class="sub-header">Cheque</h2>
    @include('point-finance::app.finance.point.cheque._menu')

    @include('core::app.error._alert')

    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('finance/point/cheque/in')}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}
                
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Form</legend>
                        </div>
                    </div> 
                </fieldset>                
                <div class="form-group">
                    <label class="col-md-3 control-label">Payment date</label>
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
                    <label class="col-md-3 control-label">Cheque Account *</label>
                    <div class="col-md-6">
                        <select name="account_cheque_id" class="selectize" data-placeholder="Choose oaccount...">
                            <option></option>
                            @foreach($list_cheque_account as $cheque_account)
                                <option selected value="{{$cheque_account->id}}">{{$cheque_account->account}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Payment From *</label>
                    <div class="col-md-6">
                        <div class="@if(access_is_allowed_to_view('create.customer') || access_is_allowed_to_view('create.supplier') || access_is_allowed_to_view('create.expedition')) input-group @endif">
                            <select id="person_id" name="person_id" class="selectize" style="width: 100%;" data-placeholder="Please choose">
                                <option></option>
                                @foreach($list_person as $person)
                                    <option value="{{$person->id}}" @if(old('person') == $person->id) selected @endif>{{$person->codeName}}</option>
                                @endforeach
                            </select>
                            <span class="input-group-btn">
                                <a href="#modal-contact" class="btn btn-effect-ripple btn-primary" data-toggle="modal">
                                    <i class="fa fa-plus"></i>
                                </a>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>
                    <div class="col-md-6">
                        <input type="text" name="notes" class="form-control"value="{{old('notes')}}">
                    </div>
                </div>
                <fieldset>
                    <div class="form-group">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="cheque-datatable" class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>Bank</th>
                                            <th>Form Date</th>
                                            <th>Due Date</th>
                                            <th>Number</th>
                                            <th>Notes</th>
                                            <th>Amount</th>
                                        </tr>
                                        </thead>
                                        <tbody class="manipulate-row">
                                        <?php $counter = 0;?>
                                        @if(count(old('bank')) > 0)
                                            @for($counter; $counter < count(old('bank')); $counter++ )
                                            <tr>
                                                <td><a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a></td>
                                                <td><input class="form-control" type="text" name="bank[]" id="bank-{{$counter}}"></td>
                                                <td>
                                                    <input type="text" name="form_date_cheque[]" id="form-date-cheque-{{$counter}}" class="form-control date input-datepicker"
                                                       data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                                       value="{{ date(date_format_get(), strtotime(\Carbon::now())) }}">
                                                </td>
                                                <td>
                                                    <input type="text" name="due_date_cheque[]" id="due-date-cheque-{{$counter}}" class="form-control date input-datepicker"
                                                       data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                                       value="{{ date(date_format_get(), strtotime(\Carbon::now())) }}">
                                                </td>
                                                <td>
                                                    <input type="text" name="number_cheque[]" id="number-cheque-{{$counter}}" value="{{old('number_cheque')[$counter]}}" class="form-control">
                                                </td>
                                                <td>
                                                    <input type="text" name="notes_cheque[]" id="notes-cheque-{{$counter}}" value="{{old('notes_cheque')[$counter]}}" class="form-control">
                                                </td>
                                                <td>
                                                    <input type="text" name="amount_cheque[]" id="amount-cheque-{{$counter}}" value="{{old('amount_cheque')[$counter]}}" class="form-control text-right format-price-alt  row-total-cheque calculate-cheque">
                                                </td>
                                            </tr>
                                            @endfor
                                        @endif
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td>
                                                    <input type="button" id="addChequeRow" class="btn btn-primary" value="Add Cheque">
                                                </td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td> Total Cheque
                                                    <input type="text" readonly="" class="form-control format-price-alt" name="total_cheque" id="total-cheque" value="{{old('total_cheque')}}">
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                </fieldset>
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="block full">
                            <!-- Block Tabs Title -->
                            <div class="block-title">
                                <ul class="nav nav-tabs" data-toggle="tabs">
                                    <li><a href="#block-tabs-detail"></a></li>
                                </ul>
                            </div>
                            <!-- END Block Tabs Title -->

                            <!-- Tabs Content -->
                            <div class="tab-content">
                                <div class="tab-pane active" id="block-tabs-detail">
                                    <div class="table-responsive" style="overflow-x:visible" >
                                        <table id="item-datatable" class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th style="min-width: 115px">Account</th>
                                                    <th style="min-width: 115px">Notes</th>
                                                    <th style="min-width: 115px">Amount</th>
                                                    <th style="min-width: 150px">Allocation</th>
                                                </tr>
                                            </thead>
                                            <tbody class="manipulate-row">
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="2"><input type="button" id="addItemRow" class="btn btn-primary" value="add Account"></td>
                                                    <td class="text-right">Total</td>
                                                    <td>
                                                        <input readonly type="hidden" id="total" name="total" class="form-control format-price text-right" value="0" />
                                                        <span id="totalAmount" class="format-price text-right"></span>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- END Tabs Content -->
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
                        <label class="col-md-3 control-label">Form creator</label>
                        <div class="col-md-6 content-show">
                            {{auth()->user()->name}}
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

@include('framework::app.master.contact.__create-person')
@include('framework::app.master.allocation._create')
@include('point-finance::app.finance.point.cheque.in._create-coa-revenue')
@stop

@section('scripts')
<script>
var item_table = initDatatable('#item-datatable');
var counter = $("#item-datatabl").dataTable().fnGetNodes().length;
$('#addItemRow').on( 'click', function () {
    item_table.row.add( [
        '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
        '<div class="@if(access_is_allowed_to_view("create.coa")) input-group @endif"><select id="coa-id-'+counter+'" name="coa_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
            +'<option ></option>'
            @foreach($list_coa as $coa)
            +'<option value="{{$coa->id}}">{{ $coa->account }}</option>'
           @endforeach
        +'</select>'
        @if(access_is_allowed_to_view('create.coa'))
        +'<span class="input-group-btn">'
            +'<a href="#modal-coa-revenue" onclick=resetForm('+counter+') class="btn btn-effect-ripple btn-primary" data-toggle="modal">'
                +'<i class="fa fa-plus"></i>'
            +'</a>'
        +'</span>'
        @endif
        +'</div>',
        '<input type="text" name="notes_detail[]" class="form-control" value="" />',
        '<input type="text" id="amount-'+counter+'" name="amount[]" class="form-control format-quantity row-total text-right calculate" value="0" />',
        '<div class="@if(access_is_allowed_to_view("create.allocation")) input-group @endif"><select id="allocation-id-'+counter+'" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
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
    reloadCoaRevenue(counter);
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

function resetForm(index, key){
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
            swal('Failed', 'Something went wrong');
        }
    });
}

function reloadCoaRevenue(counter)
{
    $.ajax({
        url: '{{url("master/coa/ajax/list/position/revenue")}}',
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

/**
 * Generate Cheque Table
 * 
 */
var cheque_table = initDatatable('#cheque-datatable');
var counter_cheque = 0;
$('#addChequeRow').on('click', function () {
    cheque_table.row.add([
        '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
        '<input type="text" id="bank-' + counter_cheque + '" name="bank[]" class="form-control">',
        '<input type="text" name="form_date_cheque[]" id="form-date-cheque-' + counter_cheque + '" class="form-control date input-datepicker"'
           + 'data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"'
           + 'value="{{ date(date_format_get(), strtotime(\Carbon::now())) }}">',
        '<input type="text" name="due_date_cheque[]" id="due-date-cheque-' + counter_cheque + '" class="form-control date input-datepicker"'
           + 'data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"'
           + 'value="{{ date(date_format_get(), strtotime(\Carbon::now())) }}">',
        '<input type="text" name="number_cheque[]" id="number-cheque-'+counter_cheque+'" value="" class="form-control">',
        '<input type="text" name="notes_cheque[]" id="notes-cheque-'+counter_cheque+'" value="" class="form-control">',
        '<input type="text" id="amount-cheque-' + counter_cheque + '" name="amount_cheque[]" class="form-control text-right format-price-alt  row-total-cheque calculate-cheque" value="0" />',
    ]).draw(false);

    initFormatNumber();

    $('.calculate-cheque').keyup(function () {
        calculateCheque();
    });
    counter_cheque++;
});

$('#cheque-datatable tbody').on('click', '.remove-row', function () {
    cheque_table.row($(this).parents('tr')).remove().draw();
    calculateCheque();
});

function calculateCheque() {
    var total_cheque = 0;
    for (var i = 0; i < counter_cheque; i++) {
        if ($('#amount-cheque-'+i).length != 0) {
            total_cheque += dbNum($('#amount-cheque-'+i).val());
        }
    }
    $('#total-cheque').val(appNum(total_cheque));
}
</script>
@stop
