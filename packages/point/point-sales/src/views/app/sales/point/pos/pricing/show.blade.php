@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-sales::app/sales/point/pos/pricing/_breadcrumb')
        <li><a href="{{ url('sales/point/pos/pricing') }}">Pricing</a></li>
        <li>Show</li>
    </ul>
    <h2 class="sub-header">Point Of Sales | Pricing</h2>
    @include('point-sales::app.sales.point.pos.pricing._menu')

    <div class="block full">
        <!-- Block Tabs Title -->
        <div class="block-title">
            <ul class="nav nav-tabs" data-toggle="tabs">
                <li class="active"><a href="#block-tabs-home">Form</a></li>
                <li><a href="#block-tabs-settings"><i class="gi gi-settings"></i></a></li>
            </ul>
        </div>
        <!-- END Block Tabs Title -->

        <div class="tab-content">
            <div class="tab-pane active" id="block-tabs-home">
                <div class="form-horizontal form-bordered">
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Used Date</label>
                        <div class="col-md-6 content-show">
                            {{date_format_view($pos_pricing->formulir->form_date)}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Number</label>
                        <div class="col-md-6 content-show">
                            {{$pos_pricing->formulir->form_number}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6 content-show">
                            {{$pos_pricing->formulir->notes}}
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="item-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th rowspan="2" class="text-center">ITEM</th>
                                        <th rowspan="2" class="text-center">COST OF GOOD <br> SALES</th>
                                        @foreach($list_group as $group)
                                            <th colspan="3" class="text-center">{{ $group->name }}</th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        @foreach($list_group as $group)
                                            <th class="text-right">price</th>
                                            <th class="text-right">discount</th>
                                            <th class="text-right">nett</th>
                                        @endforeach
                                    </tr>

                                    </thead>
                                    <tbody>
                                    <?php $counter = 0; ?>
                                    @foreach($list_pricing_detail as $pricing_detail)
                                    <?php
                                    $quantity = 0;
                                    $formulir = Point\Framework\Models\Formulir::find($pos_pricing->formulir_id);
                                    $inventory = Point\Framework\Models\Inventory::where('item_id', $pricing_detail->item_id)->where('form_date', '<=', $formulir->form_date)->get();
                                    $pos_pricing_item = Point\PointSales\Models\Pos\PosPricingItem::where('pos_pricing_id', $pos_pricing->id)->where('item_id', '=', $pricing_detail->item_id)->first();
                                    ?>
                                    @if($inventory && $pos_pricing_item)
                                    <?php
                                        $quantity = $inventory->sum('total_quantity');
                                    ?>
                                        <tr>
                                            <td>{{ $pricing_detail->item->codeName }}</td>
                                            <td class="text-right">{{ number_format_price($pricing_detail->item->averageCostOfSales(date_format_db(date('Y-m-d'),date('H:i:s'))) )}}</td>
                                            @foreach($list_group as $person_group)
                                                <?php
                                                    $price = 0;
                                                    $discount = 0;
                                                    $nett = 0;
                                                    $pos_pricing_item = \Point\PointSales\Models\Pos\PosPricingItem::where('item_id', '=', $pricing_detail->item_id)
                                                        ->where('person_group_id', '=', $person_group->id)
                                                        ->where('pos_pricing_id', '=', $pos_pricing->id)
                                                        ->orderBy('id', 'desc')
                                                        ->first();

                                                    if ($pos_pricing_item) {
                                                        $price = $pos_pricing_item->price;
                                                        $discount = $pos_pricing_item->discount;
                                                        $nett = $price - $price * $discount / 100;
                                                    }
                                                ?>
                                                <td class="text-right">{{ $price ? number_format_price($price, 0) : '' }}</td>
                                                <td class="text-right">{{ $discount ? number_format_price($discount, 0) .' %' : '' }}</td>
                                                <td class="text-right">{{ $nett ? number_format_price($nett, 0) : '' }}</td>
                                            @endforeach
                                        </tr>
                                        @endif
                                    @endforeach
                                    </tbody>
                                </table>
                                {!! $list_pricing_detail->render() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="block-tabs-settings">
                @if($pos_pricing->formulir->form_status == 0)
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Actions</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                @if(formulir_view_cancel($pos_pricing->formulir, 'delete.point.sales.pos.pricing'))
                                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                   onclick="secureCancelForm('{{url('formulir/cancel')}}',
                                           '{{ $pos_pricing->formulir_id }}',
                                           'delete.point.sales.pos.pricing')"><i class="fa fa-times"></i> Cancel</a>
                                @endif
                                @if(auth()->user()->may('read.point.sales.pos.pricing'))
                                    <a onclick="exportExcel({{ $pos_pricing->id }})" id="button-export" class="btn btn-effect-ripple btn-info"><i class="fa fa-external-link"></i> Export Pricing</a>
                                @endif
                            </div>
                            <div id="preloader" style="display:none;margin-top:5px; float: left;position: relative;margin-top: -29px;margin-left: 280px;">
                                <i class="fa fa-spinner fa-spin" style="font-size:24px;"></i>
                                 Loading...
                            </div>
                            <div class="form-group" id="download-file-wrapper" style="display:none;">
                                <div class="col-md-12" style="margin-top:10px">
                                    <div class="well-sm bg-info">
                                        <h4><strong>Information</strong></h4>
                                        <p id="link-download"></p>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </fieldset>
                @endif

                @if(formulir_view_approval($pos_pricing->formulir, 'approval.point.sales.pos.pricing'))
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6">
                                <form action="{{url('inventory/point/transfer-out/'.$pos_pricing->id.'/approve')}}" method="post">
                                    {!! csrf_field() !!}
                                    <input type="text" name="approval_message" class="form-control" placeholder="Message">
                                    <hr/>
                                    <input type="submit" class="btn btn-primary" value="Approve">
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form action="{{url('inventory/point/transfer-out/'.$pos_pricing->id.'/reject')}}" method="post">
                                    {!! csrf_field() !!}
                                    <input type="text" name="approval_message" class="form-control" placeholder="Message">
                                    <hr/>
                                    <input type="submit" class="btn btn-danger" value="Reject">
                                </form>
                            </div>
                        </div>
                    </fieldset>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('scripts')
<script>
    initDatatable('#item-datatable');

    function exportExcel(id) {
        $("#download-file-wrapper").fadeOut();
        $("#preloader").fadeIn();
        $("#button-export").addClass('disabled');
        $.ajax({
            url: '{{url("sales/point/pos/pricing/export")}}',
            data: {id: id},
            success: function (result) {
                if (result.status == 'success') {
                    $("#preloader").fadeOut();
                    $("#button-export").removeClass('disabled');
                    $("#download-file-wrapper").fadeIn();
                    var download_link = 'Your file already to download, please click this link.'
                    +'<br> <a href="{{url()}}/'+result.link+'">Download File</a>';
                    $("#link-download").html(download_link);
                }
            }
        })
    }
</script>
@stop
