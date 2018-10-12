@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/payment-collection') }}">Payment Collection</a></li>
            <li>Edit</li>
        </ul>
        <h2 class="sub-header">Payment Collection</h2>
        @include('point-sales::app.sales.point.sales.payment-collection._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('sales/point/indirect/payment-collection/'.$payment_collection->id.'/edit-review')}}"
                      method="post" class="form-horizontal form-bordered">
                    {!! csrf_field() !!}

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Payment Collection</legend>
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
                        <label class="col-md-3 control-label">Customer</label>

                        <div class="col-md-6 content-show">
                            <input type="hidden" name="person_id" value="{{$person->id}}">
                            {!! get_url_person($person->id) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Payment Type</label>

                        <div class="col-md-6">
                            <select
                                id="payment_type"
                                name="payment_type"
                                class="selectize"
                                style="width: 100%;"
                                data-placeholder="Please choose">
                                <option
                                    value="cash"
                                    {{ $payment_collection->payment_type == 'cash' ? 'selected' : '' }}>
                                    Cash
                                </option>
                                <option
                                    value="bank"
                                    {{ $payment_collection->payment_type == 'bank' ? 'selected' : '' }}>
                                    Bank
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>

                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="block full">
                                <!-- Block Tabs Title -->
                                <div class="block-title">
                                    <ul class="nav nav-tabs" data-toggle="tabs">
                                        <li class="active"><a href="#block-tabs-invoice">INVOICE</a></li>
                                        <li><a href="#block-tabs-cutoff">CUTOFF</a></li>
                                        <li><a href="#block-tabs-downpayment">DOWNPAYMENT</a></li>
                                        <li><a href="#block-tabs-retur">RETUR</a></li>
                                        <li><a href="#block-tabs-other">OTHERS</a></li>
                                    </ul>
                                </div>
                                <!-- END Block Tabs Title -->

                                <!-- Tabs Content -->
                                <div class="tab-content">
                                    <!-- INVOICE -->
                                    <div class="tab-pane active" id="block-tabs-invoice">
                                        <div class="table-responsive">
                                            <table id="invoice-datatable" class="table table-striped">
                                                <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>DATE</th>
                                                    <th>FORM NUMBER</th>
                                                    <th>NOTES</th>
                                                    <th>ITEM</th>
                                                    <th>AVAILABLE INVOICE</th>
                                                    <th>INVOICE</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($list_invoice as $invoice)
                                                    <?php
                                                    $invoice_remaining = \Point\Framework\Helpers\ReferHelper::remaining(get_class($invoice),
                                                            $invoice->id, $invoice->total);
                                                    $refer_to = ReferHelper::getReferTo(get_class($invoice),
                                                            $invoice->id,
                                                            get_class($payment_collection),
                                                            $payment_collection->id);
                                                    $refer_to_amount = 0;
                                                    if ($refer_to) {
                                                        $refer_to_amount = $refer_to->amount;
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td class="text-center">
                                                            <input type="hidden" name="invoice_reference_id[]" value="{{$invoice->id}}">
                                                            <input type="hidden" name="invoice_reference_type[]" value="{{get_class($invoice)}}">
                                                            <input type="hidden" name="invoice_rid[]"
                                                                   value="{{$invoice->formulir_id}}">
                                                            <input type="checkbox" @if($refer_to) checked
                                                                   @endif id="id-invoice-{{$invoice->formulir_id}}"
                                                                   class="row-id" name="invoice_id[]"
                                                                   value="{{$invoice->formulir_id}}"
                                                                   onclick="updateInvoice()">
                                                        </td>
                                                        <td>{{ date_Format_view($invoice->formulir->form_date) }}</td>
                                                        <td>
                                                            <a href="{{ url('sales/point/indirect/invoice/'.$invoice->id) }}">{{ $invoice->formulir->form_number}}</a>
                                                        </td>
                                                        <td>{{ $invoice->formulir->notes }}</td>
                                                        <td>
                                                            @foreach($invoice->items as $invoice_item)
                                                                {{ $invoice_item->item->codeName }} {{ number_format_price($invoice_item->quantity) .' '.$invoice_item->unit }}
                                                                <br/>
                                                            @endforeach
                                                        </td>
                                                        <td>{{ number_format_price($invoice_remaining + $refer_to_amount) }}</td>
                                                        <td>
                                                            <input type="text"
                                                                   id="total-invoice-{{$invoice->formulir_id}}"
                                                                   name="amount_invoice[]"
                                                                   class="form-control format-price row-total"
                                                                   onkeyup="updateInvoice()" value="{{$refer_to_amount}}"/>
                                                            <input type="hidden" name="available_invoice[]"
                                                                   value="{{$refer_to_amount}}"/>
                                                            <input type="hidden" name="original_amount_invoice[]"
                                                                   value="{{$invoice->total}}"/>
                                                            <input type="hidden" name="invoice_amount_edit[]"
                                                                   value="{{$invoice_remaining + $refer_to_amount}}"/>
                                                        </td>
                                                    </tr>
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
                                                @foreach($list_cut_off_receivable as $cut_off_receivable)
                                                    <?php
                                                    $cut_off_receivable_remaining = \Point\Framework\Helpers\ReferHelper::remaining(
                                                        get_class($cut_off_receivable),
                                                        $cut_off_receivable->id, $cut_off_receivable->amount);
                                                    $refer_to = ReferHelper::getReferTo(
                                                        get_class($cut_off_receivable),
                                                        $cut_off_receivable->id,
                                                        get_class($payment_collection),
                                                        $payment_collection->id);
                                                    $refer_to_amount = 0;
                                                    if ($refer_to) {
                                                        $refer_to_amount = $refer_to->amount;
                                                    }

                                                    ?>
                                                    <tr>
                                                        <td class="text-center">
                                                            <input type="hidden" name="cutoff_reference_id[]" value="{{$cut_off_receivable->id}}">
                                                            <input type="hidden" name="cutoff_reference_type[]" value="{{get_class($cut_off_receivable)}}">
                                                            <input type="hidden" name="cut_off_rid[]"
                                                                   value="{{$cut_off_receivable->id}}">
                                                            <input type="checkbox" @if($refer_to) checked @endif
                                                                   id="id-cutoff-{{$cut_off_receivable->id}}"
                                                                   class="row-id" name="cut_off_id[]"
                                                                   value="{{$cut_off_receivable->id}}"
                                                                   onclick="updateCutoff()">
                                                        </td>
                                                        <td>{{ date_Format_view($cut_off_receivable->cutoffReceivable->formulir->form_date) }}</td>
                                                        <td>
                                                            <a href="{{ url('accounting/point/cut-off/receivable/'.$cut_off_receivable->cutoffReceivable->id) }}">{{ $cut_off_receivable->cutoffReceivable->formulir->form_number}}</a>
                                                        </td>
                                                        <td>{{ $cut_off_receivable->notes }}</td>
                                                        <td>{{ number_format_price($cut_off_receivable_remaining + $refer_to_amount) }}</td>
                                                        <td>
                                                            <input type="text"
                                                                   id="total-cutoff-{{$cut_off_receivable->cutoffReceivable->formulir->id}}"
                                                                   name="amount_cutoff[]"
                                                                   class="form-control format-price row-total"
                                                                   onkeyup="updateCutoff()" value="{{$refer_to_amount}}"/>
                                                            <input type="hidden" name="available_cutoff[]"
                                                                   value="{{$refer_to_amount}}"/>
                                                            <input type="hidden" name="original_amount_cutoff[]"
                                                                   value="{{$cut_off_receivable->amount}}"/>
                                                            <input type="hidden" name="cutoff_amount_edit[]"
                                                                   value="{{$cut_off_receivable_remaining + $refer_to_amount}}"/>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- DOWNPAYMENT -->
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
                                                    $refer_to = ReferHelper::getReferTo(get_class($downpayment),
                                                            $downpayment->id,
                                                            get_class($payment_collection),
                                                            $payment_collection->id);
                                                    $refer_to_amount = 0;
                                                    if ($refer_to) {
                                                        $refer_to_amount = $refer_to->amount * -1;
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td class="text-center">
                                                            <input type="hidden" name="downpayment_reference_id[]" value="{{$downpayment->id}}">
                                                            <input type="hidden" name="downpayment_reference_type[]" value="{{get_class($downpayment)}}">
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
                                                            <a href="{{ url('sales/point/indirect/downpayment/'.$downpayment->id) }}">{{ $downpayment->formulir->form_number}}</a>
                                                        </td>
                                                        <td>{{ $downpayment->formulir->notes }}</td>
                                                        <td>{{ number_format_price($downpayment_remaining + $refer_to_amount) }}</td>
                                                        <td>
                                                            <input type="text"
                                                                   id="amount-downpayment-{{$downpayment->formulir_id}}"
                                                                   name="amount_downpayment[]"
                                                                   class="form-control format-price row-amount"
                                                                   onkeyup="updateDownpayment()" value="{{$refer_to_amount}}"/>
                                                            <input type="hidden" name="available_downpayment[]"
                                                                   value="{{$refer_to_amount}}"/>
                                                            <input type="hidden" name="original_amount_downpayment[]"
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

                                    <!-- RETUR -->
                                    <div class="tab-pane" id="block-tabs-retur">
                                        <div class="table-responsive">
                                            <table id="retur-datatable" class="table table-striped">
                                                <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>DATE</th>
                                                    <th>FORM NUMBER</th>
                                                    <th>NOTES</th>
                                                    <th>ITEM</th>
                                                    <th>AVAILABLE RETUR</th>
                                                    <th>RETUR</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($list_retur as $retur)
                                                    <?php
                                                    $retur_remaining = \Point\Framework\Helpers\ReferHelper::remaining(get_class($retur),
                                                            $retur->id, $retur->total);
                                                    $refer_to = ReferHelper::getReferTo(get_class($retur),
                                                            $retur->id,
                                                            get_class($payment_collection),
                                                            $payment_collection->id);
                                                    $refer_to_amount = 0;
                                                    if ($refer_to) {
                                                        $refer_to_amount = $refer_to->amount * -1;
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td class="text-center">
                                                            <input type="hidden" name="retur_reference_id[]" value="{{$retur->id}}">
                                                            <input type="hidden" name="retur_reference_type[]" value="{{get_class($retur)}}">
                                                            <input type="hidden" name="retur_rid[]"
                                                                   value="{{$retur->formulir_id}}">
                                                            <input type="checkbox" @if($refer_to) checked
                                                                   @endif id="id-retur-{{$retur->formulir_id}}"
                                                                   class="row-id" name="retur_id[]"
                                                                   value="{{$retur->formulir_id}}"
                                                                   onclick="updateRetur()">
                                                        </td>
                                                        <td>{{ date_Format_view($retur->formulir->form_date) }}</td>
                                                        <td>
                                                            <a href="{{ url('sales/point/indirect/retur/'.$retur->id) }}">{{ $retur->formulir->form_number}}</a>
                                                        </td>
                                                        <td>{{ $retur->formulir->notes }}</td>
                                                        <td>
                                                            @foreach($retur->items as $retur_item)
                                                                {{ $retur_item->item->codeName }} {{ number_format_price($retur_item->quantity) .' '.$retur_item->unit }}
                                                                <br/>
                                                            @endforeach
                                                        </td>
                                                        <td>{{ number_format_price($retur_remaining + $refer_to_amount) }}</td>
                                                        <td>
                                                            <input type="text" id="total-retur-{{$retur->formulir_id}}"
                                                                   name="amount_retur[]"
                                                                   class="form-control format-price row-total"
                                                                   onkeyup="updateRetur()"
                                                                   value="{{$refer_to_amount}}"/>
                                                            <input type="hidden" name="available_retur[]"
                                                                   value="{{$refer_to_amount}}"/>
                                                            <input type="hidden" name="original_amount_retur[]"
                                                                   value="{{$retur->total}}"/>
                                                            <input type="hidden" name="retur_amount_edit[]"
                                                                   value="{{$retur_remaining + $refer_to_amount}}"/>
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
                                                    <th>ALLOCATION</th>
                                                    <th>NOTES</th>
                                                    <th>AMOUNT</th>
                                                </tr>
                                                </thead>
                                                <tbody class="manipulate-row">
                                                <?php $counter = 0;?>
                                                @foreach($payment_collection->others as $payment_collection_other)
                                                    <?php $counter += 1;?>
                                                    <tr>
                                                        <td>
                                                            <a href="javascript:void(0)"
                                                               class="remove-row btn btn-danger"><i
                                                                        class="fa fa-trash"></i></a>
                                                        </td>
                                                        <td>
                                                            <select id="item-id-'+counter+'" name="coa_id[]"
                                                                    class="selectize" style="width: 100%;"
                                                                    data-placeholder="Choose one..">
                                                                @foreach($list_coa as $coa)
                                                                    <option value="{{$coa->id}}"
                                                                            @if($coa->id == $payment_collection_other->coa_id) selected @endif>{{$coa->account}}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select id="allocation-id-{{$counter}}" name="allocation_id[]" class="selectize"
                                                                    style="width: 100%;" data-placeholder="Choose one..">
                                                                @foreach($list_allocation as $allocation)
                                                                    <option @if ($payment_collection_other->allocation_id == $allocation->id) selected
                                                                            @endif value="{{$allocation->id}}">{{$allocation->name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="text" name="other_notes[]" class="form-control"
                                                                   value="{{$payment_collection_other->other_notes}}"/>
                                                        </td>
                                                        <td>
                                                            <input type="text" id="total-'+counter+'" name="total[]"
                                                                   class="form-control format-price-alt row-total calculate"
                                                                   value="{{$payment_collection_other->amount}}"/>
                                                        </td>
                                                    </tr>
                                                @endforeach
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
                                </div>
                                <!-- END Tabs Content -->
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Total Payment</label>

                        <div class="col-md-6">
                            <input readonly type="text" id="total-payment" name="total_payment"
                                   class="form-control format-price" value="0"/>
                            <input readonly type="hidden" id="total-invoice" name="total_invoice"
                                   class="form-control format-price" value="0"/>
                            <input readonly type="hidden" id="total-downpayment" name="total_downpayment"
                                   class="form-control format-price" value="0"/>
                            <input readonly type="hidden" id="total-retur" name="total_downpayment"
                                   class="form-control format-price" value="0"/>
                            <input readonly type="hidden" id="total-other" name="total_other"
                                   class="form-control format-price" value="0"/>
                            <input readonly type="hidden" id="total-cutoff" name="total_cutoff"
                                   class="form-control format-price" value="0"/>
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
                                {{\Auth::user()->name}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Ask Approval To</label>

                            <div class="col-md-6">
                                <select id="approval-to" name="approval_to" class="selectize" style="width: 100%;"
                                        data-placeholder="Choose one..">
                                    <option></option>
                                    @foreach($list_user_approval as $user_approval)

                                        @if($user_approval->may('approval.point.sales.payment.collection'))
                                            <option value="{{$user_approval->id}}"
                                                    @if(old('user_approval') == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
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
        initDatatable('#downpayment-datatable');
        initDatatable('#invoice-datatable');
        initDatatable('#cutoff-datatable');
        initDatatable('#retur-datatable');
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
                 '<select id="allocation-id-' + counter + '" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                @foreach($list_allocation as $allocation)
                + '<option value="{{$allocation->id}}">{{$allocation->name}}</option>'
                @endforeach
                + '</select>',
                '<input type="text" name="other_notes[]" class="form-control" value="" />',
                '<input type="text" id="total-' + counter + '" name="total[]" class="form-control format-price-alt row-total calculate" value="0" />'
            ]).draw(false);

            initSelectize('#item-id-' + counter);
            initSelectize('#unit-id-' + counter);
            initSelectize('#allocation-id-' + counter);
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
            updateInvoice();
            updateRetur();
            updateCutoff();
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
            var total_retur = dbNum($('#total-retur').val());
            var total_cutoff = dbNum($('#total-cutoff').val());
            var total_other = dbNum($('#total-other').val());
            $('#total-downpayment').val(appNum(total_downpayment));
            $('#total-payment').val(appNum(total_invoice + total_cutoff + total_other - total_downpayment - total_retur));
        }

        function updateInvoice() {
            var rows = $("#invoice-datatable").dataTable().fnGetNodes();
            var total_invoice = 0;
            for (var i = 0; i < rows.length; i++) {
                if ($(rows[i]).find(".row-id").prop('checked')) {
                    total_invoice += dbNum($(rows[i]).find(".row-total").val());
                }
            }
            var total_downpayment = dbNum($('#total-downpayment').val());
            var total_retur = dbNum($('#total-retur').val());
            var total_cutoff = dbNum($('#total-cutoff').val());
            var total_other = dbNum($('#total-other').val());
            $('#total-invoice').val(appNum(total_invoice));
            $('#total-payment').val(appNum(total_invoice + total_cutoff + total_other - total_downpayment - total_retur));
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
            var total_retur = dbNum($('#total-retur').val());
            var total_other = dbNum($('#total-other').val());
            $('#total-cutoff').val(appNum(total_cutoff));
            $('#total-payment').val(appNum(total_invoice + total_other + total_cutoff - total_downpayment - total_retur));
        }

        function updateRetur() {
            var rows = $("#retur-datatable").dataTable().fnGetNodes();
            var total_retur = 0;
            for (var i = 0; i < rows.length; i++) {
                if ($(rows[i]).find(".row-id").prop('checked')) {
                    total_retur += dbNum($(rows[i]).find(".row-total").val());
                }
            }
            var total_downpayment = dbNum($('#total-downpayment').val());
            var total_cutoff = dbNum($('#total-cutoff').val());
            var total_invoice = dbNum($('#total-invoice').val());
            var total_other = dbNum($('#total-other').val());
            $('#total-retur').val(appNum(total_retur));
            $('#total-payment').val(appNum(total_invoice + total_cutoff + total_other - total_downpayment - total_retur));
        }

        function calculate() {
            var rows = $("#item-datatable").dataTable().fnGetNodes();
            var total_other = 0;
            for (var i = 0; i < rows.length; i++) {
                total_other += dbNum($(rows[i]).find(".row-total").val());
            }
            var total_invoice = dbNum($('#total-invoice').val());
            var total_retur = dbNum($('#total-retur').val());
            var total_downpayment = dbNum($('#total-downpayment').val());
            $('#total-other').val(appNum(total_other));
            $('#total-payment').val(appNum(total_other + total_invoice - total_downpayment - total_retur));
        }
    </script>
@stop
