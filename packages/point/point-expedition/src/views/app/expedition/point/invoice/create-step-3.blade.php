@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-expedition::app/expedition/point/invoice/_breadcrumb')
            <li>Create Step 3</li>
        </ul>
        <h2 class="sub-header">Invoice</h2>
        @include('point-expedition::app.expedition.point.invoice._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('expedition/point/invoice')}}" method="post" class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input type="hidden" name="expedition_id" value="{{$expedition->id}}">
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Date</label>
                        <div class="col-md-3">
                            <input type="text" name="form_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}"
                                   value="{{date(date_format_get(), strtotime(\Carbon::now()))}}">
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
                        <label class="col-md-3 control-label">Expedition</label>
                        <div class="col-md-6 content-show">
                            <a href="{{url('master/contact/expedition/'.$expedition->id)}}"> {{ $expedition->codeName }}</a>
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
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    @foreach($list_expedition_order_invoice as $expedition_order)
                                        <?php
                                        $formulir_reference = Point\Framework\Helpers\FormulirHelper::getLocked($expedition_order->formulir_id);
                                        ?>
                                        <input type="hidden" name="collection_reference_type[]"
                                               value="{{$formulir_reference->formulirable_type}}">
                                        <input type="hidden" name="collection_reference_id[]"
                                               value="{{$formulir_reference->id}}">

                                        <input type="hidden" name="reference_type_expedition[]"
                                               value="{{get_class($expedition_order)}}">
                                        <input type="hidden" name="reference_id_expedition[]"
                                               value="{{$expedition_order->id}}">
                                    @endforeach
                                    <table id="item-datatable" class="table">
                                        <thead>
                                        <tr>
                                            <th width="20%">RECEIVE ORDER NUMBER</th>
                                            <th width="20%">ITEM</th>
                                            <th width="15%" class="text-right">QUANTITY</th>
                                        </tr>
                                        </thead>
                                        <?php $counter = 1;$subtotal=0; ?>
                                        <tbody class="manipulate-row">

                                        @foreach($list_expedition_order_invoice as $expedition_order)
                                            <?php $subtotal += $expedition_order->expedition_fee; ?>
                                            @foreach($expedition_order->items as $receive_order_item)
                                                <tr>
                                                    <td>
                                                        <a href="{{url('expedition/point/expedition-order/'.$expedition_order->id)}}">{{$expedition_order->formulir->form_number}} </a>
                                                        <br> {{ date_format_view($expedition_order->formulir->form_date)}}
                                                        <input type="hidden" name="reference_item_id[]"
                                                               value="{{ $receive_order_item->id }}"/>
                                                        <input type="hidden" name="reference_item_type[]"
                                                               value="{{get_class($receive_order_item)}}"/>

                                                        <input type="hidden" name="reference_type[]"
                                                               value="{{get_class($expedition_order)}}">
                                                        <input type="hidden" name="reference_id[]"
                                                               value="{{$expedition_order->id}}">
                                                    </td>
                                                    <td> {{ $receive_order_item->item->codeName }} </td>
                                                    <td class="text-right">
                                                        <input type="hidden" id="item-quantity-{{$counter}}"
                                                               name="item_quantity[]" readonly class="format-quantity"
                                                               value="{{ $receive_order_item->quantity }}"/>
                                                        {{ number_format_quantity($receive_order_item->quantity) }} {{ $receive_order_item->unit }}
                                                    </td>
                                                    </td>
                                                    <?php $counter++;?>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <div class="form-group">
                            <label class="col-md-9 control-label text-right">EXPEDITION FEE</label>
                            <div class="col-md-3 content-show">
                                <input type="text" id="subtotal" onclick="setToNontax()" onkeyup="calculate()" name="subtotal" class="form-control format-quantity text-right" value="{{ $subtotal }}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-9 control-label text-right">DISCOUNT</label>
                            <div class="col-md-3 content-show">
                                <div class="input-group">
                                    <input type="text" id="discount" onkeypress="isDiscount(this.value)" maxlength="2"
                                           name="discount" class="form-control format-quantity text-right"
                                           value="0"/>
                                    <span class="input-group-addon">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-9 control-label text-right">TAX BASE</label>
                            <div class="col-md-3 content-show">
                                <input type="text" readonly id="tax_base" name="tax_base" class="form-control format-quantity text-right" value="0"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-9 control-label text-right">TAX</label>
                            <div class="col-md-3 content-show">
                                <input type="text" readonly id="tax" name="tax" class="form-control format-quantity text-right" value="0"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-9 control-label text-right"></label>
                            <div class="col-md-3 content-show">
                                <input type="radio" id="tax-choice-include-tax" name="type_of_tax"
                                       {{ old('type_of_tax') == 'on' ? 'checked'  : '' }} onchange="calculate()"
                                       value="include"> Include Tax <br/>
                                <input type="radio" id="tax-choice-exclude-tax" name="type_of_tax"
                                       {{ old('type_of_tax') == 'on' ? 'checked'  : '' }} onchange="calculate()"
                                       value="exclude"> Exlude Tax <br/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-9 control-label text-right">TOTAL</label>
                            <div class="col-md-3 content-show">
                                <input type="text" id="total" name="total" readonly class="form-control format-quantity text-right" value="0"/>
                            </div>
                        </div>

                    </fieldset>

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> PERSON IN CHARGE</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Creator</label>
                            <div class="col-md-6 content-show">
                                {{\Auth::user()->name}}
                            </div>
                        </div>

                    </fieldset>

                    <div class="form-group">
                        <div class="col-sm-6">
                            <input type="radio" id="tax-choice-non-tax" name="type_of_tax" {{ old('type_of_tax') == 'on' ? 'checked'  : '' }} checked onchange="calculate()" value="non" style="visibility: hidden;">
                        </div>
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
        var item_table = initDatatable('#item-datatable');

        $(function () {
            calculate();
        });

        function individualFee() {
            $(".fee").attr("readonly", false);
            $(".fee:first-child").focus();
            $("#subtotal").attr("readonly", true);
        }

        function averageFee() {
            $(".fee").attr("readonly", true);
            $("#subtotal").attr("readonly", false);
            $("#subtotal").focus();
        }

        function calculate() {
            var rows_length = $("#item-datatable").dataTable().fnGetNodes().length;
            var subtotal = dbNum($('#subtotal').val());
            var total_fee = 0;
            var discount = dbNum($('#discount').val());
            var tax_base = subtotal - subtotal * discount / 100;
            var tax = 0;

            if ($('#tax-choice-exclude-tax').prop('checked')) {
                tax = tax_base * 10 / 100;
            }

            if ($('#tax-choice-include-tax').prop('checked')) {
                tax_base = tax_base * 100 / 110;
                tax = tax_base * 10 / 100;
                $('#discount').val(0);
                $('#discount').prop('readonly', true);
                var discount = 0;
            } else {
                $('#discount').prop('readonly', false);
            }

            $('#tax_base').val(appNum(tax_base));
            $('#tax').val(appNum(tax));
            $('#total').val(appNum(tax_base + tax));
        }

        function isDiscount(val) {
            if (val.length >= 2) {
                $("#discount").val("");
            }
        }

        function setToNontax() {
            $("#tax-choice-include-tax").attr("checked", false);
            $("#tax-choice-exclude-tax").attr("checked", false);
            $("#tax-choice-non-tax").trigger("click");
            calculate();
        }
    </script>
@stop
