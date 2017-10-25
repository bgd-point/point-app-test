@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-accounting::app/accounting/point/cut-off/payable/_breadcrumb')
        <li>Create</li>
    </ul>
    <h2 class="sub-header">Cut Off Account Payable</h2>
    @include('point-accounting::app.accounting.point.cut-off.payable._menu')

    @include('core::app.error._alert')
    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('accounting/point/cut-off/payable')}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}
                <input name="action" type="hidden" value="create">
                
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Create</legend>
                        </div>
                    </div> 
                </fieldset>

                <fieldset>
                <div class="form-group">
                    <label class="col-md-3 control-label">Date *</label>
                    <div class="col-md-3">
                        <input type="text" name="form_date" id="form-date" onchange="reloadDefaultValue(this.value)" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{\Input::get('date') ? \Input::get('date') : date(date_format_get(), strtotime(\Carbon::now()))}}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>
                    <div class="col-md-6">
                        <input name="notes" id="notes" class="form-control" value="{{ old('notes') }}" />
                    </div>
                </div>
                    
                </fieldset>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Details</legend>
                        </div>
                    </div>
                </fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive"  style="overflow-x:visible">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th >COA</th>
                                            <th >Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                        <?php
                                            $index = 0;
                                            $form_date = date('d-m-y');
                                            if (\Input::get('date')) {
                                                $form_date = \Input::get('date');
                                            }
                                        ?>
                                        
                                        @foreach($list_coa as $coa)

                                        <?php
                                            $amount = 0;
                                            $value_of_account = 0;

                                            $temp = Point\Core\Helpers\TempDataHelper::getAllRowHaveKeyValue('cut.off.payable', auth()->user()->id, 'coa_id', $coa->id);
                                            foreach ($temp as $temp_payable) {
                                                $amount += $temp_payable['amount'];
                                            }

                                            $position = Point\Framework\Helpers\JournalHelper::position($coa->id);
                                            $value_of_account = Point\Framework\Models\Journal::where('coa_id', $coa->id)->where('form_date', '<=', date_format_db($form_date, 'end'))->sum($position) ? : 0;
                                        ?>

                                        <tr>
                                            <td>
                                                <input type="hidden" name="coa_id[]" id="coa-id-{{$index}}" value="{{$coa->id}}"> 
                                                @if($coa->has_subledger)
                                                <a href="javascript:void(0)" class="btn btn-primary" onclick="openDetail({{$coa->id}},{{$index}})"><i class="fa fa-plus"></i></a>
                                                @endif
                                            </td>
                                            <td><strong id="coa-name-{{$index}}">{{$coa->account}}</strong></td>
                                            <td>
                                                <input type="text" onkeyup="reCalculate()" id="row-amount-{{$index}}" value="{{ $amount ? : $value_of_account }}" name="amount[]" @if($coa->has_subledger) '' : readonly @endif class="form-control text-right format-quantity">
                                            </td>
                                            
                                        </tr>
                                        <?php $index++;?>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2"></td>
                                            <td align="right"><input type="text" readonly name="foot_amount" id="foot_amount" class="form-control format-quantity text-right" /></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                <div class="form-group">
                    <label class="col-md-3 control-label">Approval To *</label>
                    <div class="col-md-6">
                        <select name="approval_to" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                            @foreach($list_user_approval as $user_approval)
                                @if($user_approval->may('approval.point.accounting.cut.off.receivable'))
                                    <option value="{{$user_approval->id}}" @if(old('approval_to') == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6 col-md-offset-3">
                        <button type="submit" class="btn btn-effect-ripple btn-primary">Submit</button>
                        
                            <a href="{{url('accounting/point/cut-off/payable/clear-tmp')}}" class="btn btn-effect-ripple btn-danger" data-toggle="modal">CLEAR</a>
                        
                    </div>
                </div>
            </form>
        </div>
    </div>  
</div>
@include('point-accounting::app.accounting.point.cut-off.payable._details')
@stop

@section('scripts')
<script type="text/javascript">

function openDetail(coa_id, index) {
    var coa = $("#coa-name-"+index).html();
    $("#modal-detail").modal();
    $("#index").val(index);
    $("#modal-coa-id").val(coa_id);
    $("#modal-body-cutoff").html("loading..");
    $.ajax({
        url: '{{url("accounting/point/cut-off/payable/load-details")}}',
        type: 'get',
        data: {
            coa_id: coa_id,
        },
        success: function(data) {
            $("#modal-body-cutoff-payable").html(data);
            $("#modal-coa-name-cutoff-payable").html("<strong>"+coa+"</strong>");
            calculate();
        },
        error: function(data) { console.log(data.status); }
    });
}

function reCalculate() {
    var rows_length = {{count($list_coa)}};
    var total_amount = 0;
    for(var i=0; i<rows_length; i++) {
        var amount = dbNum($('#row-amount-'+i).val());
        total_amount += amount;
    }

    $('#foot_amount').val(accountingNum(total_amount));
}

function reloadDefaultValue(value) {
    location.href = '{{url("accounting/point/cut-off/payable/create/?date=")}}'+value;
}

$(function() {
    reCalculate();
});
</script>
@stop
