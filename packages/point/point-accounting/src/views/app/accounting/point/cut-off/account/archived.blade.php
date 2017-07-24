@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-accounting::app/accounting/point/cut-off/account/_breadcrumb')
        <li>Archived</li>
    </ul>
    <h2 class="sub-header">Cut Off Account</h2>
    @include('point-accounting::app.accounting.point.cut-off.account._menu')

    @include('core::app.error._alert')
    <div class="panel panel-default"> 
        <div class="panel-body">
            <div class="form-horizontal form-bordered">
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="alert alert-danger alert-dismissable">
                            <h1 class="text-center"><strong>Archived</strong></h1>
                        </div>
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
                        <label class="col-md-3 control-label">Form Number</label>
                        <div class="col-md-3 content-show">
                            {{$cut_off_account->formulir->archived}}
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">Date</label>
                        <div class="col-md-3 content-show">
                            {{date_format_view($cut_off_account->formulir->form_date)}}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6 content-show">
                            {{$cut_off_account->formulir->notes}}
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
                                        $foot_debit = 0;
                                        $foot_credit = 0;
                                        $i = 0;
                                    ?>
                                    @foreach($list_coa as $coa)
                                    <?php
                                    $cut_off_account_detail = Point\PointAccounting\Models\CutOffAccountDetail::where('coa_id', $coa->id)
                                    ->where('cut_off_account_id',$cut_off_account->id)
                                    ->first();
                                    
                                    if($cut_off_account_detail){
                                        $foot_debit += $cut_off_account_detail->debit;
                                        $foot_credit += $cut_off_account_detail->credit;    
                                        
                                    }
                                    ?>
                                    <tr>
                                        <td><strong>{{$coa->name}}</strong></td>
                                        <td>
                                            @if($cut_off_account_detail)
                                                <input type="text" class="form-control format-quantity text-right" readonly value="{{$cut_off_account_detail->debit}}">
                                            @else
                                                <input type="text" class="form-control format-quantity text-right" readonly value="0">
                                            @endif

                                        </td>
                                        <td>
                                            @if($cut_off_account_detail)
                                                <input type="text" class="form-control format-quantity text-right" readonly value="{{$cut_off_account_detail->credit}}">
                                            @else
                                                <input type="text" class="form-control format-quantity text-right" readonly value="0">
                                            @endif

                                        </td>
                                    </tr>
                                    <?php $i++;?>
                                    
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td></td>
                                        <td align="right"><input type="text" value="{{$foot_debit}}" readonly name="foot_debit" id="foot_debit" class="form-control format-quantity text-right" /></td>
                                        <td align="right"><input type="text" value="{{$foot_credit}}" readonly name="foot_credit" id="foot_credit" class="form-control format-quantity text-right"/></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Person In Charge</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Creator</label>
                        <div class="col-md-6 content-show">
                            {{ $cut_off_account->formulir->createdBy->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Approval To</label>
                        <div class="col-md-6 content-show">
                            {{ $cut_off_account->formulir->approvalTo->name }}
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
    
</div>
@stop
