@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/purchase-order') }}">Purchase Order</a></li>
            <li>Create</li>
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
                    <input name="reference_type" type="hidden" value="">
                    <input name="supplier_checking" type="hidden" value="required">
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
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date *</label>

                        <div class="col-md-3">
                            <input type="text" name="form_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                   value="{{ date(date_format_get(), strtotime($purchase_order->formulir->form_date)) }}">
                            <input type="hidden" name="required_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                   value="{{ date(date_format_get(), strtotime($purchase_order->formulir->form_date)) }}">
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
                            <div class="@if(access_is_allowed_to_view('create.supplier')) input-group @endif">
                                <?php $supplier = Point\Framework\Models\Master\Person::find($purchase_order->supplier_id); ?>                        
                                <select id="contact_id" name="supplier_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                    <option value="{{ $purchase_order->supplier_id }}">{{ $supplier ? $supplier->codeName : ''}}</option>
                                </select>
                                @if(access_is_allowed_to_view('create.supplier'))
                                <span class="input-group-btn">
                                    <a href="#modal-contact" class="btn btn-effect-ripple btn-primary" data-toggle="modal">
                                        <i class="fa fa-plus"></i>
                                    </a>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Cash Purchasing</label>
                        <div class="col-md-6 content-show">
                            <input type="checkbox" id="cash-selling" name="is_cash" @if($purchase_order->is_cash == 1) checked @endif value="1">
                            <span class="help-block">If checked, you need to make a downpayment before deliver the order</span>
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
                            <input type="text" name="notes" class="form-control" value="{{ $purchase_order->formulir->notes }}">
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
                                        <th style="width: 50px"></th>
                                        <th style="min-width: 220px">ITEM *</th>
                                        <th style="min-width: 120px">QUANTITY</th>
                                        <th style="min-width: 220px">PRICE *</th>
                                        <th style="min-width: 220px">DISCOUNT</th>
                                        <th style="min-width: 220px">ALLOCATION *</th>
                                        <th style="min-width: 220px">TOTAL</th>
                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                    <?php $counter = 0;?>
                                    @foreach( $purchase_order->items as $purchase_order_item )
                                        <tr>
                                            <td><a href="javascript:void(0)" class="remove-row btn btn-danger"><i
                                                            class="fa fa-trash"></i></a></td>
                                            <td>
                                                <select id="item-id-{{$counter}}" name="item_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem(this.value, {{$counter}})">
                                                    <option value="{{$purchase_order_item->item_id}}">
                                                        <?php echo Point\Framework\Models\Master\Item::find($purchase_order_item->item_id)->codeName;?>
                                                    </option>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input style="min-width: 120px" id="item-quantity-{{$counter}}"
                                                           type="text" name="item_quantity[]"
                                                           class="form-control text-right calculate format-quantity"
                                                           value="{{$purchase_order_item->quantity}}"/><span
                                                            id="span_unit-{{$counter}}"
                                                            class="input-group-addon">{{$purchase_order_item->unit}}</span>
                                                </div>
                                                <input type="hidden" readonly id="item-unit-{{$counter}}"
                                                       name="item_unit_name[]" class="form-control text-right"
                                                       value="{{$purchase_order_item->unit}}"/></td>
                                            <td>
                                                <input type="text" id="item-price-{{$counter}}" name="item_price[]"
                                                       class="form-control calculate text-right format-quantity"
                                                       value="{{$purchase_order_item->price}}"/>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input style="min-width: 120px" type="text"
                                                           id="item-discount-{{$counter}}" name="item_discount[]"
                                                            value="{{$purchase_order_item->discount}}" class="form-control calculate text-right"
                                                           value="0"/><span class="input-group-addon">%</span></div>
                                            </td>
                                            <td>
                                                <select id="allocation-id-{{$counter}}" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                                    @foreach($list_allocation as $allocation)
                                                    <option value="{{$allocation->id}}" @if($purchase_order_item->allocation_id == $allocation->id) selected @endif>{{$allocation->name}}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="text" id="item-total-{{$counter}}" name="total_per_row" readonly
                                                       class="form-control format-quantity text-right" value="{{ $purchase_order_item->quantity * $purchase_order_item->price - $purchase_order_item->quantity * $purchase_order_item->price * $purchase_order_item->discount / 100 }}"/>
                                            </td>
                                        </tr>
                                    <?php $counter++; ?>
                                    @endforeach
                                    
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td><input type="button" id="addItemRow" class="btn btn-primary"
                                                   value="Add Item"></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">SUB TOTAL</td>
                                        <td><input type="text" readonly id="subtotal"
                                                   class="form-control format-quantity calculate text-right"
                                                   onclick="setToNontax()" value="0" name="subtotal" /></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">DISCOUNT</td>
                                        <td>
                                            <div class="input-group">
                                                <input type="text" id="discount" name="discount" maxlength="3"
                                                       class="form-control calculate text-right"
                                                       style="min-width: 100px" value="{{$purchase_order->discount }}"/><span
                                                        class="input-group-addon">%</span></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">TAX BASE</td>
                                        <td><input type="text" readonly id="tax_base" name="tax_base" 
                                                   class="form-control format-quantity calculate text-right" value="{{old('tax_base') ? : 0}}"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">TAX</td>
                                        <td>
                                            <input type="text" readonly="" id="tax" name="tax" 
                                                   class="form-control format-quantity calculate text-right" value="{{old('tax') ? : 0}}"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6"></td>
                                        <td>
                                            <input type="radio" id="tax-choice-include-tax" name="type_of_tax"
                                                   {{ $purchase_order->type_of_tax == 'include' ? 'checked' : '' }} onchange="calculate()"
                                                   value="include"> Tax Included <br/>
                                            <input type="radio" id="tax-choice-exclude-tax" name="type_of_tax"
                                                   {{ $purchase_order->type_of_tax == 'exclude' ? 'checked' : '' }} onchange="calculate()"
                                                   value="exclude"> Tax Excluded <br/>
                                            <input type="text" id="tax-choice-non-tax" name="type_of_tax"
                                                    {{ $purchase_order->type_of_tax == 'non' ? 'checked' : '' }} value="non">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">EXPEDITION FEE</td>
                                        <td><input type="text" id="fee-expedition" name="expedition_fee"
                                                   class="form-control format-price calculate text-right"
                                                   value="{{$purchase_order->expedition_fee}}"/></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">TOTAL</td>
                                        <td><input type="text" readonly id="total" name="total" 
                                                   class="form-control format-quantity calculate text-right" value="{{old('total') ? : 0}}"/>
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

                    </fieldset>

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
@include('framework::scripts.item')
@include('framework::scripts.person')
@section('scripts')
    <script>
        var item_table = initDatatable('#item-datatable');
        var counter = {{$counter}} > 0 ? {{$counter}} : 0;

        $('#addItemRow').on('click', function () {
            item_table.row.add([
                '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
                '<select id="item-id-' + counter + '" name="item_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem(this.value, ' + counter + ')">'
                + '<option ></option>'
             + '</select>',
                '<div class="input-group"><input type="text" id="item-quantity-' + counter + '" name="item_quantity[]" class="form-control format-quantity text-right calculate" value="0" /><span id="span_unit-' + counter + '" class="input-group-addon"></span></div><input type="hidden"  id="item-unit-' + counter + '" name="item_unit_name[]" class="form-control format-quantity text-right" value="" />',
                '<td><input type="text" id="item-price-' + counter + '" name="item_price[]" class="form-control format-quantity calculate text-right" value="0" />',
                '<div class="input-group">'
                + '<input type="text" id="item-discount-' + counter + '" name="item_discount[]"  class="form-control calculate text-right" value="0"  /><span class="input-group-addon">%</span></div></td>',
                '<select id="allocation-id-' + counter + '" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                + '<option value="1">Without Allocation</option>'
                @foreach($list_allocation as $allocation)
                + '<option value="{{$allocation->id}}">{{$allocation->name}}</option>'
                @endforeach
                + '</select>',
                '<input type="text" readonly id="item-total-' + counter + '" class="form-control format-quantity text-right" value="" />'

            ]).draw(false);

            initSelectize('#item-id-' + counter);
            initSelectize('#allocation-id-' + counter);
            reloadItem('#item-id-' + counter);
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

        $('.calculate').keyup(function () {
            calculate();
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

        $(function () {
            $('#tax-choice-non-tax').hide();
            var tax_status = {!! json_encode(old('type_of_tax')) !!};

            if (tax_status == 'include') {
                $("#tax-choice-include-tax").trigger("click");
            } else if (tax_status == 'exclude') {
                $("#tax-choice-exclude-tax").trigger("click");
            } else {
                $("#tax-choice-non-tax").val("non");
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
            $("#tax-choice-non-tax").trigger("click");
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
                        - ( dbNum($('#item-quantity-' + i).val()) * dbNum($('#item-price-' + i).val()) / 100 * dbNum($('#item-discount-' + i).val()) );
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
                tax = tax_base * 11 / 100;
                $("#tax-choice-non-tax").val("exclude");
            }

            if ($('#tax-choice-include-tax').prop('checked')) {
                tax_base = tax_base * 100 / 111;
                tax = tax_base * 11 / 100;
                $("#tax-choice-non-tax").val("include");
            }

            var expedition_fee = dbNum($('#fee-expedition').val());
            $('#tax_base').val(appNum(tax_base));
            $('#tax').val(appNum(tax));
            $('#total').val(appNum(tax_base + tax + expedition_fee));
        }

        function selectItem(item_id, counter) {
            getItemUnit(item_id, "#span_unit-"+counter, "html");
            getItemUnit(item_id, "#item-unit-"+counter, "input");
        }

        // reload data item with ajax
        if (counter > 0) {
            calculate();
            reloadPerson('#employee-id', 'employee', false);
            reloadPerson('#contact_id', 'supplier', false);
            for(var i=0; i< counter; i++) {
                if($('#item-id-'+i).length != 0){
                    reloadItem('#item-id-' + i, false);
                }
            }    
        } else {
            reloadPerson('#contact_id', 'supplier');
            reloadPerson('#employee-id', 'employee');
        }
    </script>
@stop
