@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-accounting::app/accounting/point/cut-off/account/_breadcrumb')
        <li>Create</li>
    </ul>
    <h2 class="sub-header">Cut Off Account</h2>
    @include('point-accounting::app.accounting.point.cut-off.account._menu')

    @include('core::app.error._alert')
    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('accounting/point/cut-off/account')}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}
                <input name="action" type="hidden" value="create">

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
                                            <th >COA</th>
                                            <th >Debit</th>
                                            <th >Credit</th>
                                        </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                        <?php

                                        $temp = Point\Core\Helpers\TempDataHelper::get('cut.off', auth()->user()->id);
                                        $index = 0;
                                        $credit = false;
                                        $debit = false;
                                        $total = 0;

                                        $form_date = date('d-m-y');
                                        if (\Input::get('date')) {
                                            $form_date = \Input::get('date');
                                        }
                                        ?>
                                        
                                        @foreach($list_coa as $coa)
                                        <?php

                                            $subledger_type_person = get_class(new Point\Framework\Models\Master\Person);
                                            $subledger_type_fixed_asset = get_class(new \Point\Framework\Models\FixedAsset);
                                            $coa_credit = Point\Core\Helpers\TempDataHelper::searchKeyValue('cut.off', auth()->user()->id, ['coa_id','position'], [$coa->id, 'credit']);
                                            $coa_debit = Point\Core\Helpers\TempDataHelper::searchKeyValue('cut.off', auth()->user()->id, ['coa_id','position'], [$coa->id,'debit']);
                                            $position = \Point\Framework\Helpers\JournalHelper::position($coa->id);

                                            // Get the last value of account
                                            $debit_value_of_account = Point\Framework\Models\Journal::where('coa_id', $coa->id)->where('form_date', '<=', date_format_db($form_date, 'end'))->sum('debit') ? : 0;
                                            $credit_value_of_account = Point\Framework\Models\Journal::where('coa_id', $coa->id)->where('form_date', '<=', date_format_db($form_date, 'end'))->sum('credit') ? : 0;

                                            if ($debit_value_of_account >= $credit_value_of_account) {
                                                $total = $debit_value_of_account - $credit_value_of_account;
                                            } else {
                                                $total = $credit_value_of_account - $debit_value_of_account;
                                            }
                                            
                                        ?>
                                        <tr>
                                            <td>{{$coa->account}}</td>
                                            <td>
                                                @if($coa_debit)
                                                    <input type="text" onkeyup="updateTemp({{$index}}, 'debit')" id="debit-{{$index}}" value="{{ $coa_debit['amount']}}" name="debit[]" class="form-control text-right format-quantity">
                                                @else
                                                    <input type="text" onkeyup="updateTemp({{$index}}, 'debit')" id="debit-{{$index}}" name="debit[]"class="form-control text-right format-quantity" value={{ $position == 'debit' ? $total : 0 }}>
                                                @endif
                                            </td>
                                            <td>
                                                @if($coa_credit)
                                                    <input type="text" onkeyup="updateTemp({{$index}}, 'credit')" id="credit-{{$index}}" value="{{$coa_credit['amount']}}" name="credit[]" class="form-control text-right format-quantity">
                                                @else
                                                    <input type="text" onkeyup="updateTemp({{$index}}, 'credit')" id="credit-{{$index}}" value="{{ $position == 'credit' ? $total : 0 }}" name="credit[]" class="form-control text-right format-quantity">
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
                        <a href="{{url('accounting/point/cut-off/account/clear-tmp')}}" class="btn btn-effect-ripple btn-danger" data-toggle="modal">CLEAR</a>
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
    if(position == 'credit'){
        $('#debit-'+index).val(0);
    }else{
        $('#credit-'+index).val(0);
    }
    $.ajax({
        url: '{{url("accounting/point/cut-off/account/store-tmp-details")}}',
        type: 'post',
        data: {
            coa_id: $('#coa-id-'+index).val(),
            amount: dbNum($('#'+position+'-'+index).val()),
            position: position,
        },
        success: function(data) {},
        error: function(data) { console.log(data.status); }
    });

    reCalculate();
}

function reloadDefaultValue(value) {
    location.href = '{{url("accounting/point/cut-off/account/create/?date=")}}'+value;
}

$(function() {
    reCalculate();
});
</script>
@stop
