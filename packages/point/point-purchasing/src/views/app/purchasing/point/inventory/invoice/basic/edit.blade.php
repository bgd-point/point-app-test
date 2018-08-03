@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/invoice') }}">Invoice</a></li>
            <li>Edit</li>
        </ul>
        <h2 class="sub-header">Invoice</h2>
        @include('point-purchasing::app.purchasing.point.inventory.invoice._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('purchasing/point/invoice/basic/'.$invoice->id)}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="action" value="edit" >
                    <input type="hidden" name="supplier_checking" value="required">


                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Reason edit *</label>

                        <div class="col-md-6">
                            <input type="text" name="edit_notes" class="form-control"
                                   value="{{$invoice->formulir->approval_message}}" autofocus>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Date</label>

                        <div class="col-md-3">
                            <input type="text" name="required_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                   value="{{ date(date_format_get(), strtotime($invoice->formulir->form_date)) }}">
                            
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
                        <label class="col-md-3 control-label">Due Date</label>

                        <div class="col-md-3">
                            <input type="text" name="due_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                   value="{{ date(date_format_get(), strtotime($invoice->due_date)) }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Supplier</label>

                        <div class="col-md-6 content-show">
                            <input type="hidden" name="supplier_id" value="{{$supplier->id}}">
                            {{$supplier->codeName}}
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
                                <legend><i class="fa fa-angle-right"></i> Item</legend>
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <input type="hidden" name="reference_type[]" value="">
                                <input type="hidden" name="reference_id[]" value="">
                                <table id="item-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th style="width:2%;"></th>
                                        <th style="width:21%;">ITEM</th>
                                        <th style="width:12%;">QUANTITY</th>
                                        <th style="width:12%;">PRICE</th>
                                        <th style="width:12%;">DISCOUNT</th>
                                        <th style="width:12%;">ALLOCATION</th>
                                        <th style="width:12%;">TOTAL</th>
                                    </tr>
                                    </thead>
                                    <?php $counter = 0; ?>
                                    <tbody class="manipulate-row">
                                    @foreach($invoice->items as $invoice_items)
                                        <tr>
                                            <td>
                                                <a href="javascript:void(0)"
                                                   class="remove-row btn btn-danger pull-right"><i
                                                            class="fa fa-trash"></i></a>
                                            </td>
                                            <td>
                                                <?php $item = Point\Framework\Models\Master\Item::find($invoice_items->item_id);?>
                                                <select id="item-id-{{$counter}}" name="item_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem(this.value, {{$counter}})">
                                                    <option value="{{$item->id}}">
                                                        {{ $item ? $item->codeName : ''}}
                                                    </option>
                                                </select>
                                            </td>
                                            <td class="text-right">
                                                <div class="input-group">
                                                    <input id="item-quantity-{{$counter}}" type="text"
                                                           name="item_quantity[]"
                                                           class="form-control format-quantity text-right calculate"
                                                           value="{{ number_format_db($invoice_items->quantity) }}"/>
                                                    <span id="span-unit-{{$counter}}"
                                                          class="input-group-addon">{{ $invoice_items->unit }}</span>
                                                    <input type="hidden" name="item_unit[]" value="{{ $invoice_items->unit }}">
                                                </div>
                                            </td>
                                            <td><input type="text" id="item-price-{{$counter}}" name="item_price[]"
                                                       class="form-control format-quantity calculate text-right"
                                                       value="{{ $invoice_items->price }}"/></td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="text" id="item-discount-{{$counter}}"
                                                           name="item_discount[]"
                                                           class="form-control format-quantity calculate text-right"
                                                           value="{{ $invoice_items->discount }}"/><span
                                                            id="span-ordinary" class="input-group-addon">%</span></div>
                                            </td>
                                            <td>
                                                <select id="allocation-id-{{$counter}}" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                                    @foreach($list_allocation as $allocation)
                                                    <option value="{{$allocation->id}}" @if($invoice_items->allocation_id == $allocation->id) selected @endif>{{$allocation->name}}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="text" readonly id="item-total-{{$counter}}"
                                                       class="form-control format-quantity text-right"
                                                       value="{{ $invoice_items->quantity * $invoice_items->price - $invoice_items->quantity * $invoice_items->price * $invoice_items->discount / 100 }}"/>
                                            </td>
                                        </tr>
                                        <?php $counter++;?>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td><input type="button" id="addItemRow" class="btn btn-primary"
                                                   value="add Item"></td>
                                        <td colspan="6"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">SUB TOTAL</td>
                                        <td><input type="text" readonly id="subtotal" onclick="setToNontax()"
                                                   class="form-control format-quantity calculate text-right" value="0"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">DISCOUNT</td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-addon">Rp</span>
                                                <input type="text"
                                                       id="discount-rp"
                                                       class="form-control text-right"
                                                       style="min-width: 100px"
                                                       onkeyup="discountNominalChanged()" 
                                                       value="0"/>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <input type="text"
                                                       id="discount"
                                                       name="discount"
                                                       class="form-control calculate text-right"
                                                       style="min-width: 100px"
                                                       value="{{$invoice->discount}}"/>
                                                <span class="input-group-addon">%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">TAX BASE</td>
                                        <td><input type="text" readonly id="tax_base" name="tax_base"
                                                   class="form-control format-quantity calculate text-right" value="{{$invoice_items->tax_base}}"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">TAX</td>
                                        <td><input type="text" readonly="" id="tax" name="tax"
                                                   class="form-control format-quantity calculate text-right" value="{{$invoice_items->tax}}"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6"></td>
                                        <td>
                                            <input type="radio" id="tax-choice-include-tax" name="type_of_tax"
                                                   {{ $invoice_items->type_of_tax == 'include' ? 'checked'  : '' }} onchange="calculate()"
                                                   value="include"> Include Tax <br/>
                                            <input type="radio" id="tax-choice-exclude-tax" name="type_of_tax"
                                                   {{ $invoice_items->type_of_tax == 'exclude' ? 'checked'  : '' }} onchange="calculate()"
                                                   value="exclude"> Exclude Tax <br/>
                                            <input type="hidden" id="tax-choice-non-tax" name="type_of_tax" {{ $invoice_items->type_of_tax == 'non' ? 'checked'  : '' }} value="non">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">EXPEDITION FEE</td>
                                        <td><input type="text" id="expedition-fee" name="expedition_fee"
                                                   class="form-control format-price calculate text-right"
                                                   value="{{$invoice->expedition_fee}}"/></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">TOTAL</td>
                                        <td><input type="text" readonly id="total" name="total"
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
                                <legend><i class="fa fa-angle-right"></i> PERSON IN CHARGE</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">FORM CREATOR</label>
                            <input type="hidden" name="approval_to" value="1">
                            <div class="col-md-6 content-show">
                                {{\Auth::user()->name}}
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
@stop
@include('framework::scripts.item')

@section('scripts')
    <script>
        var item_table = initDatatable('#item-datatable');
        var counter = {{$counter}} ? {{$counter}} : 0;

        $('#addItemRow').on('click', function () {
            item_table.row.add([
                '<a href="javascript:void(0)" class="remove-row btn btn-danger pull-right"><i class="fa fa-trash"></i></a>',
                '<select id="item-id-' + counter + '" name="item_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem(this.value, ' + counter + ')">'
                + '</select>',
                '<div class="input-group"><input type="text" id="item-quantity-' + counter + '" name="item_quantity[]" class="form-control format-quantity calculate text-right" value="0" /><span id="span-unit-' + counter + '" class="input-group-addon"></span><input type="hidden" name="item_unit[]" id="item-unit-'+counter+'"></div>',
                '<input type="text" id="item-price-' + counter + '" name="item_price[]" class="form-control format-quantity calculate text-right" value="0" />',
                '<div class="input-group"><input type="text" id="item-discount-' + counter + '" name="item_discount[]"  class="form-control calculate text-right" value="0" /><span id="span-ordinary" class="input-group-addon">%</span></div>',
                '<select id="allocation-id-' + counter + '" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                @foreach($list_allocation as $allocation)
                + '<option value="{{$allocation->id}}">{{$allocation->name}}</option>'
                @endforeach
                + '</select>',
                '<input type="text" readonly id="item-total-' + counter + '" class="form-control format-quantity text-right" value="0" />'
            ]).draw(false);
            
            initSelectize('#item-id-' + counter);
            initSelectize('#allocation-id-' + counter);
            reloadItem('#item-id-' + counter);
            initFormatNumber();

            $("input[type='text']").on("click", function () {
                $(this).select();
            });

            $('.calculate').keyup(function () {
                calculate();
            });

            selectItem($('#item-id-' + counter).val(), counter);
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
            var tax_status = {!! json_encode($invoice->type_of_tax) !!};
            if (tax_status == 'include') {
                $("#tax-choice-include-tax").trigger("click");
            } else if (tax_status == 'exclude') {
                $("#tax-choice-exclude-tax").trigger("click");
            } else {
                $("#tax-choice-non-tax").val("non");
            }

            calculate();
        });

        function setToNontax() {
            $("#tax-choice-include-tax").attr("checked", false);
            $("#tax-choice-exclude-tax").attr("checked", false);
            $("#tax-choice-non-tax").val("non");
            calculate();
        }


        function calculate() {
            var rows_length = $("#item-datatable").dataTable().fnGetNodes().length;
            var subtotal = 0;
            for (var i = 0; i < rows_length; i++) {
                if ($('#item-discount-' + i).length != 0) {
                    if (dbNum($('#item-discount-' + i).val()) > 100) {
                        dbNum($('#item-discount-' + i).val(100))
                    }
                    var total_per_row = dbNum($('#item-quantity-' + i).val()) * dbNum($('#item-price-' + i).val())
                            - ( dbNum($('#item-quantity-' + i).val()) * dbNum($('#item-price-' + i).val()) / 100 * dbNum($('#item-discount-' + i).val()) );

                    subtotal += total_per_row;
                    $('#item-total-' + i).val(appNum(total_per_row));    
                }
            }

            $('#subtotal').val(appNum(subtotal));
            calculateTotal();
        }

        $('#discount-rp').autoNumeric('init', {
            vMin: '0', vMax: '999999999999.99', aPad: false, aSep: ',', aDec: '.'
        });
        function discountNominalChanged() {
            var subtotal = dbNum($('#subtotal').val());

            if(dbNum($("#discount-rp").val()) > subtotal)
            {
                $("#discount-rp").val(number_format(subtotal, 0, ".", ","));
                $('#discount').val(100);
            }
            else
            {
                var discount = dbNum($("#discount-rp").val()) / subtotal * 100
                $('#discount').val(Number.parseFloat(discount.toFixed(15)));
                // https://stackoverflow.com/questions/5037839/avoiding-problems-with-javascripts-weird-decimal-calculations
            }
            calculateTax();
        }

        function calculateTotal() {
            var subtotal = dbNum($('#subtotal').val());
            var discount = dbNum($('#discount').val());

            if (discount > 100) {
                $('#discount').val(100);
                $("#discount-rp").val(number_format(subtotal, 0, ".", ","));
            }
            else {
                $("#discount-rp").val(number_format(subtotal * discount / 100, 0, ".", ","));
            }

            if ($('#tax-choice-include-tax').prop('checked')) {
                $('#discount, #discount-rp').val(0).prop('readonly', true);
            } else {
                $('#discount, #discount-rp').prop('readonly', false);
            }
            calculateTax();
        }

        function calculateTax(){
            var discount = dbNum($('#discount').val());
            var subtotal = dbNum($('#subtotal').val());
            var tax_base = subtotal - (subtotal / 100 * discount);
            $('#tax_base').val(appNum(tax_base));
            var tax = 0;

            if ($('#tax-choice-exclude-tax').prop('checked')) {
                tax = tax_base * 10 / 100;
                $("#tax-choice-non-tax").val("exclude");
            }

            if ($('#tax-choice-include-tax').prop('checked')) {
                tax_base = tax_base * 100 / 110;
                tax = tax_base * 10 / 100;
                $("#tax-choice-non-tax").val("include");
            }

            $('#tax_base').val(appNum(tax_base));
            $('#tax').val(appNum(tax));
            var expedition_fee = dbNum($('#expedition-fee').val());
            $('#total').val(appNum(tax_base + tax + expedition_fee));
        }
        
        function selectItem(item_id, counter) {
            getItemUnit(item_id, "#span-unit-"+counter, "html");
            getItemUnit(item_id, "#item-unit-"+counter, "input");
        }

        if (counter > 0) {
            for (i=0; i<counter; i++) {
                if ($('#item-id-' + i).length != 0) {
                    reloadItem('#item-id-' + i, false);
                }
            }
        }
    </script>
@stop
