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
                <form action="{{url('purchasing/point/invoice/'.$invoice->id)}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="supplier_id" value="{{$supplier->id}}">

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
                            <input type="text" name="form_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                   value="{{ date(date_format_get(), strtotime($invoice->formulir->form_date)) }}">
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
                            {!! get_url_person($supplier->id) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="{{ $invoice->formulir->notes }}">
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
                                @foreach($list_goods_received as $goods_received)
                                    <input type="hidden" name="reference_type[]" value="{{get_class($goods_received)}}">
                                    <input type="hidden" name="reference_id[]" value="{{$goods_received->id}}">
                                @endforeach
                                <table id="item-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>Goods Delivered NUMBER</th>
                                        <th>ITEM</th>
                                        <th class="text-right">QUANTITY</th>
                                        <th class="text-right">PRICE</th>
                                        <th class="text-right">DISCOUNT</th>
                                        <th class="text-right">TOTAL</th>
                                    </tr>
                                    </thead>
                                    <?php $counter = 1; $expedition_fee = 0; ?>
                                    <tbody class="manipulate-row">
                                    @foreach($list_goods_received as $goods_received)
                                        <?php $expedition_fee += $goods_received->expedition_fee; ?>
                                        @foreach($goods_received->items as $goods_received_item)
                                            <?php
                                            $invoice_item = \Point\Framework\Helpers\ReferHelper::getReferTo(get_class($goods_received_item),
                                                    $goods_received_item->id, get_class($invoice), $invoice->id);
                                            ?>
                                            <tr>
                                                <td>
                                                    <a href="{{url('purchasing/point/goods-received/'.$goods_received->id)}}">{{$goods_received->formulir->form_number}}</a>
                                                    <br/>
                                                    {{date_format_view($goods_received->formulir->form_date)}}
                                                    <input type="hidden" name="reference_item_type[]"
                                                           value="{{get_class($goods_received_item)}}">
                                                    <input type="hidden" name="reference_item_id[]"
                                                           value="{{$goods_received_item->id}}">
                                                    <input type="hidden" name="item_id[]" value="{{$goods_received_item->item->id}}">
                                                    <input type="hidden" name="allocation_id[]" value="{{$goods_received_item->allocation->id}}">
                                                </td>
                                                <td>
                                                    <a href="{{ url('master/item/'.$goods_received_item->item_id) }}">{{ $goods_received_item->item->codeName }}</a>
                                                </td>
                                                <td class="text-right">
                                                    <input id="item-quantity-{{$counter}}" type="hidden"
                                                           name="item_quantity[]"
                                                           class="form-control format-quantity text-right calculate"
                                                           value="{{ $goods_received_item->quantity }}"/>
                                                    {{ number_format_quantity($goods_received_item->quantity) }} {{ $goods_received_item->unit }}
                                                    <input type="hidden" name="item_unit[]" value="{{ $goods_received_item->unit }}">
                                                </td>
                                                <td><input type="text" id="item-price-{{$counter}}" name="item_price[]"
                                                           class="form-control format-quantity calculate text-right"
                                                           value="{{ $goods_received_item->price }}"/></td>
                                                <td>
                                                    <div class="input-group"><input type="text"
                                                                                    id="item-discount-{{$counter}}"
                                                                                    name="item_discount[]"
                                                                                    class="form-control calculate text-right"
                                                                                    value="{{ $goods_received_item->discount }}"/><span
                                                                class="input-group-addon">%</span></div>
                                                </td>
                                                <td><input type="text" readonly id="item-total-{{$counter}}"
                                                           class="form-control format-quantity text-right" value=""/>
                                                </td>
                                            </tr>
                                            <?php $counter++;?>
                                        @endforeach
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-right">SUB TOTAL</td>
                                        <td><input type="text" readonly id="subtotal" name="subtotal" 
                                                   class="form-control format-quantity calculate text-right" value="0"
                                                   /></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">DISCOUNT</td>
                                        <td>
                                            <div class="input-group"><input type="text" id="discount" name="discount"
                                                                            class="form-control format-quantity calculate text-right"
                                                                            style="min-width: 100px"
                                                                            value="{{$invoice->discount}}"/><span
                                                        class="input-group-addon">%</span></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">TAX BASE</td>
                                        <td><input type="text" readonly id="tax_base" name="tax_base"
                                                   class="form-control format-quantity calculate text-right"
                                                   value="{{$invoice->tax_base}}"/></td>
                                    </tr>

                                    <tr>
                                        <td colspan="5"></td>
                                        <td>
                                            <input type="radio" id="tax-choice-include-tax" name="type_of_tax"
                                                   {{ $invoice->type_of_tax == 'include' ? 'checked'  : '' }}
                                                   onchange="calculate()" value="include"> Include Tax <br/>
                                            <input type="radio" id="tax-choice-exclude-tax" name="type_of_tax"
                                                   {{ $invoice->type_of_tax == 'exclude'  ? 'checked'  : '' }} onchange="calculate()"
                                                   value="exclude"> Exlude Tax <br/>
                                            <input type="hidden" id="tax-choice-non-tax" {{ $invoice->type_of_tax == 'non'  ? 'checked'  : '' }} name="type_of_tax" value="non">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">TAX PERCENTAGE</td>
                                        <td>
                                            <div class="input-group">
                                                <input type="text" id="tax-percentage" required 
                                                    name="tax_percentage"
                                                    readonly
                                                    style="min-width: 100px"
                                                    class="form-control format-quantity calculate text-right"
                                                    value="{{$invoice->tax_percentage}}"/>
                                                <span class="input-group-addon">%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">TAX</td>
                                        <td><input type="text" readonly="" id="tax" name="tax"
                                                   class="form-control format-quantity calculate text-right"
                                                   value="{{$invoice->tax}}"/></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">EXPEDITION FEE</td>
                                        <td><input type="text" id="expedition-fee" name="expedition_fee"
                                                   class="form-control format-price calculate text-right"
                                                   value="{{$invoice->expedition_fee}}"/></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">TOTAL</td>
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

@section('scripts')
    <script>
        initDatatable('#item-datatable');
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
            $('#tax_base').val(appNum(tax_base));

            var tax = 0;

            if ($('#tax-choice-exclude-tax').prop('checked')) {
                tax = tax_base * $('#tax-percentage').val() / 100;
                $("#tax-choice-non-tax").val("exclude");
                $('#tax-percentage').prop('readonly', false);
            }

            if ($('#tax-choice-include-tax').prop('checked')) {
                tax_base = tax_base * 100 / (100 + $('#tax-percentage').val());
                tax = tax_base * $('#tax-percentage').val() / 100;
                $('#tax_base').val(appNum(tax_base));
                $("#tax-choice-non-tax").val("include");
                $('#tax-percentage').prop('readonly', false);
            }

            $('#tax_base').val(appNum(tax_base));
            $('#tax').val(appNum(tax));
            var expedition_fee = dbNum($('#expedition-fee').val());
            $('#total').val(appNum(tax_base + tax + expedition_fee));
        }
    </script>
@stop
