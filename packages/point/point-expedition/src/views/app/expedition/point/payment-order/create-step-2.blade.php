@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-expedition::app/expedition/point/payment-order/_breadcrumb')
            <li>Create Step 2</li>
        </ul>
        <h2 class="sub-header">PAYMENT ORDER</h2>
        @include('point-expedition::app.expedition.point.payment-order._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('expedition/point/payment-order/create-step-3')}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}

                    <input type="hidden" id="count-invoice" value="{{ count($list_invoice) }}">
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
                            {{$expedition->codeName}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Payment Type</label>
                        <div class="col-md-6">
                            <select id="payment_type" name="payment_type" class="selectize" style="width: 100%;"
                                    data-placeholder="Please choose">
                                <option value="cash">Cash</option>
                                <option value="bank">Bank</option>
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
                                                <table id="invoice-datatable" class="table">
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
                                                    <?php $i = 0;?>
                                                    @foreach($list_invoice as $invoice)
                                                        <?php
                                                        $invoice_remaining = \Point\Framework\Helpers\ReferHelper::remaining(get_class($invoice),
                                                                $invoice->id, $invoice->total);
                                                        ?>
                                                        <tr>
                                                            <td class="text-center" rowspan="2">
                                                                <input type="hidden" name="invoice_reference_id[]" value="{{$invoice->id}}">
                                                                <input type="hidden" name="invoice_reference_type[]" value="{{get_class($invoice)}}">
                                                                <input type="hidden" name="invoice_rid[]"
                                                                       value="{{$invoice->formulir_id}}">
                                                                <input type="checkbox" id="id-invoice-{{$i}}"
                                                                       class="row-id" name="invoice_id[]"
                                                                       value="{{$invoice->formulir_id}}"
                                                                       onclick="calculateInvoice()">
                                                            </td>
                                                            <td>{{ date_Format_view($invoice->formulir->form_date) }}</td>
                                                            <td>
                                                                <a href="{{ $invoice->getLinkInvoice() }}">{{ $invoice->formulir->form_number}}</a>
                                                                @if($invoice->type_of_tax != null && $invoice->type_of_fee != null)
                                                                    <br><i class="fa fa-caret-down"></i> <a
                                                                            data-toggle="collapse"
                                                                            href="#collapse{{$i}}">
                                                                        <small>Detail</small>
                                                                    </a>
                                                                @endif
                                                            </td>
                                                            <td>{{ $invoice->formulir->notes }}</td>
                                                            <td class="text-right">{{ number_format_price($invoice_remaining) }}</td>
                                                            <td>
                                                                <input type="text" id="total-invoice-{{$i}}"
                                                                       name="amount_invoice[]"
                                                                       class="form-control format-price row-total text-right"
                                                                       onkeyup="calculateInvoice()"
                                                                       value="{{$invoice_remaining}}"/>
                                                                <input type="hidden" name="available_invoice[]"
                                                                       value="{{$invoice_remaining}}"/>
                                                                <input type="hidden" name="original_amount_invoice[]"
                                                                       value="{{$invoice->total}}"/>
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
                                                        $cut_off_payable_remaining = \Point\Framework\Helpers\ReferHelper::remaining(get_class($cut_off_payable),
                                                                $cut_off_payable->id, $cut_off_payable->amount);
                                                        if (! $cut_off_payable_remaining > 0) {
                                                            continue;
                                                        }
                                                        ?>
                                                        <tr>
                                                            <td class="text-center">
                                                                <input type="hidden" name="cutoff_reference_id[]" value="{{$cut_off_payable->id}}">
                                                                <input type="hidden" name="cutoff_reference_type[]" value="{{get_class($cut_off_payable)}}">
                                                                <input type="hidden" name="cut_off_rid[]" value="{{$cut_off_payable->id}}">
                                                                <input type="checkbox"
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
                                                            <td>{{ number_format_price($cut_off_payable_remaining) }}</td>
                                                            <td>
                                                                <input type="text"
                                                                       id="total-cutoff-{{$cut_off_payable->cutoffPayable->formulir->id}}"
                                                                       name="amount_cutoff[]"
                                                                       class="form-control format-price row-total"
                                                                       onkeyup="updateCutoff()" value="{{$cut_off_payable_remaining}}"/>
                                                                <input type="hidden" name="available_cutoff[]"
                                                                       value="{{$cut_off_payable_remaining}}"/>
                                                                <input type="hidden" name="original_amount_cutoff[]"
                                                                       value="{{$cut_off_payable->amount}}"/>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="tab-pane" id="block-tabs-downpayment">
                                            <div class="table-responsive">
                                                <table id="downpayment-datatable" class="table table-striped">
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
                                                        if (! $downpayment_remaining > 0) {
                                                            continue;
                                                        }

                                                        ?>
                                                        <tr>
                                                            <td class="text-center">
                                                                <input type="hidden" name="downpayment_reference_id[]" value="{{$downpayment->id}}">
                                                                <input type="hidden" name="downpayment_reference_type[]" value="{{get_class($downpayment)}}">
                                                                <input type="hidden" name="downpayment_rid[]" value="{{$downpayment->formulir_id}}">
                                                                <input type="checkbox"
                                                                       id="id-downpayment-{{$downpayment->formulir_id}}"
                                                                       class="row-id" name="downpayment_id[]"
                                                                       value="{{$downpayment->formulir_id}}"
                                                                       onclick="calculateDownpayment()">
                                                            </td>
                                                            <td>{{ date_Format_view($downpayment->formulir->form_date) }}</td>
                                                            <td>
                                                                <a href="{{ url('expedition/point/downpayment/'.$downpayment->id) }}">{{ $downpayment->formulir->form_number}}</a>
                                                            </td>
                                                            <td>{{ $downpayment->formulir->notes }}</td>
                                                            <td>{{ number_format_price($downpayment_remaining) }}</td>
                                                            <td>
                                                                <input type="text"
                                                                       id="amount-downpayment-{{$downpayment->formulir_id}}"
                                                                       name="amount_downpayment[]"
                                                                       class="form-control format-price row-amount text-right"
                                                                       onkeyup="calculateDownpayment()"
                                                                       value="{{$downpayment_remaining}}"/>
                                                                <input type="hidden" name="available_downpayment[]"
                                                                       value="{{$downpayment_remaining}}"/>
                                                                <input type="hidden"
                                                                       name="original_amount_downpayment[]"
                                                                       value="{{$downpayment->amount}}"/>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="tab-pane" id="block-tabs-other">
                                            <div class="table-responsive">
                                                <table id="other-datatable" class="table table-striped">
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
                                                    </tbody>
                                                    <tfoot>
                                                    <tr style="height:200px">
                                                        <td><input type="button" id="addItemRow" class="btn btn-primary"
                                                                   value="Add Item"></td>
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
                                                       class="form-control format-price text-right" value="0"/>
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
                                            <option value="{{$user_approval->id}}" @if(old('user_approval') == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
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
        var counter = 0;
        var other_datatable = initDatatable('#other-datatable');
        initDatatable('#downpayment-datatable');
        initDatatable('#invoice-datatable');
        initDatatable('#cutoff-datatable');

        $('#addItemRow').on('click', function () {
            other_datatable.row.add([
                '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
                '<select id="item-id-' + counter + '" name="coa_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                + '<option ></option>'
                @foreach($list_coa as $coa)
                + '<option value="{{$coa->id}}">{{$coa->account}}</option>'
                @endforeach
                + '</select>',
                '<input type="text" name="other_notes[]" class="form-control" value="" />',
                '<input type="text" id="total-other-' + counter + '" name="total[]"  class="form-control format-quantity row-total calculate" value="0" />',
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
        });


        function calculateDownpayment() {
            var rows = $("#downpayment-datatable").dataTable().fnGetNodes();
            var total_downpayment = 0;
            for (var i = 0; i < rows.length; i++) {
                if ($(rows[i]).find(".row-id").prop('checked')) {
                    total_downpayment += dbNum($(rows[i]).find(".row-amount").val());
                }
            }
            var total_invoice = dbNum($('#total-invoice').val());
            var total_other = dbNum($('#total-other').val());
            var total_cutoff = dbNum($('#total-cutoff').val());
            $('#total-downpayment').val(appNum(total_downpayment));
            $('#total-payment').val(appNum(total_invoice + total_other + total_cutoff - total_downpayment));
        }

        function calculateInvoice() {
            var rows = $("#count-invoice").val();
            var total_invoice = 0;
            for (var i = 0; i < rows; i++) {
                if ($("#id-invoice-" + i).prop('checked')) {
                    total_invoice += dbNum($("#total-invoice-" + i).val());
                }
            }
            
            var total_downpayment = dbNum($('#total-downpayment').val());
            var total_cutoff = dbNum($('#total-cutoff').val());
            var total_other = dbNum($('#total-other').val());
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
            var rows = $("#other-datatable").dataTable().fnGetNodes();
            var total_other = 0;
            for (var i = 0; i < rows.length; i++) {
                if ($("#total-other-" + i).length != 0) {
                    total_other += dbNum($("#total-other-" + i).val());
                }
            }
            var total_invoice = dbNum($('#total-invoice').val());
            var total_downpayment = dbNum($('#total-downpayment').val());
            $('#total-other').val(appNum(total_other));
            $('#total-payment').val(appNum(total_other + total_invoice - total_downpayment));
        }

        $('#other-datatable tbody').on('mouseup', '.remove-row', function () {
            other_datatable.row($(this).parents('tr')).remove().draw();
            calculate();
        });
    </script>
@stop
