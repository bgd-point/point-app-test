@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/sales-order') }}">Sales Order</a></li>
            <li>Show</li>
        </ul>
        <h2 class="sub-header">Sales Order </h2>
        @include('point-sales::app.sales.point.sales.sales-order._menu')

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
                                        'approval_status' => $sales_order->formulir->approval_status,
                                        'approval_message' => $sales_order->formulir->approval_message,
                                        'approval_at' => $sales_order->formulir->approval_at,
                                        'approval_to' => $sales_order->formulir->approvalTo->name,
                                    ])
                                    @include('framework::app.include._form_status_label', ['form_status' => $sales_order->formulir->form_status])
                                </div>
                            </div>
                        </fieldset>

                        @if($noreference)
                            <fieldset>
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <legend><i class="fa fa-angle-right"></i> REF <a
                                                    href="{{ url('sales/point/indirect/sales-quotation/'.$reference->id) }}">#{{ $reference->formulir->form_number }}</a>
                                        </legend>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Form Date</label>
                                    <div class="col-md-6 content-show">
                                        {{ date_format_view($reference->formulir->form_date, true) }}
                                    </div>
                                </div>
                            </fieldset>
                        @endif

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Form</legend>
                                </div>
                            </div>
                        </fieldset>
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

                            <div class="col-md-6 content-show">
                                {{ $sales_order->formulir->form_number }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Date</label>

                            <div class="col-md-6 content-show">
                                {{ date_format_view($sales_order->formulir->form_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Customer</label>

                            <div class="col-md-6 content-show">
                                {!! get_url_person($sales_order->person_id) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>

                            <div class="col-md-6 content-show">
                                {{ $sales_order->formulir->notes }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Referenced By</label>
                            <div class="col-md-6 content-show">
                                @foreach($list_referenced as $referenced)
                                    <?php
                                    $model = $referenced->locking->formulirable_type;
                                    $url = $model::showUrl($referenced->locking->formulirable_id);
                                    ?>
                                    <a href="{{ url($url) }}">{{ $referenced->locking->form_number }}</a> <br/>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Require downpayment before delivering the order</label>

                            <div class="col-md-6 content-show">
                                <input disabled type="checkbox" id="credit-selling"
                                       name="is_cash" {{ $sales_order->is_cash == 1 ? 'checked' : '' }}>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Order Expedition Service</label>

                            <div class="col-md-6 content-show">
                                <input disabled type="checkbox"
                                       name="include_expedition" {{ $sales_order->include_expedition == 0 ? 'checked' : '' }}>
                            </div>
                        </div>
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Item</legend>
                                </div>
                            </div>
                        </fieldset>

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
                                            <th class="text-right">DISCOUNT (%)</th>
                                            <th class="text-right">TOTAL</th>
                                        </tr>
                                        </thead>
                                        <tbody class="manipulate-row">
                                        @foreach($sales_order->items as $sales_order_item)
                                            <tr>
                                                <td>{{ $sales_order_item->item->codeName }}</td>
                                                <td>{{ $sales_order_item->allocation_id ? $sales_order_item->allocation->name : 'No Allocation' }}</td>
                                                <td class="text-right">{{ number_format_quantity($sales_order_item->quantity) }}</td>
                                                <td>{{ $sales_order_item->unit }}</td>
                                                <td class="text-right">{{ number_format_quantity($sales_order_item->price) }}</td>
                                                <td class="text-right">{{ number_format_quantity($sales_order_item->discount) }}</td>
                                                <td class="text-right">{{ number_format_quantity($sales_order_item->quantity * $sales_order_item->price - ($sales_order_item->quantity * $sales_order_item->price/100 * $sales_order_item->discount) ) }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <td colspan="6" class="text-right">SUBTOTAL</td>
                                            <td class="text-right">{{ number_format_quantity($sales_order->subtotal) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="text-right">DISCOUNT (%)</td>
                                            <td class="text-right">{{ number_format_quantity($sales_order->discount) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="text-right">TAX BASE</td>
                                            <td class="text-right">{{ number_format_quantity($sales_order->tax_base) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="text-right">TAX ({{ $sales_order->type_of_tax }})</td>
                                            <td class="text-right">{{ number_format_quantity($sales_order->tax) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="text-right">EXPEDITION FEE</td>
                                            <td class="text-right">{{ number_format_quantity($sales_order->expedition_fee) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="text-right">TOTAL</td>
                                            <td class="text-right">{{ number_format_quantity($sales_order->total) }}</td>
                                        </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Authorized User</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Form Creator</label>
                                <div class="col-md-6 content-show">
                                    {{ $sales_order->formulir->createdBy->name }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Request Approval To</label>
                                <div class="col-md-6 content-show">
                                    {{ $sales_order->formulir->approvalTo->name }}
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
                                @if($sales_order->checkHaveReference() != null)
                                    <a href="{{url('sales/point/indirect/sales-order/'.$sales_order->id.'/edit')}}"
                                       class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                @else
                                    <a href="{{url('sales/point/indirect/sales-order/'.$sales_order->id.'/edit-no-ref')}}"
                                       class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                @endif

                                @if(formulir_view_cancel_or_request_cancel($sales_order->formulir, 'delete.point.sales.order', 'approval.point.sales.order') == 1)
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureCancelForm('{{url('formulir/cancel')}}', '{{ $sales_order->formulir_id }}','approval.point.sales.order')">
                                        <i class="fa fa-times"></i> 
                                        Cancel Form
                                    </a>
                                @elseif(formulir_view_cancel_or_request_cancel($sales_order->formulir, 'delete.point.sales.order', 'approval.point.sales.order') == 2)
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureRequestCancelForm(this, '{{url('formulir/requestCancel')}}', '{{ $sales_order->formulir_id }}', 'delete.point.sales.order')">
                                        <i class="fa fa-times"></i> 
                                        Request Cancel Form
                                    </a>
                                @endif

                                @if(formulir_view_close($sales_order->formulir, 'update.point.sales.order'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCloseForm({{$sales_order->formulir_id}},'{{url('formulir/close')}}')">Close
                                        Form</a>
                                @endif
                                @if(formulir_view_reopen($sales_order->formulir, 'update.point.sales.order'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureReopenForm({{$sales_order->formulir_id}},'{{url('formulir/reopen')}}')">Reopen
                                        Form</a>
                                @endif
                                @if($sales_order->formulir->approval_status == 1 && $sales_order->formulir->form_status == 0 && auth()->user()->may('create.point.sales.downpayment') && $sales_order->is_cash == 1)
                                    @if($sales_order->getTotalRemainingDownpayment($sales_order->id) < $sales_order->total)
                                    <a href="{{ url('sales/point/indirect/downpayment/insert/' . $sales_order->id) }}"
                                       class="btn btn-effect-ripple  btn-info"><i class="fa fa-external-link"></i>
                                        Downpayment</a>
                                    @endif
                                @endif
                                @if(formulir_view_email_vendor($sales_order->formulir, 'create.point.sales.order'))
                                    <form action="{{url('sales/point/indirect/sales-order/send-email-order')}}" method="post">
                                        {!! csrf_field() !!}
                                        <input type="hidden" readonly="" name="sales_order_id" value="{{$sales_order->id}}">
                                        <input type="submit" class="btn btn-primary" value="Send Email To Customer">
                                    </form>
                                @endif
                            </div>
                        </div>
                    </fieldset>

                    @if(formulir_view_approval($sales_order->formulir, 'approval.point.sales.order'))
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6">
                                    <form action="{{url('sales/point/indirect/sales-order/'.$sales_order->id.'/approve')}}"
                                          method="post">
                                        {!! csrf_field() !!}

                                        <div class="input-group">
                                            <input type="text" name="approval_message" class="form-control"
                                                   placeholder="Message">
                                            <span class="input-group-btn">
                                                <input type="submit" class="btn btn-primary" value="Approve">
                                            </span>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <form action="{{url('sales/point/indirect/sales-order/'.$sales_order->id.'/reject')}}"
                                          method="post">
                                        {!! csrf_field() !!}

                                        <div class="input-group">
                                            <input type="text" name="approval_message" class="form-control"
                                                   placeholder="Message">
                                    <span class="input-group-btn">
                                        <input type="submit" class="btn btn-danger" value="Reject">
                                    </span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </fieldset>
                    @endif

                    @if($list_sales_order_archived->count() > 0)
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
                                                <th>Form Date</th>
                                                <th>Form Number</th>
                                                <th>Created By</th>
                                                <th>Updated By</th>
                                                <th>Reason</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php $count = 0;?>
                                            @foreach($list_sales_order_archived as $sales_order_archived)
                                                <tr>
                                                    <td class="text-center">
                                                        <a href="{{ url('sales/point/indirect/sales-order/'.$sales_order_archived->id.'/archived') }}"
                                                           data-toggle="tooltip" title="Show"
                                                           class="btn btn-effect-ripple btn-xs btn-info"><i
                                                                    class="fa fa-file"></i> {{ 'Revision ' . $count++ }}
                                                        </a>
                                                    </td>
                                                    <td>{{ date_format_view($sales_order->formulir->form_date) }}</td>
                                                    <td>{{ $sales_order_archived->formulir->archived }}</td>
                                                    <td>{{ $sales_order_archived->formulir->createdBy->name }}</td>
                                                    <td>{{ $sales_order_archived->formulir->updatedBy->name }}</td>
                                                    <td>{{ $sales_order_archived->formulir->edit_notes }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    @endif

                    @if($email_history->count() > 0)
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> EMAIL HISTORY</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-12 content-show">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th style="text-align: right;">NO</th>
                                                    <th style="text-align: center;">SENT AT</th>
                                                    <th>SENDER</th>
                                                    <th>RECIPIENT</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($email_history as $index=>$item)
                                                <tr>
                                                    <td style="text-align: right;">{{ $index+1 }}</td>
                                                    <td style="text-align: center;">{{ date_format_view($item->sent_at, true) }}</td>
                                                    <td><a href="/master/user/{{ $item->sender }}">{{ $item->user->name }}</a></td>
                                                    <td>{!! get_url_person($item->recipient) !!}
                                                        <span style="text-transform: none !important;"> ({{ $item->recipient_email }})</span>
                                                    </td>
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
            <!-- END Tabs Content -->
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
