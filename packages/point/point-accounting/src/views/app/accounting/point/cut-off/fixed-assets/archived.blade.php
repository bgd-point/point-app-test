@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-accounting::app/accounting/point/cut-off/fixed-assets/_breadcrumb')
        <li>Archived</li>
    </ul>
    <h2 class="sub-header">Cut Off Account FixedAssets</h2>
    @include('point-accounting::app.accounting.point.cut-off.fixed-assets._menu')

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
                            {{$cut_off_fixed_assets_archived->formulir->archived}}
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">Date</label>
                        <div class="col-md-3 content-show">
                            {{date_format_view($cut_off_fixed_assets_archived->formulir->form_date)}}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6 content-show">
                            {{$cut_off_fixed_assets_archived->formulir->notes}}
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
                                        $amount = Point\PointAccounting\Models\CutOffFixedAssetsDetail::getAmountDepreciation($coa->id, $cut_off_fixed_assets_archived->id);
                                        $total_amount += $amount;
                                    ?>
                                    
                                    <tr>
                                        <td>
                                            @if($coa->has_subledger)
                                                @if($amount)
                                                    <a href="javascript:void(0)" class="btn btn-primary" onclick="openDetail({{$cut_off_fixed_assets_archived->id}},{{$coa->id}}, {{$i}})"><i class="fa fa-eye"></i></a>
                                                @endif
                                            @endif
                                        </td>
                                        <td><strong id="coa-name{{$i}}">{{$coa->name}}</strong></td>
                                        <td><input type="text" class="form-control format-accounting text-right" readonly value="{{$amount}}"></td>
                                    </tr>
                                    <?php $i++;?>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td align="right"><input type="text" value="{{$total_amount}}" readonly class="form-control format-accounting text-right" /></td>
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
                            {{ $cut_off_fixed_assets_archived->formulir->createdBy->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Approval To</label>
                        <div class="col-md-6 content-show">
                            {{ $cut_off_fixed_assets_archived->formulir->approvalTo->name }}
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
    
</div>
@include('point-accounting::app.accounting.point.cut-off.fixed-assets._details')
@stop

@section('scripts')
<script type="text/javascript">

function openDetail(cut_off_fixed_assets_id, coa_id, index) {
    coa = $("#coa-name"+index).html();
    $("#modal-detail").modal();
    $("#modal-body-cutoff").html("loading..");
    html = '<button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>';
    $("#modal-footer").html(html);
    $.ajax({
        url: '{{url("accounting/point/cut-off/fixed-assets/load-details-account-fixed-assets")}}',
        type: 'get',
        data: {
            coa_id: coa_id,
            fixed_assets_id : cut_off_fixed_assets_id,
        },
        success: function(data) {
            $("#modal-body-cutoff-fixed-assets").html(data);
            $("#modal-coa-name-cutoff-fixed-assets").html(coa);
        },
        error: function(data) { console.log(data.status); }
    });
}

</script>
@stop
