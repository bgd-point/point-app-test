@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-accounting::app/accounting/point/cut-off/account/_breadcrumb')
        <li>Show</li>
    </ul>
    <h2 class="sub-header">Cut Off Account</h2>
    @include('point-accounting::app.accounting.point.cut-off.account._menu')

    @include('core::app.error._alert')
    <div class="block full">
        <!-- Block Tabs Title -->
        <div class="block-title">
            <ul class="nav nav-tabs" data-toggle="tabs">
                <li class="active"><a href="#block-tabs-home">Form</a></li>
                <li><a href="#block-tabs-settings"><i class="gi gi-settings"></i></a></li>
            </ul>
        </div>
        <!-- END Block Tabs Title -->

        <!-- Tabs Content -->
        <div class="tab-content">
            <div class="tab-pane active" id="block-tabs-home">
                <div class="form-horizontal form-bordered">
                    <fieldset>
                        <div class="form-group pull-right">
                            <div class="col-md-12">
                            
                                @include('framework::app.include._approval_status_label', [
                                    'approval_status' => $cut_off_account->formulir->approval_status,
                                    'approval_message' => $cut_off_account->formulir->approval_message,
                                    'approval_at' => $cut_off_account->formulir->approval_at,
                                    'approval_to' => $cut_off_account->formulir->approvalTo->name,
                                ])
                                @include('framework::app.include._form_status_label', ['form_status' => $cut_off_account->formulir->form_status])
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div> 
                    </fieldset>

                    <fieldset>
                        @if($revision)
                        <div class="form-group">
                            <label class="col-md-3 control-label">Revision</label>
                            <div class="col-md-6 content-show">
                                {{ $revision }}
                            </div>
                        </div>
                        @endif
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Number</label>
                            <div class="col-md-3 content-show">
                                {{$cut_off_account->formulir->form_number}}
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
                                                <th style="min-width:50px"></th>
                                                <th style="min-width:250px">COA</th>
                                                <th style="min-width:150px" class="text-right">Debit</th>
                                                <th style="min-width:150px" class="text-right">Credit</th>
                                            </tr>
                                        </thead>
                                        <tbody class="">
                                            <?php
                                                $foot_debit = 0;
                                                $foot_credit = 0;
                                                $i = 0;

                                                $mark_warning = '
                                                <span data-toggle="tooltip" data-placement="top" title="" style="overflow: hidden; position: relative;color:red !important" data-original-title="Value not balance or incorrect for place the value">
                                                    <i class="fa fa-warning fa-lg"></i>
                                                </span>';

                                                $space = '<div style="width:21px;height:3px;float:left"></div>';
                                            ?>
                                            
                                            @foreach($list_coa as $coa)
                                            
                                            <?php
                                            $hide = false;
                                            $cut_off_inventory = 0;
                                            $amount_inventory = 0;
                                            $amount_payable = 0;
                                            $amount_receivable = 0;
                                            
                                            $position = Point\Framework\Helpers\JournalHelper::position($coa->id);
                                            
                                            $cut_off_account_detail = Point\PointAccounting\Models\CutOffAccountDetail::where('coa_id', $coa->id)
                                            ->where('cut_off_account_id', $cut_off_account->id)
                                            ->first();

                                            if ($cut_off_account_detail) {
                                                $foot_debit += $cut_off_account_detail->debit;
                                                $foot_credit += $cut_off_account_detail->credit;    
                                            }
                                            
                                            # check data from cut off account inventory
                                            $cut_off_inventory = Point\PointAccounting\Models\CutOffInventoryDetail::joinInventory()
                                            ->joinFormulir()
                                            ->where('formulir.form_date', $cut_off_account->formulir->form_date)
                                            ->where('formulir.formulirable_type', 'Point\PointAccounting\Models\CutOffInventory')
                                            ->where('formulir.form_number', 'like', '%' . 'COI' . '%')
                                            ->whereIn('formulir.form_status',[0, 1])
                                            ->where('point_accounting_cut_off_inventory_detail.coa_id', $coa->id)
                                            ->orderBy('formulir.id', 'desc')
                                            ->first();

                                            if ($cut_off_inventory) {
                                                $amount_inventory = Point\PointAccounting\Models\CutOffInventoryDetail::where('coa_id', $coa->id)->where('cut_off_inventory_id',$cut_off_inventory->cut_off_inventory_id)->sum('amount');
                                            }

                                            # check data from cut off account payable
                                            $cut_off_payable = Point\PointAccounting\Models\CutOffPayableDetail::joinPayable()
                                            ->joinFormulir()
                                            ->where('formulir.form_date', $cut_off_account->formulir->form_date)
                                            ->where('formulir.formulirable_type', 'Point\PointAccounting\Models\CutOffPayable')
                                            ->where('formulir.form_number', 'like', '%' . 'COP' . '%')
                                            ->whereIn('formulir.form_status', [0, 1])
                                            ->where('point_accounting_cut_off_payable_detail.coa_id', $coa->id)
                                            ->orderBy('formulir.id', 'desc')
                                            ->first();

                                            if ($cut_off_payable) {
                                                $amount_payable = Point\PointAccounting\Models\CutOffPayableDetail::where('coa_id', $coa->id)->where('cut_off_payable_id',$cut_off_payable->cut_off_payable_id)->sum('amount');
                                            }

                                            # check data from cut off account receivable
                                            $cut_off_receivable = Point\PointAccounting\Models\CutOffReceivableDetail::joinReceivable()
                                            ->joinFormulir()
                                            ->where('formulir.form_date', $cut_off_account->formulir->form_date)
                                            ->where('formulir.formulirable_type', 'Point\PointAccounting\Models\CutOffReceivable')
                                            ->where('formulir.form_number', 'like', '%' . 'COR' . '%')
                                            ->whereIn('formulir.form_status',[0, 1])
                                            ->where('point_accounting_cut_off_receivable_detail.coa_id', $coa->id)
                                            ->orderBy('formulir.id', 'desc')
                                            ->first();

                                            if ($cut_off_receivable) {
                                                $amount_receivable = Point\PointAccounting\Models\CutOffReceivableDetail::where('coa_id', $coa->id)->where('cut_off_receivable_id',$cut_off_receivable->cut_off_receivable_id)->sum('amount');
                                            }

                                            # check data from cut off account receivable
                                            $cut_off_fixed_assets = Point\PointAccounting\Models\CutOffFixedAssetsDetail::joinFixedAssets()
                                            ->joinFormulir()
                                            ->where('formulir.form_date', $cut_off_account->formulir->form_date)
                                            ->where('formulir.formulirable_type', 'Point\PointAccounting\Models\CutOffFixedAssets')
                                            ->where('formulir.form_number', 'like', '%' . 'COFA' . '%')
                                            ->whereIn('formulir.form_status',[0, 1])
                                            ->where('point_accounting_cut_off_fixed_assets_detail.coa_id', $coa->id)
                                            ->orderBy('formulir.id', 'desc')
                                            ->first();

                                            if ($cut_off_fixed_assets) {
                                                $amount_fixed_assets = Point\PointAccounting\Models\CutOffFixedAssetsDetail::where('coa_id', $coa->id)->where('fixed_assets_id', $cut_off_fixed_assets->fixed_assets_id)->sum('total_price');
                                            }
                                            ?>
                                            <tr>
                                                <td>
                                                    @if($cut_off_inventory)
                                                        <?php $hide = false;?>
                                                        @if($cut_off_account_detail)
                                                            @if(number_format_db($amount_inventory) != number_format_db($cut_off_account_detail->$position))
                                                                <?php echo $mark_warning; ?>
                                                            @else
                                                                <?php echo $space; ?>
                                                            @endif

                                                            @if(($amount_inventory > 0 ) && (!$cut_off_inventory->subledger_id > 0))
                                                            <?php $hide = true; ?>
                                                            @else 
                                                            <?php $hide = false;?>
                                                            @endif

                                                        @endif
                                                        <?php $url = url('accounting/point/cut-off/inventory/load-details-account-inventory');?>
                                                        <a href="javascript:void(0)" class="btn btn-primary {{$hide ? 'hide' : ''}}" onclick="openDetail({{$cut_off_inventory->cut_off_inventory_id}}, {{$coa->id}}, '{{$url}}', {{$i}})"><i class="fa fa-eye"></i></a>
                                                    @elseif($cut_off_payable)
                                                        <?php $hide = false; ?>
                                                        @if($cut_off_account_detail)
                                                            @if(number_format_db($amount_payable) != number_format_db($cut_off_account_detail->$position))
                                                                <?php echo $mark_warning; ?>
                                                            @else
                                                                <?php echo $space; ?>
                                                            @endif

                                                            @if(($amount_payable > 0 ) && (!$cut_off_payable->subledger_id > 0))
                                                            <?php $hide = true; ?>
                                                            @else 
                                                            <?php $hide = false;?>
                                                            @endif
                                                        @endif
                                                        <?php $url = url('accounting/point/cut-off/payable/load-details-account-payable');?>
                                                        <a href="javascript:void(0)" class="btn btn-primary {{$hide ? 'hide' : ''}}" onclick="openDetail({{$cut_off_payable->cut_off_payable_id}}, {{$coa->id}}, '{{$url}}', {{$i}})"><i class="fa fa-eye"></i></a>
                                                    @elseif($cut_off_receivable)
                                                        <?php $hide = false;?>
                                                        @if($cut_off_account_detail)
                                                            @if(number_format_db($amount_receivable) != number_format_db($cut_off_account_detail->$position))
                                                                <?php echo $mark_warning; ?>
                                                            @else
                                                                <?php echo $space; ?>
                                                            @endif

                                                            @if(($amount_receivable > 0 ) && (!$cut_off_receivable->subledger_id > 0))
                                                            <?php $hide = true; ?>
                                                            @else 
                                                            <?php $hide = false;?>
                                                            @endif
                                                        @endif
                                                        <?php $url = url('accounting/point/cut-off/receivable/load-details-account-receivable');?>
                                                        <a href="javascript:void(0)" class="btn btn-primary {{$hide ? 'hide' : ''}}" onclick="openDetail({{$cut_off_receivable->cut_off_receivable_id}}, {{$coa->id}}, '{{$url}}', {{$i}})"><i class="fa fa-eye"></i></a>
                                                    @elseif($cut_off_fixed_assets)
                                                        @if($cut_off_account_detail)
                                                            @if(number_format_db($amount_fixed_assets) != number_format_db($cut_off_account_detail->$position))
                                                                <?php echo $mark_warning; ?>
                                                            @else
                                                                <?php echo $space; ?>
                                                            @endif
                                                        @endif
                                                        <?php $url = url('accounting/point/cut-off/fixed-assets/load-details-account-fixed-assets');?>
                                                        <a href="javascript:void(0)" class="btn btn-primary" onclick="openDetail({{$cut_off_fixed_assets->fixed_assets_id}}, {{$coa->id}}, '{{$url}}', {{$i}})"><i class="fa fa-eye"></i></a>
                                                    @endif

                                                </td>
                                                <td><strong id="coa-name-{{$i}}">{{$coa->account}}</strong></td>
                                                <td>
                                                    @if($cut_off_account_detail)
                                                        <input type="text" class="form-control format-accounting text-right" readonly value="{{$cut_off_account_detail->debit}}">
                                                    @else
                                                        <input type="text" class="form-control format-accounting text-right" readonly value="0">
                                                    @endif

                                                </td>
                                                <td>
                                                    @if($cut_off_account_detail)
                                                        <input type="text" class="form-control format-accounting text-right" readonly value="{{$cut_off_account_detail->credit}}">
                                                    @else
                                                        <input type="text" class="form-control format-accounting text-right" readonly value="0">
                                                    @endif

                                                </td>
                                            </tr>
                                            <?php $i++;?>
                                            
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="2"></td>
                                                <td align="right"><input type="text" value="{{$foot_debit}}" style="font-weight:bold; font-size:16px" readonly name="foot_debit" id="foot_debit" class="form-control format-accounting text-right" /></td>
                                                <td align="right"><input type="text" value="{{$foot_credit}}" style="font-weight:bold; font-size:16px" readonly name="foot_credit" id="foot_credit" class="form-control format-accounting text-right"/></td>
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
            <div class="tab-pane" id="block-tabs-settings">
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Action</legend>                    
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            @if(!$cut_off_account->formulir->form_status == 1)
                                @if(formulir_view_edit($cut_off_account->formulir, 'update.point.accounting.cut.off.account'))
                                <a href="{{url('accounting/point/cut-off/account/'.$cut_off_account->id.'/edit')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                @endif
                                @if(formulir_view_cancel($cut_off_account->formulir, 'delete.point.accounting.cut.off.account'))
                                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" 
                                   onclick="secureCancelForm('{{url('accounting/point/cut-off/account/cancel')}}', {{$cut_off_account->formulir->id}},
                                   'delete.point.accounting.cut.off.account')"><i class="fa fa-times"></i> cancel</a>
                               @endif
                           @endif
                        </div>
                    </div>
                </fieldset>
               

                @if(formulir_view_approval($cut_off_account->formulir, 'approval.point.accounting.cut.off.account'))
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Approval Actions</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6">
                                <form action="{{url('accounting/point/cut-off/account/'.$cut_off_account->id.'/approve')}}" method="post">
                                    {!! csrf_field() !!}
                                    <input type="text" name="approval_message" class="form-control" placeholder="Message">
                                    <hr/>
                                    <input type="submit" class="btn btn-primary" value="Approve">
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form action="{{url('accounting/point/cut-off/account/'.$cut_off_account->id.'/reject')}}" method="post">
                                    {!! csrf_field() !!}
                                    <input type="text" name="approval_message" class="form-control" placeholder="Message">
                                    <hr/>
                                    <input type="submit" class="btn btn-danger" value="Reject">
                                </form>
                            </div>
                        </div>
                    </fieldset>
                @endif

                @if($list_cut_off_account_archived->count() > 0)
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Archived Form</legend>                    
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12 content-show">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th></th> 
                                            <th>Date</th>
                                            <th>Number</th>
                                            <th>Created By</th>
                                            <th>Updated By</th>
                                            <th>Edit Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $count=0;?>
                                        @foreach($list_cut_off_account_archived as $cut_off_account_archived)
                                        <tr>
                                            <td class="text-center">
                                                <a href="{{ url('accounting/point/cut-off/account/'.$cut_off_account_archived->id.'/archived') }}" data-toggle="tooltip" title="Show" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-file"></i> {{ 'Revision ' . $count++ }}</a>
                                            </td>
                                            <td>{{ date_format_view($cut_off_account_archived->formulir->form_date) }}</td>
                                            <td>{{ $cut_off_account_archived->formulir->archived }}</td>
                                            <td>{{ $cut_off_account_archived->formulir->createdBy->name }}</td>
                                            <td>{{ $cut_off_account_archived->formulir->updatedBy->name }}</td>
                                            <td>{{ $cut_off_account_archived->formulir->edit_notes }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody> 
                                </table>
                            </div>
                        </div>
                    </div>
                </fieldset>
                @endif
            </div>
        </div>
    </div>
</div>
@include('point-accounting::app.accounting.point.cut-off.account._details')
@stop

@section('scripts')
<script type="text/javascript">

function openDetail(account_id, coa_id, url, index) {
    var coa = $("#coa-name-"+index).html();
    $("#modal-detail").modal();
    $.ajax({
        url: url,
        type: 'get',
        data: {
            coa_id: coa_id,
            cut_off_id : account_id,
        },
        success: function(data) {
            $("#data-details").html(data);
            $("#modal-coa-name").html(coa);
        },
        error: function(data) { console.log(data.status); }
    });
}

</script>
@stop
