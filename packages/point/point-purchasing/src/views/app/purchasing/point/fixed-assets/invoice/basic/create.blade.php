@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.fixed-assets._breadcrumb')
            <li><a href="{{ url('purchasing/point/fixed-assets/invoice') }}">Invoice</a></li>
            <li>Create</li>
        </ul>
        <h2 class="sub-header">Invoice</h2>
        @include('point-purchasing::app.purchasing.point.fixed-assets.invoice._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('purchasing/point/fixed-assets/invoice/basic/store')}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input type="hidden" name="reference_type">
                    <input type="hidden" name="supplier_checking" value="required">
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
                                   value="{{ date(date_format_get(), strtotime(\Carbon::now())) }}">
                            <input type="hidden" name="required_date" class="form-control date input-datepicker"
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
                        <label class="col-md-3 control-label">Due Date</label>

                        <div class="col-md-3">
                            <input type="text" name="due_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                   value="{{ date(date_format_get(), strtotime(\Carbon::now())) }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Supplier *</label>
                        <div class="col-md-6">
                            <?php $supplier = Point\Framework\Models\Master\Person::find(old('supplier_id')); ?>                        
                            <select id="supplier-id" name="supplier_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                <option value="{{ old('supplier_id') }}">{{ $supplier ? $supplier->codeName : ''}}</option>
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
                                        <th style="min-width: 100px;"></th>
                                        <th style="min-width: 220px">Asset Account</th>
                                        <th style="min-width: 120px" class="text-right">Asset Name</th>
                                        <th style="min-width: 120px" class="text-right">Quantity</th>
                                        <th style="min-width: 120px" class="text-right">Unit</th>
                                        <th style="min-width: 120px" class="text-right">Price</th>
                                        <th style="min-width: 120px" class="text-right">Discount</th>
                                        <th style="min-width: 220px" class="text-right">Allocation</th>
                                        <th style="min-width: 220px" class="text-right">Total</th>
                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                    <?php $counter = 0;?>
                                    @if(count(old('coa_id')) > 0)
                                        @for($counter; $counter < count(old('coa_id')); $counter++)
                                            <tr>
                                                <td><a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a></td>
                                                <td>
                                                    <select class="selectize" name="coa_id[]" data-placeholder="Choose one..">
                                                        @foreach($list_account as $account)
                                                        <option value="{{$account->id}}" @if(old('coa_id')[$counter] == $account->id) selected @endif>{{$account->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="text-right">
                                                    <input type="text" name="name[]" value="{{old('name')[$counter]}}" class="form-control">
                                                </td>
                                                <td>
                                                    <input style="min-width: 120px" 
                                                    id="item-quantity-{{$counter}}" 
                                                    type="text" name="item_quantity[]"
                                                    class="form-control format-quantity text-right calculate"
                                                    value="{{old('item_quantity')[$counter] }}"/>
                                                </td>
                                                <td>
                                                    <input type="text" name="item_unit[]" value="{{old('item_unit')[$counter]}}" class="form-control">
                                                </td>
                                                <td><input type="text" id="item-price-{{$counter}}" name="item_price[]"
                                                           class="form-control format-quantity calculate text-right"
                                                           value="{{ old('item_price')[$counter] }}"/></td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" id="item-discount-{{$counter}}" maxlength="3"
                                                               name="item_discount[]"
                                                               class="form-control calculate text-right"
                                                               value="{{ old('item_discount')[$counter] ? : 0}}"/>
                                                        <span id="span-ordinary" class="input-group-addon">%</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <select id="allocation-id-{{$counter}}" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                                        @foreach($list_allocation as $allocation)
                                                        <option value="{{$allocation->id}}" @if(old('allocation_id')[$counter] == $allocation->id) selected @endif>{{$allocation->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" readonly id="item-total-{{$counter}}" name="total_per_row[]" class="form-control format-quantity text-right" value="{{old('total_per_row')[$counter] ? : 0}}"/>
                                                </td>
                                            </tr>
                                        @endfor
                                    @endif
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td><input type="button" id="addItemRow" class="btn btn-primary"
                                                   value="add Item"></td>
                                        <td colspan="8"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="8" class="text-right">SUB TOTAL</td>
                                        <td><input type="text" readonly id="subtotal" onclick="setToNontax()" name="subtotal" 
                                                   class="form-control format-quantity calculate text-right" value="{{old('subtotal') ? : 0}}"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="8" class="text-right">DISCOUNT</td>
                                        <td>
                                            <div class="input-group"><input type="text" id="discount" name="discount"
                                                                            maxlength="3"
                                                                            class="form-control calculate text-right"
                                                                            style="min-width: 100px" value="{{old('discount') ? : 0}}"/><span
                                                        class="input-group-addon">%</span></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="8" class="text-right">TAX BASE</td>
                                        <td><input type="text" readonly id="tax_base" name="tax_base"
                                                   class="form-control format-quantity calculate text-right" value="{{old('tax_base') ? : 0}}"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="8" class="text-right">TAX</td>
                                        <td><input type="text" readonly="" id="tax" name="tax"
                                                   class="form-control format-quantity calculate text-right" value="{{old('tax') ? : 0}}"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="8"></td>
                                        <td>
                                            <input type="radio" id="tax-choice-include-tax" name="type_of_tax"
                                                   {{ old('type_of_tax') == 'include' ? 'checked'  : '' }} onchange="calculateTotal()"
                                                   value="include"> Tax Included<br/>
                                            <input type="radio" id="tax-choice-exclude-tax" name="type_of_tax"
                                                   {{ old('type_of_tax') == 'exclude' ? 'checked'  : '' }} onchange="calculateTotal()"
                                                   value="exclude"> Tax Excluded
                                            <input type="hidden" id="tax-choice-non-tax" {{ old('type_of_tax') == 'non' ? 'checked'  : '' }}
                                                   name="type_of_tax" value="non">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="8" class="text-right">EXPEDITION FEE</td>
                                        <td><input type="text" id="expedition-fee" name="expedition_fee"
                                                   class="form-control format-price calculate text-right"
                                                   value="{{old('expedition_fee') ? : 0}}"/></td>
                                    </tr>
                                    <tr>
                                        <td colspan="8" class="text-right">TOTAL</td>
                                        <td><input type="text" readonly id="total" name="purchasing_total"
                                                   class="form-control format-quantity calculate text-right"
                                                   value="{{old('purchasing_total') ? : 0}}"/></td>
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
@include('framework::scripts.item')
@include('framework::scripts.person')
@stop

@section('scripts')
    <script>
        var item_table = initDatatable('#item-datatable');
        var counter = {{$counter}} ? {{$counter}} : 0;

        $('#addItemRow').on('click', function () {
            item_table.row.add([
                '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
                '<select id="coa-id-' + counter + '" name="coa_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                @foreach($list_account as $account)
                + '<option value="{{$account->id}}">{{$account->name}}</option>'
                @endforeach
                + '</select>',
                '<input type="text" class="form-control" name="name[]">',
                '<input type="text" id="item-quantity-' + counter + '" name="item_quantity[]" class="form-control format-quantity text-right calculate" value="0" />',
                '<input type="text" id="item-unit-' + counter + '" name="item_unit[]" class="form-control" value="unit" />',
                '<input type="text" id="item-price-' + counter + '" name="item_price[]" class="form-control format-quantity calculate text-right" value="0" />',
                '<div class="input-group"><input type="text" id="item-discount-' + counter + '" name="discount[]" class="form-control format-quantity text-right calculate" value="0" /><span id="span-ordinary" class="input-group-addon">%</span>',
                '<select id="allocation-id-' + counter + '" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                @foreach($list_allocation as $allocation)
                + '<option value="{{$allocation->id}}">{{$allocation->name}}</option>'
                @endforeach
                + '</select>',
                '<input type="text" readonly id="item-total-' + counter + '" name="total_per_row[]" class="form-control format-quantity text-right" value="0" />'
            ]).draw(false);
            
            initSelectize('#coa-id-' + counter);
            initSelectize('#allocation-id-' + counter);
            initFormatNumber();

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
            $('#tax-choice-non-tax').hide();
            calculate();
        });

        function setToNontax() {
            $("#tax-choice-include-tax").attr("checked", false);
            $("#tax-choice-exclude-tax").attr("checked", false);
            $("#tax-choice-non-tax").val("non");
            calculate();
        }

        function calculate() {
            var subtotal = 0;
            for (var i = 0; i < counter; i++) {
                if($('#item-discount-'+i).length != 0){
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

        function calculateTotal() {
            if (dbNum($('#discount').val()) > 100) {
                dbNum($('#discount').val(100))
            }

            var subtotal = dbNum($('#subtotal').val());
            var discount = dbNum($('#discount').val());
            if ($('#tax-choice-include-tax').prop('checked')) {
                $('#discount').val(0);
                $('#discount').prop('readonly', true);
                var discount = 0;
            } else {
                $('#discount').prop('readonly', false);
            }
            
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

        // reload data item with ajax
        if (counter > 0) {
            reloadPerson('#supplier-id', 'supplier', false);
        } else {
            reloadPerson('#supplier-id', 'supplier');
        }
    </script>
@stop
