@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/invoice') }}">Invoice</a></li>
            <li>Show</li>
        </ul>
        <h2 class="sub-header">Invoice </h2>
        @include('point-purchasing::app.purchasing.point.inventory.invoice._menu')

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
                                        'approval_status' => $invoice->formulir->approval_status,
                                        'approval_message' => $invoice->formulir->approval_message,
                                        'approval_at' => $invoice->formulir->approval_at,
                                        'approval_to' => $invoice->formulir->approvalTo->name,
                                    ])
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
                            <label class="col-md-3 control-label">FORM Date</label>
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
                            <label class="col-md-3 control-label">Supplier</label>
                            <div class="col-md-6 content-show">
                                {!! get_url_person($invoice->supplier_id) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>
                            <div class="col-md-6 content-show">
                                {{ $invoice->formulir->notes }}
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
                                                <td class="text-right">{{ number_format_quantity($invoice_item->quantity) }} {{ $invoice_item->unit }}</td>
                                                <td class="text-right">{{ number_format_quantity($invoice_item->price) }}</td>
                                                <td class="text-right">{{ number_format_quantity($invoice_item->discount) }}</td>
                                                <td class="text-right">{{ number_format_quantity(($invoice_item->quantity * $invoice_item->price) - ($invoice_item->quantity * $invoice_item->price /100 * $invoice_item->discount)) }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-right">SUB TOTAL</td>
                                            <td class="text-right">{{ number_format_quantity($invoice->subtotal) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-right">DISCOUNT (%)</td>
                                            <td class="text-right">{{ number_format_quantity($invoice->discount) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-right">TAX BASE</td>
                                            <td class="text-right">{{ number_format_quantity($invoice->tax_base) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-right">TAX ({{$invoice->type_of_tax}})</td>
                                            <td class="text-right">{{ number_format_quantity($invoice->tax) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-right">EXPEDITION FEE</td>
                                            <td class="text-right">{{ number_format_quantity($invoice->expedition_fee) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-right">TOTAL</td>
                                            <td class="text-right">{{ number_format_quantity($invoice->total) }}</td>
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
                                    {{ $invoice->formulir->createdBy->name }}
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
                                @if(formulir_view_edit($invoice->formulir, 'update.point.purchasing.invoice'))
                                    <?php $parent_refer = ReferHelper::getRefers(get_class($invoice), $invoice->id); ?>
                                    @if(count($parent_refer) > 0)
                                        <a href="{{url('purchasing/point/invoice/'.$invoice->id.'/edit')}}"
                                           class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                    @else
                                        <a href="{{url('purchasing/point/invoice/basic/'.$invoice->id.'/edit')}}"
                                           class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                    @endif
                                @endif
                                @if(formulir_view_cancel($invoice->formulir, 'delete.point.purchasing.invoice'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCancelForm('{{url('formulir/cancel')}}',
                                               '{{ $invoice->formulir_id }}',
                                               'delete.point.purchasing.invoice')"><i class="fa fa-times"></i> Cancel
                                        Form</a>
                                @endif
                                @if(formulir_view_close($invoice->formulir, 'update.point.purchasing.invoice'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCloseForm({{$invoice->formulir_id}},'{{url('formulir/close')}}')">Close
                                        Form</a>
                                @endif
                                @if(formulir_view_reopen($invoice->formulir, 'update.point.purchasing.invoice'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureReopenForm({{$invoice->formulir_id}},'{{url('formulir/reopen')}}')">Reopen
                                        Form</a>
                                @endif
                                @if(formulir_view_email_vendor($invoice->formulir, 'create.point.purchasing.invoice'))
                                    <form action="{{url('purchasing/point/invoice/send-email')}}" method="post">
                                        {!! csrf_field() !!}
                                        <input type="hidden" readonly="" name="invoice_id" value="{{$invoice->id}}">
                                        <input type="submit" class="btn btn-primary" value="Send Email Supplier">
                                    </form>
                                    <a class="btn btn-effect-ripple btn-info"
                                    href="{{url('purchasing/point/invoice/'.$invoice->id.'/export')}}">Export PDF</a>
                                @endif
                                @if($invoice->formulir->form_status != -1)
                                <a class="btn btn-effect-ripple btn-info"
                                    href="{{url('purchasing/point/invoice/'.$invoice->id.'/print-barcode')}}">Print Barcode</a>
                                @endif
                            </div>
                        </div>
                    </fieldset>

                    @if(formulir_view_approval($invoice->formulir, 'approval.point.purchasing.invoice'))
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6">
                                    <form action="{{url('purchasing/point/invoice/'.$invoice->id.'/approve')}}"
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
                                    <form action="{{url('purchasing/point/invoice/'.$invoice->id.'/reject')}}"
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
                                                        <a href="{{ url('purchasing/point/invoice/'.$invoice_archived->id.'/archived') }}"
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
