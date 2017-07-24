@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/sales-order') }}">Sales Order</a></li>
            <li><a href="{{ url('sales/point/indirect/sales-order/'.$sales_order->id) }}">{{$sales_order->formulir->form_date}}</a></li>
            <li>Archived</li>
        </ul>
        <h2 class="sub-header">Sales Order</h2>
        @include('point-sales::app.sales.point.sales.sales-order._menu')

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
                        {{ $sales_order_archived->formulir->archived }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Form Date</label>

                    <div class="col-md-6 content-show">
                        {{ date_format_view($sales_order_archived->formulir->form_date, true) }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Customer</label>
                    <div class="col-md-6 content-show">
                        {!! get_url_person($sales_order_archived->person_id) !!}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>

                    <div class="col-md-6 content-show">
                        {{ $sales_order_archived->formulir->notes }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Cash Sales</label>

                    <div class="col-md-6 content-show">
                        <input disabled type="checkbox" id="credit-selling" name="is_cash" {{ $sales_order_archived->is_cash == 1 ? 'checked' : '' }}>
                        <span class="help-block">If checked, you need to make a downpayment before deliver the order</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Include Expedition</label>

                    <div class="col-md-6 content-show">
                        <input disabled type="checkbox" name="include_expedition" {{ $sales_order_archived->include_expedition == 1 ? 'checked' : '' }}>
                        <span class="help-block">Uncheck this if you want to order expedition service</span>
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
                                        <th>ITEM</th>
                                        <th>ALLOCATION</th>
                                        <th class="text-right">QUANTITY</th>
                                        <th>UNIT</th>
                                        <th class="text-right">PRICE</th>
                                        <th class="text-right">DISCOUNT</th>
                                        <th class="text-right">TOTAL</th>
                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                    @foreach($sales_order->items as $sales_order_item)
                                        <tr>
                                            <td>{{ $sales_order_item->item->codeName }}</td>
                                            <td>{{ $sales_order_item->allocation_id ? $sales_order_item->allocation->name : 'no allocation'}}</td>
                                            <td class="text-right">{{ number_format_quantity($sales_order_item->quantity) }}</td>
                                            <td>{{ $sales_order_item->unit }}</td>
                                            <td class="text-right">{{ number_format_quantity($sales_order_item->price) }}</td>
                                            <td class="text-right">{{ number_format_quantity($sales_order_item->discount) }}</td>
                                            <td class="text-right">{{ number_format_quantity($sales_order_item->quantity * $sales_order_item->price - ($sales_order_item->quantity * $sales_order_item->discount) ) }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-right">SUBTOTAL</td>
                                        <td class="text-right">{{ number_format_quantity($sales_order->subtotal) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">DISCOUNT</td>
                                        <td class="text-right">{{ number_format_quantity($sales_order->discount) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">TAX BASE</td>
                                        <td class="text-right">{{ number_format_quantity($sales_order->tax_base) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">TAX</td>
                                        <td class="text-right">{{ number_format_quantity($sales_order->tax) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">TOTAL</td>
                                        <td class="text-right">{{ number_format_quantity($sales_order->total) }}</td>
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
                            {{ $sales_order_archived->formulir->createdBy->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Request Approval To</label>

                        <div class="col-md-6 content-show">
                            {{ $sales_order_archived->formulir->approvalTo->name }}
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
                                        'approval_status' => $sales_order_archived->formulir->approval_status,
                                        'approval_message' => $sales_order_archived->formulir->approval_message,
                                        'approval_at' => $sales_order_archived->formulir->approval_at,
                                        'approval_to' => $sales_order_archived->formulir->approvalTo->name,
                                    ])
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Status</label>

                        <div class="col-md-6 content-show">
                            @include('framework::app.include._form_status_label', ['form_status' => $sales_order_archived->formulir->form_status])
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <style>
        tbody.manipulate-row:after {
            content: '';
            display: block;
            height: 100px;
        }
    </style>
    <script>
        var item_table = $('#item-datatable').DataTable({
            bSort: false,
            bPaginate: false,
            bInfo: false,
            bFilter: false,
            bScrollCollapse: false,
            scrollX: true
        });
    </script>
@stop
