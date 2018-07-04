@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.service._breadcrumb')
            <li><a href="{{ url('sales/point/service/invoice') }}">Invoice</a></li>
            <li>Show</li>
        </ul>
        <h2 class="sub-header">Invoice </h2>
        @include('point-sales::app.sales.point.service.invoice._menu')

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
                            <label class="col-md-3 control-label">Customer</label>
                            <div class="col-md-6 content-show">
                                {!! get_url_person($invoice->person->id) !!}
                            </div>
                        </div>
                        @if($list_referenced->count() > 0)
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
                        @endif
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>
                            <div class="col-md-6 content-show">
                                {{ $invoice->formulir->notes }}
                            </div>
                        </div>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Service</legend>
                                </div>
                            </div>
                        </fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <!-- SERVICE DATA -->
                                    <table id="item-datatable" class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th>SERVICE</th>
                                            <th>ALLOCATION</th>
                                            <th>NOTES</th>
                                            <th class="text-right">QUANTITY</th>
                                            <th class="text-right">PRICE</th>
                                            <th class="text-right">DISCOUNT (%)</th>
                                            <th class="text-right">TOTAL</th>
                                        </tr>
                                        </thead>
                                        <tbody class="manipulate-row">
                                        @foreach($invoice->services as $invoice_service)
                                            <tr>
                                                <td>
                                                    <a href="{{ url('master/item/'.$invoice_service->item_id) }}">{{ $invoice_service->service->name }}</a>
                                                </td>
                                                <td>{{$invoice_service->allocation->name}}</td>
                                                <td>{{$invoice_service->service_notes}}</td>
                                                <td class="text-right">{{ number_format_quantity($invoice_service->quantity) }} {{ $invoice_service->unit }}</td>
                                                <td class="text-right">{{ number_format_quantity($invoice_service->price) }}</td>
                                                <td class="text-right">{{ number_format_quantity($invoice_service->discount) }}</td>
                                                <td class="text-right">{{ number_format_quantity(($invoice_service->quantity * $invoice_service->price) - ($invoice_service->quantity * $invoice_service->price * $invoice_service->discount / 100)) }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                        @if(count($invoice->items) > 0)
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
                                    
                                    <!-- END SERVICE -->
                                    <table id="item-datatable" class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th>ITEM</th>
                                            <th>ALLOCATION</th>
                                            <th>NOTES</th>
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
                                                <td>{{$invoice_item->allocation->name}}</td>
                                                <td>{{$invoice_item->item_notes}}</td>
                                                <td class="text-right">{{ number_format_quantity($invoice_item->quantity) }} {{ $invoice_item->unit }}</td>
                                                <td class="text-right">{{ number_format_quantity($invoice_item->price) }}</td>
                                                <td class="text-right">{{ number_format_quantity($invoice_item->discount) }}</td>
                                                <td class="text-right">{{ number_format_quantity(($invoice_item->quantity * $invoice_item->price) - ($invoice_item->quantity * $invoice_item->price * $invoice_item->discount / 100)) }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table  class="table">
                                        <tr>
                                            <td colspan="5"></td>
                                            <td class="text-right"><strong>SUB TOTAL</strong></td>
                                            <td class="text-right">{{ number_format_quantity($invoice->subtotal) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="5"></td>
                                            <td class="text-right"><strong>DISCOUNT (%)</strong></td>
                                            <td class="text-right">{{ number_format_quantity($invoice->discount) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="5"></td>
                                            <td class="text-right"><strong>TAX BASE</strong></td>
                                            <td class="text-right">{{ number_format_quantity($invoice->tax_base) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="5"></td>
                                            <td class="text-right"><strong>TAX ({{$invoice->type_of_tax}})</strong></td>
                                            <td class="text-right">{{ number_format_quantity($invoice->tax) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="5"></td>
                                            <td class="text-right"><strong>TOTAL</strong></td>
                                            <td class="text-right">{{ number_format_quantity($invoice->total) }}</td>
                                        </tr>
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
                                @if(formulir_view_edit($invoice->formulir, 'update.point.sales.service.invoice'))
                                    <a href="{{url('sales/point/service/invoice/'.$invoice->id.'/edit')}}"
                                       class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                @endif
                                @if(formulir_view_cancel($invoice->formulir, 'delete.point.sales.service.invoice'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCancelForm('{{url('formulir/cancel')}}',
                                               '{{ $invoice->formulir_id }}',
                                               'delete.point.sales.invoice')"><i class="fa fa-times"></i> Cancel
                                        Form</a>
                                @endif
                                @if(formulir_view_close($invoice->formulir, 'update.point.sales.service.invoice'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCloseForm({{$invoice->formulir_id}},'{{url('formulir/close')}}')">Close
                                        Form</a>
                                @endif
                                @if(formulir_view_reopen($invoice->formulir, 'update.point.sales.service.invoice'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureReopenForm({{$invoice->formulir_id}},'{{url('formulir/reopen')}}')">Reopen
                                        Form</a>
                                @endif
                                @if(formulir_view_email_vendor($invoice->formulir, 'create.point.sales.service.invoice'))
                                    <form action="{{url('sales/point/service/invoice/send-email')}}" method="post">
                                        {!! csrf_field() !!}
                                        <input type="hidden" readonly="" name="invoice_id" value="{{$invoice->id}}">
                                        <input type="submit" class="btn btn-primary" value="Send Email To Customer">
                                    </form>
                                    <a class="btn btn-effect-ripple btn-info"
                                    href="{{url('sales/point/service/invoice/'.$invoice->id.'/export')}}">Export PDF</a>
                                @endif
                            </div>
                        </div>
                    </fieldset>

                    @if(formulir_view_approval($invoice->formulir, 'approval.point.sales.service.invoice'))
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6">
                                    <form action="{{url('sales/point/service/invoice/'.$invoice->id.'/approve')}}"
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
                                    <form action="{{url('sales/point/service/invoice/'.$invoice->id.'/reject')}}"
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
                                                        <a href="{{ url('sales/point/service/invoice/'.$invoice_archived->id.'/archived') }}"
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
