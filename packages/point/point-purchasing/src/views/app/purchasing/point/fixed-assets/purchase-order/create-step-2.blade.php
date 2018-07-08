@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.fixed-assets._breadcrumb')
            <li><a href="{{ url('purchasing/point/fixed-assets/purchase-order') }}">Purchase Order</a></li>
            <li>Create step 2</li>
        </ul>
        <h2 class="sub-header">Purchase Order</h2>
        @include('point-purchasing::app.purchasing.point.fixed-assets.purchase-order._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('purchasing/point/fixed-assets/purchase-order')}}"
                      method="post" class="form-horizontal form-bordered">
                    {!! csrf_field() !!}

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> REF# <a
                                            href="{{ url('purchasing/point/fixed-assets/purchase-requisition/'.$purchase_requisition->id) }}"
                                            target="_blank">{{ $purchase_requisition->formulir->form_number }}</a>
                                </legend>
                                <input type="hidden" name="reference_id" value="{{ $purchase_requisition->id }}"/>
                                <input type="hidden" name="reference_type" value="{{ get_class($purchase_requisition) }}"/>
                                <input type="hidden" name="supplier_checking" value="required">

                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Required Date</label>

                            <div class="col-md-6 content-show">
                                {{ date_format_view($purchase_requisition->required_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Employee</label>

                            <div class="col-md-6 content-show">
                                {{ $purchase_requisition->employee->codeName }}
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
                                   data-date-format="{{date_format_masking()}}"
                                   value="{{date(date_format_get(), strtotime(\Carbon::now()))}}">
                            <input type="hidden" name="required_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}"
                                   value="{{date(date_format_get(), strtotime(\Carbon::now()))}}">
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
                        <label class="col-md-3 control-label">Supplier *</label>
                        <div class="col-md-6">
                            <?php $purchase_requisition->supplier_id ? $supplier_id = $purchase_requisition->supplier_id : old('supplier_id') ?>
                            <?php $supplier = Point\Framework\Models\Master\Person::find($supplier_id); ?>                        
                            <select id="supplier-id" name="supplier_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                <option value="{{ $supplier_id }}">{{ $supplier ? $supplier->codeName : ''}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>

                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control"
                                   value="{{ $purchase_requisition->formulir->notes }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Cash Purchase</label>

                        <div class="col-md-6 content-show">
                            <input type="checkbox" id="is-cash" name="is_cash" checked value="true">
                            <span class="help-block">Check for create Receive order / Uncheck for create Downpayment</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Include Expedition</label>

                        <div class="col-md-6 content-show">
                            <input type="checkbox" id="include-expedition" name="include_expedition"
                                   onchange="includeExpedition()" checked value="true">
                            <span class="help-block">Uncheck this if you want to order expedition service</span>
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
                                        <th style="min-width: 220px">Account</th>
                                        <th style="min-width: 120px" class="text-right">Asset Name</th>
                                        <th style="min-width: 120px" class="text-right">Quantity</th>
                                        <th style="min-width: 120px" class="text-right">Unit</th>
                                        <th style="min-width: 120px" class="text-right">Price</th>
                                        <th style="min-width: 120px" class="text-right">Discount</th>
                                        <th style="min-width: 220px" class="text-right">Allocation</th>
                                        <th style="min-width: 220px" class="text-right">Total</th>
                                    </tr>
                                    </thead>
                                    <?php $counter = 0; ?>
                                    <tbody class="manipulate-row">
                                    @foreach($purchase_requisition->details as $purchase_requisition_detail)
                                        <tr>
                                            <td>
                                                {{ $purchase_requisition_detail->coa->name }}
                                                <input type="hidden" name="reference_item_id[]"
                                                       value="{{$purchase_requisition_detail->id}}"/>
                                                <input type="hidden" name="reference_item_type[]"
                                                       value="{{get_class($purchase_requisition_detail)}}"/>
                                                <input type="hidden" name="reference_item_value[]"
                                                       value="{{$purchase_requisition_detail->quantity}}"/>
                                                <input type="hidden" name="name[]"
                                                       value="{{$purchase_requisition_detail->name}}"/>
                                                <input type="hidden" name="allocation_id[]"
                                                       value="{{$purchase_requisition_detail->allocation_id}}"/>
                                                <input type="hidden" name="coa_id[]"
                                                       value="{{$purchase_requisition_detail->coa_id}}"/>
                                            </td>
                                            <td>
                                                {{ $purchase_requisition_detail->name }}
                                            </td>
                                            <td>
                                                <input id="item-quantity-{{$counter}}" type="text"
                                                   name="quantity[]"
                                                   class="form-control format-quantity text-right calculate"
                                                   value="{{ ReferHelper::remaining(get_class($purchase_requisition_detail), $purchase_requisition_detail->id, $purchase_requisition_detail->quantity) }}"/>
                                            </td>
                                            <td>
                                                <input type="text" name="unit[]" class="form-control" value="{{$purchase_requisition_detail->unit}}">
                                            </td>
                                            <td><input type="text" id="item-price-{{$counter}}" name="price[]"
                                                       class="form-control format-quantity calculate text-right"
                                                       value="{{$purchase_requisition_detail->price ? : 0}}"/></td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="text" id="item-discount-{{$counter}}"
                                                           name="item_discount[]"
                                                           class="form-control format-quantity calculate text-right"
                                                           value="{{old('item_discount')[$counter] ? : 0 }}"/><span
                                                            class="input-group-addon">%</span></div>
                                            </td>
                                            <td>
                                                <select id="allocation-id-{{$counter}}" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                                    @foreach($list_allocation as $allocation)
                                                    <option value="{{$allocation->id}}" @if(old('allocation_id')[$counter] == $allocation->id) selected @endif>{{$allocation->name}}</option>
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
                                        <td colspan="7" class="text-right">SUB TOTAL</td>
                                        <td><input type="text" readonly id="subtotal" value="0"
                                                   class="form-control format-quantity calculate text-right" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-right">DISCOUNT</td>
                                        <td>
                                            <div class="input-group">
                                                <input type="text" id="discount" name="discount"
                                                       style="min-width: 100px"
                                                       class="form-control format-quantity calculate text-right"
                                                       value="{{old('discount') ? : 0}}"/>
                                                <span class="input-group-addon">%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-right">TAX BASE</td>
                                        <td><input type="text" readonly id="tax_base"
                                                   class="form-control format-quantity calculate text-right" value="0"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-right">TAX</td>
                                        <td>
                                            <input type="text" readonly="" id="tax"
                                                   class="form-control format-quantity calculate text-right" value="0"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7"></td>
                                        <td>
                                            <label>
                                                <input type="checkbox" id="tax-choice-include-tax" name="type_of_tax"
                                                   {{ old('type_of_tax') == 'include' ? 'checked'  : '' }}
                                                   onchange="$('#tax-choice-exclude-tax').prop('checked', false); calculate();"
                                                   value="include" /> Tax Included
                                            </label>
                                            <br />
                                            <label>
                                                <input type="checkbox" id="tax-choice-exclude-tax" name="type_of_tax"
                                                   {{ old('type_of_tax') == 'exclude' ? 'checked'  : '' }}
                                                   onchange="$('#tax-choice-include-tax').prop('checked', false); calculate();"
                                                   value="exclude" /> Tax Excluded
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-right">EXPEDITION FEE</td>
                                        <td><input type="text" id="fee-expedition" name="expedition_fee"
                                                   class="form-control calculate format-quantity text-right"
                                                   value="{{old('expedition_fee') ? : 0}}"/></td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-right">TOTAL</td>
                                        <td><input type="text" readonly id="total"
                                                   class="form-control format-quantity calculate text-right" value="0"/>
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

                                        @if($user_approval->may('approval.point.purchasing.order.fixed.assets'))
                                            <option value="{{$user_approval->id}}"
                                                    @if(old('approval_to') == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
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
@stop
@include('framework::scripts.person')

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
            reloadPerson('#supplier-id', 'supplier', false);
            calculate();
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

        function calculate() {

            var rows_length = $("#item-datatable").dataTable().fnGetNodes().length;
            var subtotal = 0;
            for (var i = 0; i < rows_length; i++) {
                if (dbNum($('#item-discount-' + i).val()) > 100) {
                    dbNum($('#item-discount-' + i).val(100))
                }
                var total_per_row = dbNum($('#item-quantity-' + i).val()) * dbNum($('#item-price-' + i).val())
                        - ( dbNum($('#item-quantity-' + i).val()) * dbNum($('#item-price-' + i).val()) / 100 * dbNum($('#item-discount-' + i).val()));
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

            var tax_base = subtotal - (subtotal / 100 * discount);
            var tax = 0;

            if ($('#tax-choice-exclude-tax').prop('checked')) {
                tax = tax_base * 10 / 100;
            }

            if ($('#tax-choice-include-tax').prop('checked')) {
                tax_base = tax_base * 100 / 110;
                tax = tax_base * 10 / 100;
            }

            $('#tax_base').val(appNum(tax_base));
            $('#tax').val(appNum(tax));
            var expedition_fee = dbNum($('#fee-expedition').val());
            $('#total').val(appNum(tax_base + tax + expedition_fee));
        }
    </script>
@stop
