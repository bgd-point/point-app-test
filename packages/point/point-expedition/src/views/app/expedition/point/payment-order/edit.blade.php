@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-expedition::app/expedition/point/payment-order/_breadcrumb')
            <li>Edit</li>
        </ul>
        <h2 class="sub-header">PAYMENT ORDER</h2>
        @include('point-expedition::app.expedition.point.payment-order._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('expedition/point/payment-order/'.$payment_order->id.'/edit-review')}}"
                      method="post" class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <div class="form-group">
                        <label class="col-md-3 control-label">Reason edit *</label>
                        <div class="col-md-6">
                            <input type="text" name="edit_notes" class="form-control" autofocus>
                        </div>
                    </div>
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> PAYMENT ORDER</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">PAYMENT DATE</label>
                        <div class="col-md-3">
                            <input type="text" name="payment_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                   value="{{ date(date_format_get(), strtotime(\Carbon::now())) }}">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group bootstrap-timepicker">
                                <input type="text" id="time" name="time" class="form-control timepicker">
                                <span class="input-group-btn">
                                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary"><i
                                            class="fa fa-clock-o"></i></a>
                            </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Expedition</label>
                        <div class="col-md-6 content-show">
                            <input type="hidden" name="expedition_id" value="{{$expedition->id}}">
                            {!! get_url_person($expedition->id) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Payment Type</label>
                        <div class="col-md-6 content-show">
                            <select id="payment_type" name="payment_type" class="selectize" style="width: 100%;"
                                    data-placeholder="Please choose">
                                <option value="cash" @if($payment_order->payment_type=="cash") selected @endif>Cash</option>
                                <option value="bank" @if($payment_order->payment_type=="bank") selected @endif>Bank</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="">
                        </div>
                    </div>
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="block full">
                                    <!-- Block Tabs Title -->
                                    <div class="block-title">
                                        <ul class="nav nav-tabs" data-toggle="tabs">
                                            <li class="active"><a href="#block-tabs-invoice">INVOICE</a></li>
                                            <li><a href="#block-tabs-cutoff">CUTOFF</a></li>
                                            <li><a href="#block-tabs-downpayment">DOWNPAYMENT</a></li>
                                            <li><a href="#block-tabs-other">OTHERS</a></li>
                                        </ul>
                                    </div>
                                    <!-- END Block Tabs Title -->

                                    <!-- Tabs Content -->
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="block-tabs-invoice">
                                            <div class="table-responsive">
                                                <table id="invoice" class="table">
                                                    <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th>DATE</th>
                                                        <th>FORM NUMBER</th>
                                                        <th>NOTES</th>
                                                        <th>AVAILABLE INVOICE</th>
                                                        <th>INVOICE</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <input type="hidden" value="{{ count($list_invoice)}}"
                                                           id="count-invoice">
                                                    <?php $i = 1;?>
                                                    @foreach($list_invoice as $invoice)
                                                        <?php
                                                        $invoice_remaining = \Point\Framework\Helpers\ReferHelper::remaining(get_class($invoice),
                                                                $invoice->id, $invoice->total);
                                                        $refer_to = ReferHelper::getReferTo(get_class($invoice),
                                                                $invoice->id,
                                                                get_class($payment_order),
                                                                $payment_order->id);
                                                        $refer_to_amount = 0;
                                                        if ($refer_to) {
                                                            $refer_to_amount = $refer_to->amount;
                                                        }

                                                        $url = url('expedition/point/invoice/basic/' . $invoice->id);
                                                        $basic = false;
                                                        if ($invoice->type_of_tax != null && $invoice->type_of_fee != null) {
                                                            $url = url('expedition/point/invoice/' . $invoice->id);
                                                            $basic = true;
                                                        }

                                                        ?>
                                                        <tr>
                                                            <td class="text-center">
                                                                <input type="hidden" name="invoice_rid[]"
                                                                       value="{{$invoice->formulir_id}}">
                                                                <input type="checkbox" @if($refer_to) checked
                                                                       @endif id="id-invoice-{{$invoice->formulir_id}}"
                                                                       class="row-id-{{$i}}" name="invoice_id[]"
                                                                       value="{{$invoice->formulir_id}}"
                                                                       onclick="updateInvoice()">
                                                            </td>
                                                            <td>{{ date_format_view($invoice->formulir->form_date) }} </td>
                                                            <td>
                                                                <a href="{{ $url }}">{{ $invoice->formulir->form_number}}</a>
                                                                @if($basic == true)
                                                                    <br><i class="fa fa-caret-down"></i> <a
                                                                            data-toggle="collapse"
                                                                            href="#collapse{{$i}}">
                                                                        <small>Detail</small>
                                                                    </a>
                                                                @endif
                                                            </td>
                                                            <td>{{ $invoice->formulir->notes }}</td>
                                                            <td>{{ number_format_price($invoice_remaining + $refer_to_amount) }}</td>
                                                            <td>
                                                                <input type="text"
                                                                       id="total-invoice-{{$invoice->formulir_id}}"
                                                                       name="amount_invoice[]"
                                                                       class="form-control format-price row-total-{{$i}}"
                                                                       onkeyup="updateInvoice()"
                                                                       value="{{$refer_to_amount}}"/>
                                                                <input type="hidden" name="available_invoice[]"
                                                                       value="{{$refer_to_amount}}"/>
                                                                <input type="hidden" name="original_amount_invoice[]"
                                                                       value="{{$invoice->total}}"/>
                                                                <input type="hidden" name="invoice_amount_edit[]"
                                                                       value="{{$invoice_remaining + $refer_to_amount}}"/>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="6" style="border-top: none;">
                                                                <div id="collapse{{$i}}"
                                                                     class="panel-collapse collapse">
                                                                    <b>Description</b>
                                                                    <ul class="list-group">
                                                                        @foreach($invoice->items as $invoice_item)
                                                                            <li class="list-group-item">
                                                                                <small>{{ $invoice_item->item->codeName }}
                                                                                    # {{ number_format_price($invoice_item->quantity) .' '.$invoice_item->unit }}
                                                                                    <span class="pull-right">{{ number_format_quantity($invoice_item->item_fee) }}</span>
                                                                                </small>
                                                                            </li>
                                                                        @endforeach
                                                                        <li class="list-group-item">
                                                                            <small> Discount
                                                                                <span class="pull-right">{{ number_format_price($invoice->discount) }}</span>
                                                                            </small>
                                                                        </li>
                                                                        <li class="list-group-item">
                                                                            <small> Tax Base
                                                                                <span class="pull-right">{{ number_format_price($invoice->tax_base) }}</span>
                                                                            </small>
                                                                        </li>
                                                                        <li class="list-group-item">
                                                                            <small> Tax
                                                                                <span class="pull-right">{{ number_format_price($invoice->tax) }}</span>
                                                                            </small>
                                                                        </li>
                                                                        <li class="list-group-item">
                                                                            <small> Total
                                                                                <span class="pull-right">{{ number_format_price($invoice->total) }}</span>
                                                                            </small>
                                                                        </li>
                                                                        
                                                                    </ul>
                                                                </div>
                                                            </td>

                                                        </tr>
                                                        <?php $i++;?>
                                                    @endforeach
                                                    </tbody>

                                                </table>
                                            </div>
                                        </div>

                                        <!-- CUTOFF -->
                                        <div class="tab-pane" id="block-tabs-cutoff">
                                            <div class="table-responsive">
                                                <table id="cutoff-datatable" class="table table-striped">
                                                    <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th>DATE</th>
                                                        <th>FORM NUMBER</th>
                                                        <th>NOTES</th>
                                                        <th>AVAILABLE AMOUNT</th>
                                                        <th>CUTOFF</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($list_cut_off_payable as $cut_off_payable)
                                                        <?php
                                                        $cut_off_payable_remaining = \Point\Framework\Helpers\ReferHelper::remaining(
                                                            get_class($cut_off_payable),
                                                            $cut_off_payable->id, $cut_off_payable->amount);
                                                        $refer_to = ReferHelper::getReferTo(
                                                            get_class($cut_off_payable),
                                                            $cut_off_payable->id,
                                                            get_class($payment_order),
                                                            $payment_order->id);
                                                        $refer_to_amount = 0;
                                                        if ($refer_to) {
                                                            $refer_to_amount = $refer_to->amount;
                                                        }

                                                        if (! $refer_to_amount > 0) {
                                                            continue;
                                                        }

                                                        ?>
                                                        <tr>
                                                            <td class="text-center">
                                                                <input type="hidden" name="cut_off_rid[]"
                                                                       value="{{$cut_off_payable->id}}">
                                                                <input type="checkbox" @if($refer_to) checked @endif
                                                                       id="id-cutoff-{{$cut_off_payable->id}}"
                                                                       class="row-id" name="cut_off_id[]"
                                                                       value="{{$cut_off_payable->id}}"
                                                                       onclick="updateCutoff()">
                                                            </td>
                                                            <td>{{ date_Format_view($cut_off_payable->cutoffPayable->formulir->form_date) }}</td>
                                                            <td>
                                                                <a href="{{ url('accounting/point/cut-off/payable/'.$cut_off_payable->cutoffPayable->id) }}">{{ $cut_off_payable->cutoffPayable->formulir->form_number}}</a>
                                                            </td>
                                                            <td>{{ $cut_off_payable->notes }}</td>
                                                            <td>{{ number_format_price($cut_off_payable_remaining + $refer_to_amount) }}</td>
                                                            <td>
                                                                <input type="text"
                                                                       id="total-cutoff-{{$cut_off_payable->cutoffPayable->formulir->id}}"
                                                                       name="amount_cutoff[]"
                                                                       class="form-control format-price row-total"
                                                                       onkeyup="updateCutoff()" value="{{$refer_to_amount}}"/>
                                                                <input type="hidden" name="available_cutoff[]"
                                                                       value="{{$refer_to_amount}}"/>
                                                                <input type="hidden" name="original_amount_cutoff[]"
                                                                       value="{{$cut_off_payable->amount}}"/>
                                                                <input type="hidden" name="cutoff_amount_edit[]"
                                                                       value="{{$cut_off_payable_remaining + $refer_to_amount}}"/>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="tab-pane" id="block-tabs-downpayment">
                                            <div class="table-responsive">
                                                <table id="downpayment-datatable"
                                                       class="table table-striped">
                                                    <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th>DATE</th>
                                                        <th>FORM NUMBER</th>
                                                        <th>NOTES</th>
                                                        <th>AVAILABLE DOWNPAYMENT</th>
                                                        <th>DOWNPAYMENT</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($list_downpayment as $downpayment)
                                                        <?php
                                                        $downpayment_remaining = \Point\Framework\Helpers\ReferHelper::remaining(get_class($downpayment),
                                                                $downpayment->id, $downpayment->amount);
                                                        $refer_to = Point\Framework\Helpers\ReferHelper::getReferTo(get_class($downpayment),
                                                                $downpayment->id, get_class($payment_order), $payment_order->id);
                                                        $refer_to_amount = 0;
                                                        if ($refer_to) {
                                                            $refer_to_amount = $refer_to->amount * -1;
                                                        }
                                                        ?>
                                                        <tr>
                                                            <td class="text-center">
                                                                <input type="hidden" name="downpayment_rid[]"
                                                                       value="{{$downpayment->formulir_id}}">
                                                                <input type="checkbox" @if($refer_to) checked
                                                                       @endif id="id-downpayment-{{$downpayment->formulir_id}}"
                                                                       class="row-id" name="downpayment_id[]"
                                                                       value="{{$downpayment->formulir_id}}"
                                                                       onclick="updateDownpayment()">
                                                            </td>
                                                            <td>{{ date_Format_view($downpayment->formulir->form_date) }}</td>
                                                            <td>
                                                                <a href="{{ url('expedition/point/downpayment/'.$downpayment->id) }}">{{ $downpayment->formulir->form_number}}</a>
                                                            </td>
                                                            <td>{{ $downpayment->formulir->notes }}</td>
                                                            <td>{{ number_format_price($downpayment_remaining + $refer_to_amount) }}</td>
                                                            <td>
                                                                <input type="text"
                                                                       id="amount-downpayment-{{$downpayment->formulir_id}}"
                                                                       name="amount_downpayment[]"
                                                                       class="form-control format-price row-amount"
                                                                       onkeyup="updateDownpayment()"
                                                                       value="{{$refer_to_amount}}"/>
                                                                <input type="hidden" name="available_downpayment[]"
                                                                       value="{{$refer_to_amount}}"/>
                                                                <input type="hidden"
                                                                       name="original_amount_downpayment[]"
                                                                       value="{{$downpayment->total}}"/>
                                                                <input type="hidden" name="downpayment_amount_edit[]"
                                                                       value="{{$downpayment_remaining + $refer_to_amount}}"/>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>

                                                </table>
                                            </div>
                                        </div>

                                        <div class="tab-pane" id="block-tabs-other">
                                            <div class="table-responsive">
                                                <table id="item-datatable" class="table table-striped">
                                                    <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th>ACCOUNT</th>
                                                        <th>NOTES</th>
                                                        <th>AMOUNT</th>
                                                        <th>ALLOCATION</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody class="manipulate-row">
                                                    <?php $counter = 0;?>
                                                    @foreach($payment_order->others as $payment_order_other)
                                                        <?php $counter += 1;?>
                                                        <tr>
                                                            <td>
                                                                <a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>
                                                            </td>
                                                            <td>
                                                                <select id="item-id-'+counter+'" name="coa_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                                                    @foreach($list_coa as $coa)
                                                                    <option value="{{$coa->id}}" @if($coa->id == $payment_order_other->coa_id) selected @endif>{{$coa->account}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="text" name="other_notes[]" class="form-control" value="{{$payment_order_other->other_notes}}"/>
                                                            </td>
                                                            <td>
                                                                <input type="text" id="total-'+counter+'" name="total[]" class="form-control format-accounting row-total calculate" value="{{$payment_order_other->amount}}"/>
                                                            </td>
                                                            <td>
                                                                <select id="allocation-id-'+counter+'" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                                                    @foreach($list_allocation as $allocation)
                                                                    <option value="{{$allocation->id}}" @if($allocation->id == $payment_order_other->allocation_id) selected @endif>{{$allocation->name}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                    <tr style="height:200px">
                                                        <td><input type="button" id="addItemRow" class="btn btn-primary"value="Add Item"></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-9 control-label">Total Payment</label>
                                            <div class="col-md-3">
                                                <input readonly type="text" id="total-payment" name="total_payment"
                                                       class="form-control format-price" value="0"/>
                                                <input readonly type="hidden" id="total-invoice" name="total_invoice"
                                                       class="form-control format-price" value="0"/>
                                                <input readonly type="hidden" id="total-downpayment"
                                                       name="total_downpayment" class="form-control format-price"
                                                       value="0"/>
                                                <input readonly type="hidden" id="total-other" name="total_other"
                                                       class="form-control format-price" value="0"/>
                                                <input readonly type="hidden" id="total-cutoff" name="total_cutoff"
                                                        class="form-control format-price" value="0"/>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- END Tabs Content -->
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Person In Charge</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Creator</label>
                            <div class="col-md-6 content-show">
                                {{\Auth::user()->name}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Ask Approval To</label>
                            <div class="col-md-6">
                                <select id="approval-to" name="approval_to" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                    <option></option>
                                    @foreach($list_user_approval as $user_approval)
                                        @if($user_approval->may('approval.point.expedition.payment.order'))
                                            <option value="{{$user_approval->id}}" @if($payment_order->formulir->approval_to == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <button type="submit" class="btn btn-effect-ripple btn-primary">Review</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        var item_table = initDatatable('#item-datatable');
        var downpayment_table = initDatatable('#downpayment-datatable');
        initDatatable('#invoice-datatable');
        initDatatable('#cutoff-datatable');
        var counter = 1;
        
        $('#addItemRow').on('click', function () {
            item_table.row.add([
                '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
                '<select id="item-id-' + counter + '" name="coa_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                + '<option ></option>'
                @foreach($list_coa as $coa)
                + '<option value="{{$coa->id}}">{{$coa->account}}</option>'
                @endforeach
                + '</select>',
                '<input type="text" name="other_notes[]" class="form-control" value="" />',
                '<input type="text" id="total-' + counter + '" name="total[]" class="form-control format-accounting row-total calculate" value="0" />',
                '<select id="allocation-id-' + counter + '" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                + '<option value="1">WITHOUT ALLOCATION</option>'
                @foreach($list_allocation as $allocation)
                + '<option value="{{$allocation->id}}">{{$allocation->name}}</option>'
                @endforeach
                + '</select>'
            ]).draw(false);

            initSelectize('#item-id-' + counter);
            initSelectize('#allocation-id-' + counter);
            initSelectize('#unit-id-' + counter);
            initFormatNumber();

            $("textarea").on("click", function () {
                $(this).select();
            });
            $("input[type='text']").on("click", function () {
                $(this).select();
            });
            $('.calculate').keyup(function () {
                calculate();
            });
            
            counter++;
        });

        $('#item-datatable tbody').on('click', '.remove-row', function () {
            item_table.row($(this).parents('tr')).remove().draw();
            calculate();
        });

        $(document).on("keypress", 'form', function (e) {
            var code = e.keyCode || e.which;
            if (code == 13) {
                e.preventDefault();
                return false;
            }
        });

        $('.calculate').keyup(function () {
            calculate();
        });

        $(function () {
            calculate();
            updateDownpayment();
            updateCutoff();
            updateInvoice();

        });

        function updateDownpayment() {
            var rows = $("#downpayment-datatable").dataTable().fnGetNodes();
            var total_downpayment = 0;
            for (var i = 0; i < rows.length; i++) {
                if ($(rows[i]).find(".row-id").prop('checked')) {
                    total_downpayment += dbNum($(rows[i]).find(".row-amount").val());
                }
            }
            var total_invoice = dbNum($('#total-invoice').val());
            var total_cutoff = dbNum($('#total-cutoff').val());
            var total_other = dbNum($('#total-other').val());
            $('#total-downpayment').val(appNum(total_downpayment));
            $('#total-payment').val(appNum(total_invoice + total_cutoff + total_other - total_downpayment));
        }

        function updateInvoice() {
            var rows = $("#count-invoice").val();
            var total_invoice = 0;
            var total_payment = 0;
            for (var i = 1; i <= rows; i++) {
                if ($(".row-id-" + i).prop('checked')) {
                    total_invoice += total_payment + dbNum($(".row-total-" + i).val());
                }
            }
            var total_downpayment = dbNum($('#total-downpayment').val());
            var total_other = dbNum($('#total-other').val());
            var total_cutoff = dbNum($('#total-cutoff').val());
            $('#total-invoice').val(appNum(total_invoice));
            $('#total-payment').val(appNum(total_invoice + total_cutoff + total_other - total_downpayment));
        }

        function updateCutoff() {
            var rows = $("#cutoff-datatable").dataTable().fnGetNodes();
            var total_cutoff = 0;
            for (var i = 0; i < rows.length; i++) {
                if ($(rows[i]).find(".row-id").prop('checked')) {
                    total_cutoff += dbNum($(rows[i]).find(".row-total").val());
                }
            }
            var total_downpayment = dbNum($('#total-downpayment').val());
            var total_invoice = dbNum($('#total-invoice').val());
            var total_other = dbNum($('#total-other').val());
            $('#total-cutoff').val(appNum(total_cutoff));
            $('#total-payment').val(appNum(total_invoice + total_other + total_cutoff - total_downpayment));
        }

        function calculate() {
            var rows = $("#item-datatable").dataTable().fnGetNodes();
            var total_other = 0;
            for (var i = 0; i < rows.length; i++) {
                total_other += dbNum($(rows[i]).find(".row-total").val());
            }
            var total_invoice = dbNum($('#total-invoice').val());
            var total_cutoff = dbNum($('#total-cutoff').val());
            var total_downpayment = dbNum($('#total-downpayment').val());
            $('#total-other').val(appNum(total_other));
            $('#total-payment').val(appNum(total_other + total_cutoff + total_invoice - total_downpayment));
        }
    </script>
@stop
