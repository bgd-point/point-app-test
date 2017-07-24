@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-accounting::app/accounting/point/cut-off/account/_breadcrumb')
        <li>Edit</li>
    </ul>
    <h2 class="sub-header">Cut Off Account</h2>
    @include('point-accounting::app.accounting.point.cut-off.account._menu')

    @include('core::app.error._alert')
    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('accounting/point/cut-off/account/'.$cut_off_account->formulir->id)}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}
                <input name="_method" type="hidden" value="PUT">
                <input name="action" type="hidden" value="edit">
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
                        <div class="col-md-6">
                            <input type="text" name="form_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{date(date_format_get(), strtotime($cut_off_account->formulir->form_date))}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6">
                            <input name="notes" id="notes" class="form-control" value="{{ $cut_off_account->formulir->notes }}" />
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
                                        <th>COA</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                    </tr>
                                </thead>
                                <tbody class="manipulate-row">
                                    <?php
                                        $temp = Point\Core\Helpers\TempDataHelper::get('cut.off', auth()->user()->id);
                                        $index = 0;
                                        $credit = false;
                                        $debit = false;
                                        $amount = 0;
                                    ?>
                                    @foreach($list_coa as $coa)
                                    <?php
                                        $coa_credit = Point\Core\Helpers\TempDataHelper::searchKeyValue('cut.off', auth()->user()->id, ['coa_id','position'], [$coa->id, 'credit']);
                                        $coa_debit = Point\Core\Helpers\TempDataHelper::searchKeyValue('cut.off', auth()->user()->id, ['coa_id','position'], [$coa->id,'debit']);
                                    ?>
                                    <tr>
                                        <td><strong id="coa-name-{{$index}}">{{$coa->name}}</strong></td>
                                        <td>
                                            @if($coa_debit)
                                                <input type="text" onChange="updateTemp({{$index}}, 'debit')" id="debit-{{$index}}" value="{{ $coa_debit['amount']}}" name="debit[]" class="form-control text-right format-quantity">
                                            @else
                                                <input type="text" onChange="updateTemp({{$index}}, 'debit')" id="debit-{{$index}}" name="debit[]" class="form-control text-right format-quantity" value=0>
                                            @endif
                                        </td>
                                        <td>
                                            @if($coa_credit)
                                                <input type="text" onChange="updateTemp({{$index}}, 'credit')" id="credit-{{$index}}" value="{{$coa_credit['amount']}}" name="credit[]" class="form-control text-right format-quantity">
                                            @else
                                                <input type="text" onChange="updateTemp({{$index}}, 'credit')" id="credit-{{$index}}" value="0" name="credit[]" class="form-control text-right format-quantity">
                                            @endif
                                        </td>
                                    </tr>
                                    <input type="hidden" name="coa_id[]" id="coa-id-{{$index}}" value="{{$coa->id}}"> 
                                    <?php $index++;?>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td></td>
                                        <td align="right"><input type="text" readonly name="foot_debit" id="foot_debit" class="form-control format-quantity text-right" /></td>
                                        <td align="right"><input type="text" readonly name="foot_credit" id="foot_credit" class="form-control format-quantity text-right"/></td>
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
                                @if($user_approval->may('approval.point.accounting.cut.off.account'))
                                    <option value="{{$user_approval->id}}" @if(old('approval_to') == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6 col-md-offset-3">
                        <button type="submit" class="btn btn-effect-ripple btn-primary">Submit</button>
                        <a href="{{url('accounting/point/cut-off/account/clear-tmp')}}" class="btn btn-effect-ripple btn-danger" data-toggle="modal">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>  
</div>

@stop

@section('scripts')
<script type="text/javascript">

function reCalculate() {
    var rows_length = {{count($list_coa)}};
    var totalCredit = 0;
    var totalDebit = 0;
    for(var i=0; i<rows_length; i++) {
        var credit = dbNum($('#credit-'+i).val());
        var debit = dbNum($('#debit-'+i).val());

        totalCredit += credit;
        totalDebit += debit;

    }

    $('#foot_debit').val(accountingNum(totalDebit));
    $('#foot_credit').val(accountingNum(totalCredit));
}

function updateTemp(index, position){
    
    $.ajax({
        url: '{{url("accounting/point/cut-off/account/store-tmp-details")}}',
        type: 'post',
        data: {
            coa_id: $('#coa-id-'+index).val(),
            amount: dbNum($('#'+position+'-'+index).val()),
            position: position,
        },
        success: function(data) { console.log(data.status); },
        error: function(data) { console.log(data.status); }
    });

    reCalculate();
}

$(function() {
    reCalculate();
});
</script>
@stop
