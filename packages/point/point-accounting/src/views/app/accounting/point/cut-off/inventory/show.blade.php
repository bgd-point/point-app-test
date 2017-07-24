@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-accounting::app/accounting/point/cut-off/inventory/_breadcrumb')
        <li>Show</li>
    </ul>
    <h2 class="sub-header">Cut Off Account Inventory</h2>
    @include('point-accounting::app.accounting.point.cut-off.inventory._menu')

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
                                    'approval_status' => $cut_off_inventory->formulir->approval_status,
                                    'approval_message' => $cut_off_inventory->formulir->approval_message,
                                    'approval_at' => $cut_off_inventory->formulir->approval_at,
                                    'approval_to' => $cut_off_inventory->formulir->approvalTo->name,
                                ])
                                @include('framework::app.include._form_status_label', ['form_status' => $cut_off_inventory->formulir->form_status])
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
                                {{$cut_off_inventory->formulir->form_number}}
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-3 control-label">Date</label>
                            <div class="col-md-3 content-show">
                                {{date_format_view($cut_off_inventory->formulir->form_date)}}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>
                            <div class="col-md-6 content-show">
                                {{$cut_off_inventory->formulir->notes}}
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
                                            $i = 0;
                                            $total_amount = 0;
                                            ?>
                                            @foreach($list_coa as $coa)
                                            <?php
                                            $amount = Point\PointAccounting\Models\CutOffInventoryDetail::where('coa_id', $coa->id)->where('cut_off_inventory_id',$cut_off_inventory->id)->sum('amount');
                                            $total_amount += $amount;
                                            ?>
                                            <tr>
                                                <td>
                                                @if($coa->has_subledger)
                                                    @if($amount)
                                                    <a href="javascript:void(0)" class="btn btn-primary" onclick="openDetail({{$cut_off_inventory->id}},{{$coa->id}}, {{$i}})"><i class="fa fa-eye"></i></a>
                                                    @endif
                                                @endif
                                                </td>
                                                <td><strong id="coa-name{{$i}}">{{$coa->name}}</strong></td>
                                                <td><input type="text" id="debit-{{$i}}" class="form-control format-quantity text-right" readonly value="{{$amount}}"></td>
                                                
                                            </tr>
                                            <?php $i++;?>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="2"></td>
                                                <td align="right"><input type="text" value="{{$total_amount}}" readonly class="form-control format-quantity text-right" /></td>
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
                                {{ $cut_off_inventory->formulir->createdBy->name }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Approval To</label>
                            <div class="col-md-6 content-show">
                                {{ $cut_off_inventory->formulir->approvalTo->name }}
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
                            @if(formulir_view_edit($cut_off_inventory->formulir, 'update.point.accounting.cut.off.inventory'))
                            <a href="{{url('accounting/point/cut-off/inventory/'.$cut_off_inventory->id.'/edit')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                            @endif
                            @if(formulir_view_cancel($cut_off_inventory->formulir, 'delete.point.accounting.cut.off.inventory'))
                            <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" 
                               onclick="secureCancelForm('{{url('accounting/point/cut-off/inventory/cancel')}}', {{$cut_off_inventory->formulir->id}},
                               'delete.point.accounting.cut.off.inventory')"><i class="fa fa-times"></i> cancel</a>
                           @endif
                        </div>
                    </div>
                </fieldset>

                @if(formulir_view_approval($cut_off_inventory->formulir, 'approval.point.accounting.cut.off.inventory'))
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Approval Actions</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6">
                                <form action="{{url('accounting/point/cut-off/inventory/'.$cut_off_inventory->id.'/approve')}}" method="post">
                                    {!! csrf_field() !!}
                                    <input type="text" name="approval_message" class="form-control" placeholder="Message">
                                    <hr/>
                                    <input type="submit" class="btn btn-primary" value="Approve">
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form action="{{url('accounting/point/cut-off/inventory/'.$cut_off_inventory->id.'/reject')}}" method="post">
                                    {!! csrf_field() !!}
                                    <input type="text" name="approval_message" class="form-control" placeholder="Message">
                                    <hr/>
                                    <input type="submit" class="btn btn-danger" value="Reject">
                                </form>
                            </div>
                        </div>
                    </fieldset>
                @endif

                @if($list_cut_off_inventory_archived->count() > 0)
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
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $count=0;?>
                                        @foreach($list_cut_off_inventory_archived as $cut_off_archived)
                                        <tr>
                                            <td class="text-center">
                                                <a href="{{ url('accounting/point/cut-off/inventory/'.$cut_off_archived->formulir_id.'/archived') }}" data-toggle="tooltip" title="Show" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-file"></i> {{ 'Revision ' . $count++ }}</a>
                                            </td>
                                            <td>{{ date_format_view($cut_off_inventory->formulir->form_date) }}</td>
                                            <td>{{ $cut_off_archived->formulir->archived }}</td>
                                            <td>{{ $cut_off_archived->formulir->createdBy->name }}</td>
                                            <td>{{ $cut_off_archived->formulir->updatedBy->name }}</td>
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
@include('point-accounting::app.accounting.point.cut-off.inventory._details')
@stop

@section('scripts')
<script type="text/javascript">

function openDetail(cut_off_inventory_id, coa_id, index) {
    coa = $("#coa-name"+index).html();
    $("#modal-detail").modal();
    $("#modal-body-cutoff").html("loading..");
    html = '<button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>';
    $("#modal-footer").html(html);
    $.ajax({
        url: '{{url("accounting/point/cut-off/inventory/load-details-account-inventory")}}',
        type: 'get',
        data: {
            coa_id: coa_id,
            cut_off_id : cut_off_inventory_id,
        },
        success: function(data) {
            $("#modal-body-cutoff-inventory").html(data);
            $("#modal-coa-name-cutoff-inventory").html(coa);
        },
        error: function(data) { console.log(data.status); }
    });
}

</script>
@stop
