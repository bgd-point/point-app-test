@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/purchase-order') }}">Purchase Order</a></li>
            <li>Edit</li>
        </ul>
        <h2 class="sub-header">Purchase Order</h2>
        @include('point-purchasing::app.purchasing.point.inventory.purchase-order._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('purchasing/point/purchase-order/'.$purchase_order->id)}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input name="_method" type="hidden" value="PUT">
                    <input name="reference_type" type="hidden" value="{{get_class($purchase_requisition)}}">
                    <input name="reference_id" type="hidden" value="{{$purchase_requisition->id}}">
                    <input type="hidden" name="supplier_checking" value="required">
                    <input name="action" type="hidden" value="edit">

                    <div class="form-group">
                        <label class="col-md-3 control-label">Reason edit *</label>

                        <div class="col-md-6">
                            <input type="text" name="edit_notes" class="form-control" value="{{$purchase_order->formulir->approval_message}}" autofocus>
                        </div>
                    </div>
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> REF# <a
                                            href="{{ url('purchasing/point/purchase-requisition/'.$purchase_requisition->id) }}"
                                            target="_blank">{{ $purchase_requisition->formulir->form_number }}</a>
                                </legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Required Date</label>
                            <div class="col-md-6 content-show">
                                {{ date_format_view($purchase_requisition->required_date) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Employee</label>
                            <div class="col-md-6 content-show">
                                {!! get_url_person($purchase_requisition->employee_id) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Close</label>
                            <div class="col-md-6 content-show">
                                <input type="checkbox" name="close" checked>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date</label>

                        <div class="col-md-3">
                            <input type="text" name="form_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                   value="{{ date(date_format_get(), strtotime($purchase_order->formulir->form_date)) }}">
                            <input type="hidden" name="required_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}"
                                   value="{{date(date_format_get(), strtotime($purchase_order->formulir->form_date))}}">
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
                        <label class="col-md-3 control-label">Supplier</label>

                        <div class="col-md-6">
                            @if(auth()->user()->may('create.supplier')) <div class="input-group"> @endif
                            <?php $supplier = Point\Framework\Models\Master\Person::find($purchase_order->supplier_id); ?>                        
                            <select id="contact_id" name="supplier_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                <option value="{{ $purchase_order->supplier_id }}">{{ $supplier ? $supplier->codeName : ''}}</option>
                            </select>
                            @if(auth()->user()->may('create.supplier'))
                                <span class="input-group-btn">
                                    <a href="#modal-contact" class="btn btn-effect-ripple btn-primary" data-toggle="modal">
                                        <i class="fa fa-plus"></i>
                                    </a>
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Cash Purchasing</label>
                        <div class="col-md-6 content-show">
                            <input type="checkbox" id="cash-selling" name="is_cash" @if($purchase_order->is_cash == 1) checked @endif value="1">
                            <span class="help-block">Check if this purchase need downpayment</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Include Expedition</label>
                        <div class="col-md-6 content-show">
                            <input type="checkbox" id="include-expedition" name="include_expedition"
                                   onchange="includeExpedition()"
                                   {{$purchase_order->include_expedition == 1 ? 'checked' : ''}} value="true">
                            <span class="help-block">Uncheck this if you want to order expedition service</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="{{$purchase_order->formulir->notes}}">
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
                                        <th style="min-width: 220px">ITEM *</th>
                                        <th style="min-width: 120px">QUANTITY</th>
                                        <th style="min-width: 220px">PRICE *</th>
                                        <th style="min-width: 220px">DISCOUNT</th>
                                        <th style="min-width: 220px">ALLOCATION *</th>
                                        <th style="min-width: 220px">TOTAL</th>
                                    </tr>
                                    </thead>
                                    <?php $counter = 1; ?>
                                    <tbody class="manipulate-row">
                                    @foreach($purchase_requisition->items as $purchase_requisition_item)
                                        <?php 
                                            $refer_to = ReferHelper::getReferTo(get_class($purchase_requisition_item),
                                                $purchase_requisition_item->id,
                                                get_class($purchase_order),
                                                $purchase_order->id);
                                        ?>
                                        <td>
                                            {{ $purchase_requisition_item->item->codeName }}
                                            <input type="hidden" name="reference_item_id[]"
                                                   value="{{$purchase_requisition_item->id}}"/>
                                            <input type="hidden" name="reference_item_type[]"
                                                   value="{{get_class($purchase_requisition_item)}}"/>
                                            <input type="hidden" name="reference_item_value[]"
                                                   value="{{$purchase_requisition_item->quantity}}"/>
                                            <input type="hidden" name="item_id[]"
                                                   value="{{$purchase_requisition_item->item_id}}"/>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <input id="item-quantity-{{$counter}}" type="text" name="item_quantity[]"
                                                       class="form-control format-quantity text-right calculate"
                                                       value="{{ $refer_to->quantity }}"/>
                                                <span class="input-group-addon">{{ $purchase_requisition_item->unit }}</span>
                                            </div>

                                                <input type="hidden" name="item_unit_name[]"
                                                   value="{{$purchase_requisition_item->unit}}"/>
                                                <input type="hidden" name="item_unit_converter[]"
                                                       value="{{$purchase_requisition_item->converter}}"/>
                                        </td>
                                        <td><input type="text" id="item-price-{{$counter}}" name="item_price[]"
                                                   class="form-control format-quantity calculate text-right"
                                                   value="{{$refer_to->price}}"/></td>
                                        <td>
                                            <div class="input-group"><input type="text" id="item-discount-{{$counter}}"
                                                                            name="item_discount[]"
                                                                            class="form-control format-quantity calculate text-right"
                                                                            value="{{$refer_to->discount}}"/><span
                                                        class="input-group-addon">%</span>
                                                        </div>
                                        </td>
                                        <td>
                                            <select id="allocation-id-{{$counter}}" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                                @foreach($list_allocation as $allocation)
                                                <option value="{{$allocation->id}}" @if($purchase_requisition_item->allocation_id == $allocation->id) selected @endif>{{$allocation->name}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" readonly id="item-total-{{$counter}}"
                                                   class="form-control format-quantity text-right" value=""/></td>
                                        </tr>
                                        <?php $counter++;?>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-right">SUB TOTAL</td>
                                        <td><input type="text" readonly id="subtotal"
                                                   class="form-control format-quantity calculate text-right" value="0"
                                                   onclick="setToNontax()"/></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">DISCOUNT</td>
                                        <td>
                                            <div class="input-group"><input style="min-width: 100px" type="text"
                                                                            id="discount" name="discount"
                                                                            class="form-control format-quantity calculate text-right"
                                                                            value="{{$purchase_order->discount}}"/><span
                                                        class="input-group-addon">%</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">TAX BASE</td>
                                        <td><input type="text" readonly id="tax_base" name="tax_base"
                                                   class="form-control format-quantity calculate text-right" value="{{$purchase_order->tax_base}}"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5"></td>
                                        <td>
                                            <input type="radio" id="tax-choice-include-tax" name="type_of_tax"
                                                   {{ old('type_of_tax') == 'on' ? 'checked'  : '' }} onchange="calculate()"
                                                   value="include"> Tax Included<br/>
                                            <input type="radio" id="tax-choice-exclude-tax" name="type_of_tax"
                                                   {{ old('type_of_tax') == 'on' ? 'checked'  : '' }} onchange="calculate()"
                                                   value="exclude"> Tax Excluded <br/>
                                            <input type="text" id="tax-choice-non-tax" name="type_of_tax" value="non">

                                            
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">TAX PERCENTAGE</td>
                                        <td>
                                            <div class="input-group">
                                                <input type="text" id="tax-percentage"
                                                    name="tax_percentage"
                                                    readonly
                                                    style="min-width: 100px"
                                                    class="form-control format-quantity calculate text-right"
                                                    value="{{$purchase_order->tax_percentage}}"/>
                                                <span class="input-group-addon">%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">TAX</td>
                                        <td>
                                            <input type="text" readonly id="tax" name="tax"
                                                class="form-control format-quantity calculate text-right" value="{{$purchase_order->tax}}"/>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <td colspan="5" class="text-right">EXPEDITION FEE</td>
                                        <td><input type="text" id="fee-expedition" name="expedition_fee"
                                                   class="form-control format-price calculate text-right"
                                                   value="{{$purchase_order->expedition_fee}}"/></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">TOTAL</td>
                                        <td><input type="text" readonly id="total"
                                                   class="form-control format-quantity calculate text-right" value="{{$purchase_order->total}}"/>
                                        </td>
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
                                {{auth()->user()->name}}
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                            <label class="col-md-3 control-label">Request Approval To *</label>
                            <div class="col-md-6">
                                <select name="approval_to" class="selectize" style="width: 100%;"
                                        data-placeholder="Choose one..">
                                    @foreach($list_user_approval as $user_approval)
                                        @if($user_approval->may('approval.point.purchasing.order'))
                                            <option value="{{$user_approval->id}}"
                                                    @if($purchase_order->formulir->approval_to == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
                                        @endif

                                    @endforeach
                                </select>
                            </div>
                        </div>
                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <button type="submit" class="btn btn-effect-ripple btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('framework::app.master.contact.__create', ['person_type' => 'supplier'])

@stop

@section('scripts')
    <script>
        var item_table = initDatatable('#item-datatable');

        $('.calculate').keyup(function () {
            calculate();
        });

        $("input[type='text']").on("click", function () {
            $(this).select();
        });


        $(function () {
            $('#tax-choice-non-tax').hide();
            var tax_status = {!! json_encode('$purchase_order->type_of_tax') !!};
            \Log::info(tax_status);
            if (tax_status == 'include') {
                $("#tax-choice-include-tax").trigger("click");
                $("#tax-choice-non-tax").val("include");
            } else if (tax_status == 'exclude') {
                $("#tax-choice-exclude-tax").trigger("click");
                $("#tax-choice-non-tax").val("exclude");
            } else {
                $("#tax-choice-non-tax").val("non");
            }

            calculate();
            if (!document.getElementById("include-expedition").checked) {
                $('#fee-expedition').hide();
            }
        });

        function includeExpedition() {
            if (document.getElementById("include-expedition").checked) {
                $('#fee-expedition').show();
            } else {
                $('#fee-expedition').val(0);
                $('#fee-expedition').hide();
            }
            calculate();
        }

        function setToNontax() {
            $("#tax-choice-include-tax").attr("checked", false);
            $("#tax-choice-exclude-tax").attr("checked", false);
            $("#tax-choice-non-tax").val("non");
            calculate();
        }

        function calculate() {
            var rows_length = $("#item-datatable").dataTable().fnGetNodes().length;
            var subtotal = 0;
            for (var i = 1; i <= rows_length; i++) {
                if (dbNum($('#item-discount-' + i).val()) > 100) {
                    dbNum($('#item-discount-' + i).val(100))
                }
                var total_per_row = dbNum($('#item-quantity-' + i).val()) * dbNum($('#item-price-' + i).val()) - ( dbNum($('#item-discount-' + i).val()) * dbNum($('#item-quantity-' + i).val()) );
                subtotal += total_per_row;
                $('#item-total-' + i).val(appNum(total_per_row));
            }

            $('#subtotal').val(appNum(subtotal));

            if (dbNum($('#discount').val()) > 100) {
                dbNum($('#discount').val(100))
            }

            var discount = dbNum($('#discount').val());
            if($('#tax-choice-include-tax').prop('checked')) {
                $('#discount').val(0);
                $('#discount').prop('readonly', true);
                var discount = 0;
            } else {
                $('#discount').prop('readonly', false);
            }
            if (dbNum($('#tax-percentage').val()) > 100) {
                dbNum($('#tax-percentage').val(100))
            }
            var tax_base = subtotal - (subtotal / 100 * discount);
            $('#tax_base').val(appNum(tax_base));

            var tax = 0;
            if ($('#tax-choice-exclude-tax').prop('checked')) {
                tax = tax_base * dbNum($('#tax-percentage').val()) / 100;
                $("#tax-choice-non-tax").val("exclude");
                $('#tax-percentage').prop('readonly', false);
            }

            if ($('#tax-choice-include-tax').prop('checked')) {
                tax_base = tax_base * 100 / (100 + dbNum($('#tax-percentage').val()));
                tax = tax_base * dbNum($('#tax-percentage').val()) / 100;
                $("#tax-choice-non-tax").val("include");
                $('#tax-percentage').prop('readonly', false);
            }

            $('#tax').val(appNum(tax));
            var expedition_fee = dbNum($('#fee-expedition').val());
            $('#total').val(appNum(tax_base + tax + expedition_fee));
        }
    </script>
@stop
