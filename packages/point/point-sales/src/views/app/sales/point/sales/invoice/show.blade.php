@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/invoice') }}">Invoice</a></li>
            <li>Show</li>
        </ul>
        <h2 class="sub-header">Invoice </h2>
        @include('point-sales::app.sales.point.sales.invoice._menu')

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
                                    @include('framework::app.include._form_status_label', ['form_status' => $invoice->formulir->form_status])
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Formulir</legend>
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
                            <label class="col-md-3 control-label">FORM NUMBER</label>
                            <div class="col-md-6 content-show">
                                {{ $invoice->formulir->form_number }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Date</label>
                            <div class="col-md-6 content-show">
                                {{ date_format_view($invoice->formulir->form_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Due Date</label>
                            <div class="col-md-6 content-show">
                                {{ date_format_view($invoice->due_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">DELIVERY ORDER NUMBER</label>
                            <div class="col-md-6 content-show">
                                @foreach($invoice->lockingForm as $lockingForm)
                                    {!! formulir_url($lockingForm->lockedForm) !!}
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Customer</label>
                            <div class="col-md-6 content-show">
                                {!! get_url_person($invoice->person_id) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>
                            <div class="col-md-6 content-show">
                                {{ $invoice->formulir->notes }}
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
                                            <th class="text-right">PRICE</th>
                                            <th class="text-right">DISCOUNT (%)</th>
                                            <th class="text-right">TOTAL</th>
                                        </tr>
                                        </thead>
                                        <tbody class="manipulate-row">
                                        @foreach($invoice->items as $invoice_item)
                                            <tr>
                                                <td>
                                                    <a href="{{ url('master/item/'.$invoice_item->item_id) }}">{{ $invoice_item->item->codeName }}</a>
                                                    <input type="hidden" name="item_id[]"
                                                           value="{{$invoice_item->item_id}}"/>
                                                </td>
                                                <td>{{$invoice_item->allocation_id ? $invoice_item->allocation->name : 'no allocation'}}</td>
                                                <td class="text-right">{{ number_format_quantity($invoice_item->quantity) }} {{ $invoice_item->unit }}</td>
                                                <td class="text-right">{{ number_format_quantity($invoice_item->price) }}</td>
                                                <td class="text-right">{{ number_format_quantity($invoice_item->discount) }}</td>
                                                <td class="text-right">{{ number_format_quantity(($invoice_item->quantity * $invoice_item->price) - ($invoice_item->quantity * $invoice_item->price * $invoice_item->discount / 100)) }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <td colspan="5" class="text-right">SUB TOTAL</td>
                                            <td class="text-right">{{ number_format_quantity($invoice->subtotal) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" class="text-right">DISCOUNT (%)</td>
                                            <td class="text-right">{{ number_format_quantity($invoice->discount) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" class="text-right">TAX BASE</td>
                                            <td class="text-right">{{ number_format_quantity($invoice->tax_base) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" class="text-right">TAX</td>
                                            <td class="text-right">{{ number_format_quantity($invoice->tax) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" class="text-right">EXPEDITION FEE</td>
                                            <td class="text-right">{{ number_format_quantity($invoice->expedition_fee) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" class="text-right">TOTAL</td>
                                            <td class="text-right">{{ number_format_quantity($invoice->total) }}</td>
                                        </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        @if($returs->count() > 0)
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Retur</legend>
                                </div>
                            </div>
                        </fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    @foreach($returs as $retur)
                                        <table id="table" class="table table-striped">
                                            <thead>
                                            <tr>
                                                <th>RETUR {{ date_format_view($retur->formulir->form_date) }} | {{ $retur->formulir->form_number }}</th>
                                                <th class="text-right">QTY RETUR</th>
                                                <th class="text-right">PRICE</th>
                                                <th class="text-right">TOTAL RETUR</th>
                                            </tr>
                                            </thead>
                                            <tbody class="">
                                            @foreach($retur->items as $retur_item)
                                                <tr>
                                                    <td>
                                                        <a href="{{ url('master/item/'.$retur_item->item_id) }}">{{ $retur_item->item->codeName }}</a>
                                                        <input type="hidden" name="item_id[]"
                                                                value="{{$retur_item->item_id}}"/>
                                                    </td>
                                                    <td class="text-right">{{ number_format_quantity($retur_item->quantity) }} {{ $retur_item->unit }}</td>
                                                    <td class="text-right">{{ number_format_quantity($retur_item->price) }}</td>
                                                    <td class="text-right">{{ number_format_quantity($retur_item->quantity * $retur_item->price) }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                            <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-right">TOTAL</td>
                                                <td class="text-right">{{ number_format_quantity($retur->subtotal) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-right">TAX</td>
                                                <td class="text-right">{{ number_format_quantity($retur->tax) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-right">TOTAL</td>
                                                <td class="text-right">{{ number_format_quantity($retur->total) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-right"></td>
                                                <td class="text-right">
                                                    <form method="POST" action="{{ url('sales/point/indirect/invoice/'. $retur->point_sales_invoice_id.'/retur/' . $retur->id . '/delete') }}">
                                                        {!! csrf_field() !!}
                                                        <button class="btn btn-danger">
                                                            Delete Retur
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Person In Charge</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Form Creator</label>

                                <div class="col-md-6 content-show">
                                    {{ $invoice->formulir->createdBy->name }} ({{ \Point\Core\Helpers\DateHelper::formatView($invoice->formulir->created_at) }})
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
                                @if(formulir_view_edit($invoice->formulir, 'update.point.sales.invoice'))
                                    <?php $parent_refer = Point\Framework\Helpers\ReferHelper::getRefers(get_class($invoice), $invoice->id); ?>
                                    @if(count($parent_refer) > 0)
                                        <a href="{{url('sales/point/indirect/invoice/'.$invoice->id.'/edit')}}"
                                           class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                    @else
                                        <a href="{{url('sales/point/indirect/invoice/basic/'.$invoice->id.'/edit')}}"
                                           class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                    @endif
                                @endif

                                <a href="{{url('sales/point/indirect/invoice/'.$invoice->id.'/retur')}}"
                                        class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Retur</a>

                                @if(formulir_view_cancel_or_request_cancel($invoice->formulir, 'delete.point.sales.invoice', 'approval.point.sales.invoice') == 1)
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureCancelForm('{{url('formulir/cancel')}}', '{{ $invoice->formulir_id }}','approval.point.sales.invoice')">
                                        <i class="fa fa-times"></i>
                                        Cancel Form
                                    </a>
                                @elseif(formulir_view_cancel_or_request_cancel($invoice->formulir, 'delete.point.sales.invoice', 'approval.point.sales.invoice') == 2)
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureRequestCancelForm(this, '{{url('formulir/requestCancel')}}', '{{ $invoice->formulir_id }}', 'delete.point.sales.invoice')">
                                        <i class="fa fa-times"></i>
                                        Request Cancel Form
                                    </a>
                                @endif
                                @if(formulir_view_email_vendor($invoice->formulir, 'create.point.sales.invoice'))
                                    <form action="{{url('sales/point/indirect/invoice/send-email')}}" method="post">
                                        {!! csrf_field() !!}
                                        <input type="hidden" readonly="" name="invoice_id" value="{{$invoice->id}}">
                                        <input type="submit" class="btn btn-primary" value="Send Email To Customer">
                                    </form>
                                    @if($invoice->formulir->approval_status == 1) {
                                    @if($invoice->approval_print_status == 1)
                                    <a class="btn btn-effect-ripple btn-info" href="{{url('sales/point/indirect/invoice/'.$invoice->id.'/export')}}">Print</a>
                                    @else
                                    <a class="btn btn-effect-ripple btn-info" href="{{url('sales/point/indirect/invoice/request-approval-print/'.$invoice->id)}}">Send Request Approval to Print</a>
                                    @endif
                                    @endif
                                @endif
                            </div>
                        </div>
                    </fieldset>

                    @if(formulir_view_approval($invoice->formulir, 'approval.point.sales.invoice'))
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6">
                                    <form action="{{url('sales/point/indirect/invoice/'.$invoice->id.'/approve')}}"
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
                                    <form action="{{url('sales/point/indirect/invoice/'.$invoice->id.'/reject')}}"
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

                    @if($list_invoice_archived->count() > 0)
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
                                            @foreach($list_invoice_archived as $invoice_archived)
                                                <tr>
                                                    <td class="text-center">
                                                        <a href="{{ url('sales/point/indirect/invoice/'.$invoice_archived->id.'/archived') }}"
                                                           data-toggle="tooltip" title="Show"
                                                           class="btn btn-effect-ripple btn-xs btn-info"><i
                                                                    class="fa fa-file"></i> {{ 'Revision ' . $count++ }}
                                                        </a>
                                                    </td>
                                                    <td>{{ date_format_view($invoice->formulir->form_date) }}</td>
                                                    <td>{{ $invoice_archived->formulir->archived }}</td>
                                                    <td>{{ $invoice_archived->formulir->createdBy->name }}</td>
                                                    <td>{{ $invoice_archived->formulir->updatedBy->name }}</td>
                                                    <td>{{ $invoice_archived->formulir->edit_notes }}</td>
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
    <script>
        var item_table = initDatatable('#item-datatable');
    </script>
@stop
