@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-accounting::app/accounting/point/cut-off/receivable/_breadcrumb')
        <li>Edit</li>
    </ul>
    <h2 class="sub-header">Cut Off Account Receivable</h2>
    @include('point-accounting::app.accounting.point.cut-off.receivable._menu')

    @include('core::app.error._alert')
    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('accounting/point/cut-off/receivable/'.$cut_off_receivable->formulir_id)}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}
                <input name="action" type="hidden" value="edit">
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

                <fieldset>
                <div class="form-group">
                    <label class="col-md-3 control-label">Date *</label>
                    <div class="col-md-3">
                        <input type="text" name="form_date" id="form_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{date(date_format_get(), strtotime($cut_off_receivable->formulir->form_date))}}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>
                    <div class="col-md-6">
                        <input name="notes" id="notes" class="form-control" value="{{ $cut_off_receivable->formulir->notes }}" />
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
                                        ?>
                                        
                                        @foreach($list_coa as $coa)

                                        <?php
                                            $amount = 0;
                                            $temp = Point\Core\Helpers\TempDataHelper::getAllRowHaveKeyValue('cut.off.receivable', auth()->user()->id, 'coa_id', $coa->id);
                                            foreach ($temp as $temp_receivable) {
                                                $amount += $temp_receivable['amount'];
                                            }

                                            if (!$amount) {
                                                $receivable_detail_coa = Point\PointAccounting\Models\CutOffReceivableDetail::where('cut_off_receivable_id', $cut_off_receivable->id)->where('coa_id', $coa->id)->first();
                                                if ($receivable_detail_coa) {
                                                    $amount = Point\PointAccounting\Models\CutOffReceivableDetail::where('cut_off_receivable_id', $cut_off_receivable->id)->where('coa_id', $coa->id)->first()->amount;
                                                }
                                            }
                                        ?>

                                        <tr>
                                            <td>
                                                <input type="hidden" name="coa_id[]" id="coa-id-{{$index}}" value="{{$coa->id}}"> 
                                                @if($coa->has_subledger)
                                                <a href="javascript:void(0)" class="btn btn-primary" onclick="openDetail({{$coa->id}},{{$index}})"><i class="fa fa-plus"></i></a>
                                                @endif
                                            </td>
                                            <td><strong id="coa-name-{{$index}}">{{$coa->account}}</strong></td>
                                            <td><input onkeyup="reCalculate()" type="text" id="row-amount-{{$index}}" value="{{ $amount }}" name="amount[]" @if($coa->has_subledger) '' : readonly @endif class="form-control text-right format-quantity"></td>
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
                        
                            <a href="{{url('accounting/point/cut-off/account/clear-tmp')}}" class="btn btn-effect-ripple btn-danger" data-toggle="modal">CLEAR</a>
                        
                    </div>
                </div>
            </form>
        </div>
    </div>  
</div>
@include('point-accounting::app.accounting.point.cut-off.receivable._details')
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
        url: '{{url("accounting/point/cut-off/receivable/load-details")}}',
        type: 'get',
        data: {
            coa_id: coa_id,
        },
        success: function(data) {
            $("#modal-body-cutoff-receivable").html(data);
            $("#modal-coa-name-cutoff-receivable").html("<strong>"+coa+"</strong>");
            calculate();
            initFormatNumber();
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

$(function() {
    reCalculate();
});

</script>
@stop
