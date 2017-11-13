@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/purchase-order') }}">Purchase Order</a></li>
            <li>Show</li>
        </ul>
        <h2 class="sub-header">Purchase Order </h2>
        @include('point-purchasing::app.purchasing.point.inventory.purchase-order._menu')

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
                                        'approval_status' => $purchase_order->formulir->approval_status,
                                        'approval_message' => $purchase_order->formulir->approval_message,
                                        'approval_at' => $purchase_order->formulir->approval_at,
                                        'approval_to' => $purchase_order->formulir->approvalTo->name,
                                    ])
                                    @include('framework::app.include._form_status_label', ['form_status' => $purchase_order->formulir->form_status])
                                </div>
                            </div>
                        </fieldset>

                        @if($reference)
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> REF <a
                                                href="{{ url('purchasing/point/purchase-requisition/'.$reference->id) }}">#{{ $reference->formulir->form_number }}</a>
                                    </legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Form Date</label>
                                <div class="col-md-6 content-show">
                                    {{ date_format_view($reference->formulir->form_date, true) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Employee</label>
                                <div class="col-md-6 content-show">
                                    {!! get_url_person($reference->employee->id) !!}

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
                                {{ $purchase_order->formulir->form_number }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Date</label>
                            <div class="col-md-6 content-show">
                                {{ date_format_view($purchase_order->formulir->form_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Supplier</label>
                            <div class="col-md-6 content-show">
                                {!! get_url_person($purchase_order->supplier->id) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>
                            <div class="col-md-6 content-show">
                                {{ $purchase_order->formulir->notes }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Cash Purchase</label>
                            <div class="col-md-6 content-show">
                                <input disabled type="checkbox" id="is_credit"
                                       name="is_credit" {{ $purchase_order->is_cash == 1 ? 'checked' : '' }}>
                                <span class="help-block">Check for create Delivery order / Uncheck for create Downpayment</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Include Expedition</label>
                            <div class="col-md-6 content-show">
                                <input disabled type="checkbox"
                                       name="include_expedition" {{ $purchase_order->include_expedition == 1 ? 'checked' : '' }}>
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
                                                <th style="min-width:220px">ITEM</th>
                                                <th style="min-width:120px" class="text-right">QUANTITY</th>
                                                <th style="min-width:20px">UNIT</th>
                                                <th style="min-width:120px" class="text-right">PRICE</th>
                                                <th style="min-width:120px" class="text-right">DISCOUNT (%)</th>
                                                <th style="min-width:220px" class="text-right">ALLOCATION</th>
                                                <th style="min-width:120px" class="text-right">TOTAL</th>
                                            </tr>
                                            </thead>
                                            <tbody class="manipulate-row">
                                            @foreach($purchase_order->items as $purchase_order_item)
                                                <tr>
                                                    <td>{{ $purchase_order_item->item->codeName }}</td>
                                                    <td class="text-right">{{ number_format_quantity($purchase_order_item->quantity) }}</td>
                                                    <td>{{ $purchase_order_item->unit }}</td>
                                                    <td class="text-right">{{ number_format_quantity($purchase_order_item->price) }}</td>
                                                    <td class="text-right">{{ number_format_quantity($purchase_order_item->discount) }}</td>
                                                    <td class="text-right">{{$purchase_order_item->allocation->name}}</td>
                                                    <td class="text-right">{{ number_format_quantity($purchase_order_item->quantity * $purchase_order_item->price - ($purchase_order_item->quantity * $purchase_order_item->discount) ) }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                            <tfoot>
                                            <tr>
                                                <td colspan="6" class="text-right">SUBTOTAL</td>
                                                <td class="text-right">{{ number_format_quantity($purchase_order->subtotal) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-right">DISCOUNT (%)</td>
                                                <td class="text-right">{{ number_format_quantity($purchase_order->discount) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-right">TAX BASE</td>
                                                <td class="text-right">{{ number_format_quantity($purchase_order->tax_base) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-right">TAX ({{ $purchase_order->type_of_tax }}
                                                    )
                                                </td>
                                                <td class="text-right">{{ number_format_quantity($purchase_order->tax) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-right">EXPEDITION FEE</td>
                                                <td class="text-right">{{ number_format_quantity($purchase_order->expedition_fee) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-right">TOTAL</td>
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
                                    {{ $purchase_order->formulir->createdBy->name }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Request Approval To</label>

                                <div class="col-md-6 content-show">
                                    {{ $purchase_order->formulir->approvalTo->name }}
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
                                @if(formulir_view_edit($purchase_order->formulir, 'update.point.purchasing.order'))
                                    @if($purchase_order->checkHaveReference() != null)
                                    <a href="{{url('purchasing/point/purchase-order/'.$purchase_order->id.'/edit')}}"
                                       class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                    @else
                                    <a href="{{url('purchasing/point/purchase-order/basic/'.$purchase_order->id.'/edit')}}"
                                       class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                    @endif
                                @endif
                                @if(formulir_view_cancel($purchase_order->formulir, 'delete.point.purchasing.order'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCancelForm('{{url('formulir/cancel')}}',
                                               '{{ $purchase_order->formulir_id }}',
                                               'delete.point.purchasing.order')"><i class="fa fa-times"></i> Cancel Form</a>
                                @endif
                                @if(formulir_view_close($purchase_order->formulir, 'update.point.purchasing.order'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCloseForm({{$purchase_order->formulir_id}},'{{url('formulir/close')}}')">Close
                                        Form</a>
                                @endif
                                @if(formulir_view_reopen($purchase_order->formulir, 'update.point.purchasing.order'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureReopenForm({{$purchase_order->formulir_id}},'{{url('formulir/reopen')}}')">Reopen
                                        Form</a>
                                @endif
                                @if(formulir_view_email_vendor($purchase_order->formulir, 'create.point.purchasing.order'))
                                    <a class="btn btn-effect-ripple btn-info"
                                            href="{{url('purchasing/point/purchase-order/'.$purchase_order->id.'/export')}}">Print</a>
                                    <form action="{{url('purchasing/point/purchase-order/send-email-order')}}" method="post">
                                        {!! csrf_field() !!}
                                        <input type="hidden" readonly="" name="purchase_order_id" value="{{$purchase_order->id}}">
                                        <input type="submit" class="btn btn-primary" value="Send Email Purchase Order">
                                    </form>
                                @endif
                            </div>
                        </div>
                    </fieldset>

                    @if(formulir_view_approval($purchase_order->formulir, 'approval.point.purchasing.order'))
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6">
                                    <form action="{{url('purchasing/point/purchase-order/'.$purchase_order->id.'/approve')}}"
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
                                    <form action="{{url('purchasing/point/purchase-order/'.$purchase_order->id.'/reject')}}"
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

                    @if($list_purchase_order_archived->count() > 0)
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
                                            @foreach($list_purchase_order_archived as $purchase_order_archived)
                                                <tr>
                                                    <td class="text-center">
                                                        <a href="{{ url('purchasing/point/purchase-order/'.$purchase_order_archived->id.'/archived') }}"
                                                           data-toggle="tooltip" title="Show"
                                                           class="btn btn-effect-ripple btn-xs btn-info"><i
                                                                    class="fa fa-file"></i> {{ 'Revision ' . $count++ }}
                                                        </a>
                                                    </td>
                                                    <td>{{ date_format_view($purchase_order->formulir->form_date) }}</td>
                                                    <td>{{ $purchase_order_archived->formulir->archived }}</td>
                                                    <td>{{ $purchase_order_archived->formulir->createdBy->name }}</td>
                                                    <td>{{ $purchase_order_archived->formulir->updatedBy->name }}</td>
                                                    <td>{{ $purchase_order_archived->formulir->edit_notes }}</td>
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
