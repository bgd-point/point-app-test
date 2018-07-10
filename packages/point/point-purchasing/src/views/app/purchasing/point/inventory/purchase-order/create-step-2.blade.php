@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/purchase-order') }}">Purchase Order</a></li>
            <li>Create step 2</li>
        </ul>
        <h2 class="sub-header">Purchase Order</h2>
        @include('point-purchasing::app.purchasing.point.inventory.purchase-order._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('purchasing/point/purchase-order')}}"
                      method="post" class="form-horizontal form-bordered">
                    {!! csrf_field() !!}

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend>
                                    <i class="fa fa-angle-right"></i> REF# 
                                    <a href="{{ url('purchasing/point/purchase-requisition/'.$purchase_requisition->id) }}"
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
                                   data-date-format="{{date_format_masking()}}"
                                   value="{{date(date_format_get(), strtotime($purchase_requisition->formulir->form_date))}}">
                            <input type="hidden" name="required_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}"
                                   value="{{date(date_format_get(), strtotime($purchase_requisition->formulir->form_date))}}">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group bootstrap-timepicker">
                                <input type="text" id="time" name="time" class="form-control timepicker">
                                <span class="input-group-btn">
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary"><i class="fa fa-clock-o"></i></a>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Supplier *</label>
                        <div class="col-md-6">
                            <?php $supplier_id = $purchase_requisition->supplier_id ? $purchase_requisition->supplier_id : old('supplier_id') ?>
                            <?php $supplier = Point\Framework\Models\Master\Person::find($supplier_id); ?>                        
                            @if(auth()->user()->may('create.supplier')) 
                            <div class="input-group"> 
                            @endif
                                <select id="contact_id" name="supplier_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                    <option value="{{ $supplier ? $supplier->id : 0 }}">{{ $supplier ? $supplier->codeName : ''}}</option>
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
                            <span class="help-block">Check if this purchase need downpayment</span>
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
                                        <th style="min-width: 220px">ITEM</th>
                                        <th style="min-width: 120px" class="text-right">QUANTITY</th>
                                        <th style="min-width: 120px" class="text-right">PRICE</th>
                                        <th style="min-width: 120px" class="text-right">DISCOUNT</th>
                                        <th style="min-width: 220px" class="text-right">ALLOCATION</th>
                                        <th style="min-width: 220px" class="text-right">TOTAL</th>
                                    </tr>
                                    </thead>
                                    <?php $counter = 0; ?>
                                    <tbody class="manipulate-row">
                                    @foreach($purchase_requisition->items as $purchase_requisition_item)
                                        <tr>
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
                                                <input type="hidden" name="allocation_id[]"
                                                       value="{{$purchase_requisition_item->allocation_id}}"/>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input id="item-quantity-{{$counter}}" type="text"
                                                       name="item_quantity[]"
                                                       class="form-control format-quantity text-right calculate"
                                                       value="{{ ReferHelper::remaining(get_class($purchase_requisition_item), $purchase_requisition_item->id, $purchase_requisition_item->quantity) }}"/>
                                                    <span class="input-group-addon">{{ $purchase_requisition_item->unit }}</span>
                                                </div>

                                                <input type="hidden" name="item_unit_name[]"
                                                       value="{{$purchase_requisition_item->unit}}"/>
                                                <input type="hidden" name="item_unit_converter[]"
                                                       value="{{$purchase_requisition_item->converter}}"/>
                                            </td>
                                            <td><input type="text" id="item-price-{{$counter}}" name="item_price[]"
                                                       class="form-control format-quantity calculate text-right"
                                                       value="{{old('item_price')[$counter] ? : $purchase_requisition_item->price}}"/></td>
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
                                        <td><input type="text" readonly id="subtotal" value="0"
                                                   class="form-control format-quantity calculate text-right" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">DISCOUNT</td>
                                        <td>
                                            <div class="input-group"><input type="text" id="discount" name="discount"
                                                                            style="min-width: 100px"
                                                                            class="form-control format-quantity calculate text-right"
                                                                            value="{{old('discount') ? : 0}}"/><span
                                                        class="input-group-addon">%</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">TAX BASE</td>
                                        <td><input type="text" readonly id="tax_base"
                                                   class="form-control format-quantity calculate text-right" value="0"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">TAX</td>
                                        <td>
                                            <input type="text" readonly="" id="tax"
                                                   class="form-control format-quantity calculate text-right" value="0"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5"></td>
                                        <td>
                                            <label>
                                                <input type="checkbox" id="tax-choice-include-tax" name="type_of_tax"
                                                   {{ old('type_of_tax') == 'include' ? 'checked'  : '' }}
                                                   onchange="$('#tax-choice-exclude-tax').prop('checked', false); calculate();"
                                                   value="include" /> Tax Included
                                            </label>
                                            <br/>
                                            <label>
                                                <input type="checkbox" id="tax-choice-exclude-tax" name="type_of_tax"
                                                   {{ old('type_of_tax') == 'exclude' ? 'checked'  : '' }}
                                                   onchange="$('#tax-choice-include-tax').prop('checked', false); calculate();"
                                                   value="exclude" /> Tax Excluded
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">EXPEDITION FEE</td>
                                        <td><input type="text" id="fee-expedition" name="expedition_fee"
                                                   class="form-control calculate format-quantity text-right"
                                                   value="{{old('expedition_fee') ? : 0}}"/></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">TOTAL</td>
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

                                        @if($user_approval->may('approval.point.purchasing.order'))
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
    @include('framework::app.master.contact.__create', ['person_type' => 'supplier'])
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
            reloadPerson('#contact_id', 'supplier', false);
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
