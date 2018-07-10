@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/invoice') }}">Invoice</a></li>
            <li>Create</li>
        </ul>
        <h2 class="sub-header">Invoice</h2>
        @include('point-purchasing::app.purchasing.point.inventory.invoice._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('purchasing/point/invoice/basic/store')}}" method="post"
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
                            <div class="@if(access_is_allowed_to_view('create.supplier')) input-group @endif">
                                <?php $supplier = Point\Framework\Models\Master\Person::find(old('supplier_id')); ?>                        
                                <select id="contact_id" name="supplier_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                    <option value="{{ old('supplier_id') }}">{{ $supplier ? $supplier->codeName : ''}}</option>
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
                                        <th style="width:2%;"></th>
                                        <th style="width:21%;">ITEM</th>
                                        <th style="width:12%;">QUANTITY</th>
                                        <th style="width:12%;">PRICE</th>
                                        <th style="width:12%;">DISCOUNT</th>
                                        <th style="width:12%;">ALLOCATION</th>
                                        <th style="width:12%;">TOTAL</th>
                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                    <?php $counter = 0;?>
                                    @if(count(old('item_id')) > 0)
                                        @for($counter; $counter < count(old('item_id')); $counter++)
                                            <tr>
                                                <td><a href="javascript:void(0)" class="remove-row btn btn-danger pull-right"><i class="fa fa-trash"></i></a></td>
                                                <td>
                                                    <?php $item = Point\Framework\Models\Master\Item::find(old('item_id')[$counter]);?>
                                                    <select id="item-id-{{$counter}}" name="item_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem(this.value, {{$counter}})">
                                                        <option value="{{(old('item_id')[$counter])}}">
                                                            {{ $item ? $item->codeName : ''}}
                                                        </option>
                                                    </select>
                                                </td>
                                                <td class="text-right">
                                                    <div class="input-group">
                                                        <input id="item-quantity-{{$counter}}" type="text"
                                                               name="item_quantity[]"
                                                               class="form-control format-quantity text-right calculate"
                                                               value="{{ number_format_db(old('item_quantity')[$counter]) }}"/>
                                                        <span id="span-unit-{{$counter}}" class="input-group-addon">{{old('item_unit')[$counter]}}</span>
                                                        <input type="hidden" name="item_unit[]" value="{{old('item_unit')[$counter]}}" id="item-unit-{{$counter}}">
                                                    </div>
                                                </td>
                                                <td><input type="text" id="item-price-{{$counter}}" name="item_price[]"
                                                           class="form-control format-quantity calculate text-right"
                                                           value="{{ number_format_db(old('item_price')[$counter]) }}"/></td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" id="item-discount-{{$counter}}" maxlength="3"
                                                               name="item_discount[]"
                                                               class="form-control calculate text-right"
                                                               value="{{ old('item_discount')[$counter] ? : 0}}"/><span
                                                                id="span-ordinary" class="input-group-addon">%</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <select id="allocation-id-{{$counter}}" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                                        @foreach($list_allocation as $allocation)
                                                        <option value="{{$allocation->id}}" @if(old('allocation_id')[$counter] == $allocation->id) selected @endif>{{$allocation->name}}</option>
                                                        @endforeach
                                                    </select>    
                                                </td>
                                                <td><input type="text" readonly id="item-total-{{$counter}}" name="total_per_row[]" 
                                                           class="form-control format-quantity text-right" value="{{number_format_db(old('total_per_row')[$counter] ? : 0)}}"/>
                                                </td>
                                            </tr>
                                        @endfor
                                    @endif
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td><input type="button" id="addItemRow" class="btn btn-primary"
                                                   value="add Item"></td>
                                        <td colspan="6"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">SUB TOTAL</td>
                                        <td><input type="text" readonly id="subtotal" name="subtotal" 
                                                   class="form-control format-quantity calculate text-right" value="{{old('subtotal') ? : 0}}"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">DISCOUNT</td>
                                        <td>
                                            <div class="input-group"><input type="text" id="discount" name="discount"
                                                                            maxlength="3"
                                                                            class="form-control calculate text-right"
                                                                            style="min-width: 100px" value="{{old('discount') ? : 0}}"/><span
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
                                        <td><input type="text" readonly="" id="tax" name="tax"
                                                   class="form-control format-quantity calculate text-right" value="{{old('tax') ? : 0}}"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6"></td>
                                        <td>
                                            <label>
                                                <input type="checkbox" id="tax-choice-include-tax" name="type_of_tax"
                                                   {{ old('type_of_tax') == 'include' ? 'checked'  : '' }}
                                                   onchange="$('#tax-choice-exclude-tax').prop('checked', false); calculateTotal();"
                                                   value="include" /> Tax Included
                                            </label>
                                            <br/>
                                            <label>
                                                <input type="checkbox" id="tax-choice-exclude-tax" name="type_of_tax"
                                                   {{ old('type_of_tax') == 'exclude' ? 'checked'  : '' }}
                                                   onchange="$('#tax-choice-include-tax').prop('checked', false); calculateTotal();"
                                                   value="exclude" /> Tax Excluded
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">EXPEDITION FEE</td>
                                        <td><input type="text" id="expedition-fee" name="expedition_fee"
                                                   class="form-control format-price calculate text-right"
                                                   value="{{old('expedition_fee') ? : 0}}"/></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">TOTAL</td>
                                        <td><input type="text" readonly id="total" name="total"
                                                   class="form-control format-quantity calculate text-right"
                                                   value="{{old('total') ? : 0}}"/></td>
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
@include('framework::app.master.contact.__create', ['person_type' => 'supplier'])
@include('framework::scripts.item')
@include('framework::scripts.person')
@stop

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
                + '<option value="1">Without Allocation</option>'
                @foreach($list_allocation as $allocation)
                + '<option value="{{$allocation->id}}">{{$allocation->name}}</option>'
                @endforeach
                + '</select>',
                '<input type="text" readonly id="item-total-' + counter + '" class="form-control format-quantity text-right" value="0" name="total_per_row[]" />'
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
            calculate();
        });

        function calculate() {
            var subtotal = 0;
            for (var i = 0; i < counter; i++) {
                if ($('#item-discount-' + i).length !=0) {
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
            }

            if ($('#tax-choice-include-tax').prop('checked')) {
                tax_base = tax_base * 100 / 110;
                tax = tax_base * 10 / 100;
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

        // reload data item with ajax
        if (counter > 0) {
            initSelectize('#contact_id');
            reloadPerson('#contact_id', 'supplier', false);
            for(var i=0; i< counter; i++) {
                if($('#item-id-'+i).length != 0){
                    initSelectize('#item-id-' + i);
                    initSelectize('#allocation-id-' + i);
                    reloadItem('#item-id-' + i, false);
                }
            }    
        } else {
            reloadPerson('#contact_id', 'supplier');
        }
    </script>
@stop
