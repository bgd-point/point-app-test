@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/sales-quotation') }}">Sales Quotation</a></li>
            <li>Create</li>
        </ul>
        <h2 class="sub-header">Sales Quotation</h2>
        @include('point-sales::app.sales.point.sales.sales-quotation._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('sales/point/indirect/sales-quotation')}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Required Date *</label>

                        <div class="col-md-3">
                            <input type="text" name="form_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
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
                        <label class="col-md-3 control-label">Customer *</label>
                        <div class="col-md-6">
                            <div class="@if(access_is_allowed_to_view('create.customer')) input-group @endif">
                                <?php $customer = Point\Framework\Models\Master\Person::find(old('person_id')); ?>                        
                                <select id="contact_id" name="person_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                    <option value="{{ old('person_id') }}">{{ $customer ? $customer->codeName : ''}}</option>
                                </select>
                                @if(access_is_allowed_to_view('create.customer'))
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
                            <input type="text" name="notes" class="form-control" value="{{old('notes')}}">
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
                                        <th style="width: 2%"></th>
                                        <th style="width: 25%">ITEM *</th>
                                        <th style="width: 25%">ALLOCATION *</th>
                                        <th style="width: 12%">QUANTITY *</th>
                                        <th style="width: 12%">PRICE *</th>
                                        <th style="width: 12%">DISCOUNT</th>
                                        <th style="width: 12%">TOTAL</th>

                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                    @if(count(old('item_id')) > 0)
                                        @for($counter=0; $counter < count(old('item_id')); $counter++ )
                                            <tr>
                                                <?php $item = Point\Framework\Models\Master\Item::find(old('item_id')[$counter]);?>
                                                <td><a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a></td>
                                                <td>
                                                    <select id="item-id-{{$counter}}" name="item_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem(this.value, {{$counter}})">
                                                        <option value="{{(old('item_id')[$counter])}}">
                                                            {{ $item ? $item->codeName : ''}}
                                                        </option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select id="allocation-id-{{$counter}}" name="allocation_id[]" class="selectize"
                                                            style="width: 100%;" data-placeholder="Choose one..">
                                                        @foreach($list_allocation as $allocation)
                                                            <option @if (old('allocation_id')[$counter] == $allocation->id) selected
                                                                    @endif value="{{$allocation->id}}">{{$allocation->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input style="min-width: 120px" id="item-quantity-{{$counter}}"
                                                               type="text" name="item_quantity[]"
                                                               class="form-control format-quantity text-right calculate"
                                                               value="{{old('item_quantity')[$counter] }}"/><span
                                                                id="span-unit-{{$counter}}"
                                                                class="input-group-addon">{{old('item_unit')[$counter]}}</span></div>
                                                    <input type="hidden" readonly id="item-unit-{{$counter}}"
                                                           name="item_unit[]"
                                                           class="form-control text-right"
                                                           value="{{old('item_unit')[$counter]}}"/></td>
                                                <td>
                                                    <input type="text" id="item-price-{{$counter}}" name="item_price[]"
                                                           class="form-control format-price calculate text-right"
                                                           value="{{number_format_db(old('item_price')[$counter])}}"/>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input style="min-width: 120px" type="text"
                                                               id="item-discount-{{$counter}}" name="item_discount[]"
                                                                class="form-control calculate text-right"
                                                               value="{{old('item_discount')[$counter]}}"/><span
                                                                class="input-group-addon">%</span></div>
                                                </td>
                                                <td><input type="text" id="item-total-{{$counter}}"
                                                           class="form-control format-quantity text-right" value=""/>
                                                </td>
                                            </tr>
                                        @endfor
                                    @else
                                        <?php $counter = 0;?>
                                    @endif
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
                                                   onclick="setToNontax()" value="0"/></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">DISCOUNT</td>
                                        <td>
                                            <div class="input-group">
                                                <input type="text" id="discount" name="discount" maxlength="3"
                                                       class="form-control calculate text-right"
                                                       style="min-width: 100px"
                                                       value="{{number_format_db(old('discount') ? : 0)}}"/><span
                                                        class="input-group-addon">%</span></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">TAX BASE</td>
                                        <td><input type="text" readonly id="tax_base"
                                                   class="form-control format-quantity calculate text-right" value="0"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6"></td>
                                        <td>
                                            <input type="radio" id="tax-choice-include-tax" name="type_of_tax"
                                                   {{ old('type_of_tax') == 'on' ? 'checked'  : '' }} onchange="calculate()"
                                                   value="include"> Tax Included <br/>
                                            <input type="radio" id="tax-choice-exclude-tax" name="type_of_tax"
                                                   {{ old('type_of_tax') == 'on' ? 'checked'  : '' }} onchange="calculate()"
                                                   value="exclude"> Tax Excluded <br/>
                                            <input type="text" id="tax-choice-non-tax" name="type_of_tax" value="non">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">TAX PERCENTAGE</td>
                                        <td>
                                            <div class="input-group">
                                                <input type="text" id="tax-percentage"
                                                    name="tax_percentage"
                                                    readonly
                                                    style="min-width: 100px"
                                                    class="form-control format-quantity calculate text-right"
                                                    value="{{old('tax-percentage') ? : 11}}"/>
                                                <span class="input-group-addon">%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">TAX</td>
                                        <td>
                                            <input type="text" readonly="" id="tax"
                                                   class="form-control format-quantity calculate text-right" value="0"/>
                                        </td>
                                    </tr>
                                   
                                    <tr>
                                        <td colspan="6" class="text-right">EXPEDITION FEE</td>
                                        <td><input type="text" id="fee-expedition" name="expedition_fee"
                                                   class="form-control format-price calculate text-right"
                                                   value="{{number_format_db(old('expedition_fee') ? : 0)}}"/></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">TOTAL</td>
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
                        <div class="form-group">
                            <label class="col-md-3 control-label">Request Approval To *</label>

                            <div class="col-md-6">
                                <select name="approval_to" class="selectize" style="width: 100%;"
                                        data-placeholder="Choose one..">
                                    @foreach($list_user_approval as $user_approval)

                                        @if($user_approval->may('approval.point.sales.quotation'))
                                            <option value="{{$user_approval->id}}"
                                                    @if(old('approval_to') == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
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
@include('framework::app.master.contact.__create', ['person_type' => 'customer'])
@stop
@include('framework::scripts.item')
@include('framework::scripts.person')
@section('scripts')
    <script>
        reloadPerson('#contact_id', 'customer');
        var item_table = initDatatable('#item-datatable');
        var counter = {{$counter}} ?  {{$counter}} : 0;
        $('#addItemRow').on('click', function () {
            item_table.row.add([
                '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
                '<select id="item-id-' + counter + '" name="item_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem(this.value, ' + counter + ')">'
                + '<option ></option>'
                + '</select>',
                '<select id="allocation-id-' + counter + '" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                @foreach($list_allocation as $allocation)
                + '<option value="{{$allocation->id}}">{{$allocation->name}}</option>'
                @endforeach
                + '</select>',
                '<div class="input-group"><input type="text" id="item-quantity-' + counter + '" name="item_quantity[]" class="form-control format-quantity text-right calculate" value="0" /><span id="span-unit-' + counter + '" class="input-group-addon"></span></div><input type="hidden"  id="item-unit-' + counter + '" name="item_unit[]" class="form-control format-quantity text-right" value="" />',
                '<td><input type="text" id="item-price-' + counter + '" name="item_price[]" class="form-control format-quantity calculate text-right" value="0" />',
                '<div class="input-group">'
                + '<input type="text" id="item-discount-' + counter + '" name="item_discount[]"  class="form-control calculate text-right" value="0"  /><span class="input-group-addon">%</span></div></td>',
                '<input type="text" readonly id="item-total-' + counter + '" class="form-control format-quantity text-right" value="" /></td>',

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
            radioButtonState();
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

            calculate();
        });

        function radioButtonState() {
            var tax_status = $("#tax-choice-non-tax").val();
            if (tax_status == 'include') {
                $("#tax-choice-include-tax").trigger("click");
            } else if (tax_status == 'exclude') {
                $("#tax-choice-exclude-tax").trigger("click");
            } else {
                $("#tax-choice-non-tax").val("non");
            }
        }

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
            var subtotal = 0;
            for (var i = 0; i < counter; i++) {
                if($('#item-discount-' + i).length != 0) {
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

            $('#tax_base').val(appNum(tax_base));
            $('#tax').val(appNum(tax));
            var expedition_fee = dbNum($('#fee-expedition').val());
            $('#total').val(appNum(tax_base + tax + expedition_fee));
        }

        function selectItem(item_id, counter) {
            getItemUnit(item_id, "#span-unit-"+counter, "html");
            getItemUnit(item_id, "#item-unit-"+counter, "input");
            getLastPrice(item_id, "#item-price-"+counter);
        }

        function getLastPrice(item_id, callback) {
            $.ajax({
                url: '{{url("sales/point/indirect/sales-quotation/get-last-price")}}',
                data : { item_id: item_id },
                success: function(result) {
                    $(callback).val(result.price);
                },
            })
        }

        // reload data item with ajax
        if (counter > 0) {
            reloadPerson('#contact_id', 'customer');
            for(var i=0; i< counter; i++) {
                if($('#item-id-'+i).length != 0){
                    reloadItem('#item-id-' + i, false);
                }
            }    
        }
    </script>
@stop
