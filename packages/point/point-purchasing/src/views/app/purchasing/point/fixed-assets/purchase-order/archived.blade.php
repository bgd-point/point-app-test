@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.fixed-assets._breadcrumb')
            <li><a href="{{ url('purchasing/point/fixed-assets/purchase-order') }}">Purchase Order</a></li>
            <li>Archived</li>
        </ul>
        <h2 class="sub-header">Purchase Order</h2>
        @include('point-purchasing::app.purchasing.point.fixed-assets.purchase-order._menu')

        @include('core::app.error._alert')

        <div class="block full">
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
                <div class="form-group">
                    <label class="col-md-3 control-label">Form Number</label>

                    <div class="col-md-6 content-show">
                        {{ $purchase_order_archived->formulir->archived }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Form Date</label>

                    <div class="col-md-6 content-show">
                        {{ date_format_view($purchase_order_archived->formulir->form_date, true) }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Supplier</label>

                    <div class="col-md-6 content-show">
                        {{ $purchase_order_archived->supplier->codeName }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>

                    <div class="col-md-6 content-show">
                        {{ $purchase_order_archived->formulir->notes }}
                    </div>
                </div>
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Item</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="item-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th style="min-width:220px">Asset Account</th>
                                        <th style="min-width:220px">Asset Name</th>
                                        <th style="min-width:120px" class="text-right">QUANTITY</th>
                                        <th style="min-width:120px" class="text-right">UNIT</th>
                                        <th style="min-width:120px" class="text-right">PRICE</th>
                                        <th style="min-width:120px" class="text-right">DISCOUNT (%)</th>
                                        <th style="min-width:220px" class="text-right">ALLOCATION</th>
                                        <th style="min-width:120px" class="text-right">TOTAL</th>
                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                    @foreach($purchase_order->details as $purchase_order_item)
                                        <tr>
                                            <td>{{ $purchase_order_item->coa->name }}</td>
                                            <td>{{ $purchase_order_item->name }}</td>
                                            <td class="text-right">{{ number_format_quantity($purchase_order_item->quantity) }}</td>
                                            <td class="text-right">{{ $purchase_order_item->unit }}</td>
                                            <td class="text-right">{{ number_format_quantity($purchase_order_item->price) }}</td>
                                            <td class="text-right">{{ number_format_quantity($purchase_order_item->discount) }}</td>
                                            <td class="text-right">{{$purchase_order_item->allocation->name}}</td>
                                            <td class="text-right">{{ number_format_quantity($purchase_order_item->quantity * $purchase_order_item->price - ($purchase_order_item->quantity * $purchase_order_item->discount) ) }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td colspan="7" class="text-right">SUBTOTAL</td>
                                        <td class="text-right">{{ number_format_quantity($purchase_order->subtotal) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-right">DISCOUNT</td>
                                        <td class="text-right">{{ number_format_quantity($purchase_order->discount) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-right">TAX BASE</td>
                                        <td class="text-right">{{ number_format_quantity($purchase_order->tax_base) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-right">TAX ({{$purchase_order->type_of_tax}})</td>
                                        <td class="text-right">{{ number_format_quantity($purchase_order->tax) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-right">TOTAL</td>
                                        <td class="text-right">{{ number_format_quantity($purchase_order->total) }}</td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Authorized User</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Creator</label>

                        <div class="col-md-6 content-show">
                            {{ $purchase_order_archived->formulir->createdBy->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Request Approval To</label>

                        <div class="col-md-6 content-show">
                            {{ $purchase_order_archived->formulir->approvalTo->name }}
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Status</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Approval Status</label>

                        <div class="col-md-6 content-show">
                            @include('framework::app.include._approval_status_label_detailed', [
                                        'approval_status' => $purchase_order_archived->formulir->approval_status,
                                        'approval_message' => $purchase_order_archived->formulir->approval_message,
                                        'approval_at' => $purchase_order_archived->formulir->approval_at,
                                        'approval_to' => $purchase_order_archived->formulir->approvalTo->name,
                                    ])
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Status</label>

                        <div class="col-md-6 content-show">
                            @include('framework::app.include._form_status_label', ['form_status' => $purchase_order_archived->formulir->form_status])
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        var item_table = initDatatable('#item-datatable');
    </script>
@stop
