@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/sales-order') }}">Sales Order</a></li>
            <li>Create step 2</li>
        </ul>
        <h2 class="sub-header">Sales Order</h2>
        @include('point-sales::app.sales.point.sales.sales-order._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('sales/point/indirect/sales-order/'.$sales_quotation->id.'/store')}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> REF# <a
                                            href="{{ url('sales/point/indirect/sales-quotation/'.$sales_quotation->id) }}"
                                            target="_blank">{{ $sales_quotation->formulir->form_number }}</a></legend>
                                <input type="hidden" name="reference_id" value="{{ $sales_quotation->id }}"/>
                                <input type="hidden" name="reference_type" value="{{ get_class($sales_quotation) }}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Required Date</label>

                            <div class="col-md-6 content-show">
                                {{ date_format_view($sales_quotation->required_date, false) }}
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
                                <legend><i class="fa fa-angle-right"></i> Order Form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date</label>

                        <div class="col-md-3">
                            <input type="text" name="form_date" class="form-control date input-datepicker"
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
                        <label class="col-md-3 control-label">Customer</label>

                        <div class="col-md-6">
                            <div class="@if(access_is_allowed_to_view('create.customer')) input-group @endif">
                                <select id="contact_id" name="person_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                    <option></option>
                                    @foreach($list_person as $person)
                                        <option value="{{$person->id}}" @if($sales_quotation->person_id == $person->id) selected @endif>{{$person->codeName}}</option>
                                    @endforeach
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
                            <input type="text" name="notes" class="form-control"
                                   value="{{ $sales_quotation->formulir->notes }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Require downpayment before delivering the order</label>

                        <div class="col-md-6 content-show">
                            <input type="checkbox" id="credit-selling" name="is_cash" checked value="true">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Include Expedition</label>

                        <div class="col-md-6 content-show">
                            <input type="checkbox" id="include-expedition" name="include_expedition" checked value="true">
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
                                        <th>ITEM</th>
                                        <th>ALLOCATION</th>
                                        <th class="text-right">QUANTITY</th>
                                        <th>UNIT</th>
                                        <th class="text-right">PRICE</th>
                                        <th class="text-right">DISCOUNT</th>
                                        <th class="text-right">TOTAL</th>
                                    </tr>
                                    </thead>
                                    <?php $counter = 1; ?>
                                    <tbody class="manipulate-row">
                                    @foreach($sales_quotation->items as $sales_quotation_item)
                                        <tr>
                                            <td style="min-width: 150px">
                                                {{ $sales_quotation_item->item->codeName }}
                                                <input type="hidden" name="reference_item_id[]"
                                                       value="{{$sales_quotation_item->id}}"/>
                                                <input type="hidden" name="reference_item_type[]"
                                                       value="{{get_class($sales_quotation_item)}}"/>
                                                <input type="hidden" name="reference_item_value[]"
                                                       value="{{$sales_quotation_item->quantity}}"/>
                                                <input type="hidden" name="item_id[]"
                                                       value="{{$sales_quotation_item->item_id}}"/>
                                                <input type="hidden" name="allocation_id[]"
                                                       value="{{$sales_quotation_item->allocation_id}}"/>
                                            </td>
                                            <td>
                                                {{$sales_quotation_item->allocation->name}}
                                            </td>
                                            <td><input id="item-quantity-{{$counter}}" type="text"
                                                       name="item_quantity[]"
                                                       class="form-control format-quantity text-right calculate"
                                                       value="{{ ReferHelper::remaining(get_class($sales_quotation_item), $sales_quotation_item->id, $sales_quotation_item->quantity) }}"/>
                                            </td>
                                            <td>
                                                <input type="hidden" name="item_unit_name[]"
                                                       value="{{$sales_quotation_item->unit}}"/>
                                                <input type="hidden" name="item_unit_converter[]"
                                                       value="{{$sales_quotation_item->converter}}"/>
                                                {{ $sales_quotation_item->unit }}
                                            </td>
                                            <td><input type="text" id="item-price-{{$counter}}" name="item_price[]"
                                                       class="form-control format-quantity calculate text-right"
                                                       value="{{$sales_quotation_item->price}}"/></td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="text" id="item-discount-{{$counter}}"
                                                           name="item_discount[]" maxlength="3"
                                                           class="form-control calculate text-right"
                                                           value="{{number_format_quantity($sales_quotation_item->discount,0)}}"/><span
                                                            class="input-group-addon">%</span></div>
                                            </td>
                                            <td><input type="text" readonly id="item-total-{{$counter}}"
                                                       class="form-control format-quantity text-right"
                                                       value="{{$sales_quotation_item->price}}"/></td>
                                        </tr>
                                        <?php $counter++;?>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td colspan="6" class="text-right">SUB TOTAL</td>
                                        <td><input type="text" readonly id="subtotal"
                                                   class="form-control format-quantity calculate text-right"
                                                    value="0"/></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">DISCOUNT</td>
                                        <td>
                                            <div class="input-group">
                                                <input type="text" id="discount" name="discount" maxlength="3"
                                                       class="form-control calculate text-right"
                                                       style="min-width: 100px"
                                                       value="{{number_format_quantity($sales_quotation->discount,0)}}"/><span
                                                        class="input-group-addon">%</span></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right">TAX BASE</td>
                                        <td>
                                            <input type="text"
                                                readonly
                                                id="tax_base"
                                                name="tax_base"
                                                class="form-control format-quantity calculate text-right" 
                                                value="0"/>
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
                                                <input type="text" id="tax-percentage" required 
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
                                            <input type="text" readonly="" id="tax" name="tax"
                                                   class="form-control format-quantity calculate text-right" value="0"/>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <td colspan="6" class="text-right">EXPEDITION FEE</td>
                                        <td><input type="text" id="fee-expedition" name="expedition_fee"
                                                   class="form-control format-price calculate text-right"
                                                   value="{{$sales_quotation->expedition_fee}}"/></td>
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
                                <select name="approval_to" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                    @foreach($list_user_approval as $user_approval)
                                        @if($user_approval->may('approval.point.sales.order'))
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

@section('scripts')
    <script>
        var item_table = initDatatable('#item-datatable');

        $('.calculate').keyup(function () {
            calculate();
        });

        $(function () {
            $('#tax-choice-non-tax').hide();
            var tax_status = {!! json_encode($sales_quotation->type_of_tax) !!};

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
            for (var i = 1; i <= rows_length; i++) {
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
            if (dbNum($('#tax-percentage').val()) > 100) {
                dbNum($('#tax-percentage').val(100))
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
    </script>
@stop
