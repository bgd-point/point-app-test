@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-expedition::app/expedition/point/invoice/_breadcrumb')
            <li>Edit</li>
        </ul>
        <h2 class="sub-header">Invoice</h2>
        @include('point-expedition::app.expedition.point.invoice._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('expedition/point/invoice/'.$invoice->id)}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="expedition_id" value="{{$expedition->id}}">

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
                                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary">
                                    <i class="fa fa-clock-o"></i>
                                </a>
                            </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Expedition</label>
                        <div class="col-md-6 content-show">
                            <input type="hidden" name="expedition_id" value="{{$expedition->id}}">
                            {{$expedition->codeName}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="{{$invoice->formulir->notes}}">
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
                                    <table id="item-datatable" class="table table-striped">
                                        @foreach($list_expedition_order as $expedition_order)
                                            <?php
                                            $formulir_reference = Point\Framework\Helpers\FormulirHelper::getLocked($expedition_order->formulir_id);
                                            ?>
                                            <input type="hidden" name="collection_reference_type[]"
                                                   value="{{$formulir_reference->formulirable_type}}">
                                            <input type="hidden" name="collection_reference_id[]"
                                                   value="{{$formulir_reference->id}}">
                                        @endforeach
                                        <thead>
                                        <tr>
                                            <th>EXPEDITION ORDER NUMBER</th>
                                            <th>ITEM</th>
                                            <th>QUANTITY</th>
                                        </tr>
                                        </thead>
                                        <?php $counter = 1; $expedition_fee = 0; ?>
                                        <tbody class="manipulate-row">
                                        @foreach($list_expedition_order as $expedition_order)
                                            @foreach($expedition_order->items as $expedition_order_item)
                                                <tr>
                                                    <td>
                                                        <a href="{{url('expedition/point/invoice/'.$expedition_order->id)}}">{{$expedition_order->formulir->form_number}} </a>
                                                        <br> {{ date_format_view($expedition_order->formulir->form_date)}}
                                                        <input type="hidden" name="reference_item_type[]" value="{{get_class($expedition_order_item)}}">
                                                        <input type="hidden" name="reference_item_id[]" value="{{$expedition_order_item->id}}">
                                                        <input type="hidden" name="reference_type[]" value="{{get_class($expedition_order)}}">
                                                        <input type="hidden" name="reference_id[]" value="{{$expedition_order->id}}">
                                                    </td>
                                                    <td> {{ $expedition_order_item->item->codeName }} </td>
                                                    <td>
                                                        <input type="hidden" id="item-quantity-{{$counter}}" name="item_quantity[]" readonly class="format-quantity" value="{{ $expedition_order_item->quantity}}"/>
                                                        {{ number_format_quantity($expedition_order_item->quantity)}}
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
                            <label class="col-md-9 control-label text-right">SUB TOTAL</label>
                            <div class="col-md-3 content-show">
                                <input type="text" id="subtotal" name="subtotal" onkeyup="calculate()" 
                                       class="form-control format-quantity text-right"
                                       value="{{ $invoice->subtotal}}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-9 control-label text-right">DISCOUNT</label>
                            <div class="col-md-3 content-show">
                                <div class="input-group">
                                    <input type="text" id="discount" onkeypress="isDiscount(this.value)" maxlength="2"
                                           name="discount"
                                           class="form-control format-quantity text-right"
                                           value="{{ $invoice->discount}}"/>
                                    <span class="input-group-addon">%</span>
                                </div>

                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-9 control-label text-right">TAX BASE</label>
                            <div class="col-md-3 content-show">
                                <input type="text" readonly id="tax_base" name="tax_base"
                                       class="form-control format-quantity text-right" value="{{ $invoice->tax_base}}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-9 control-label text-right">TAX</label>
                            <div class="col-md-3 content-show">
                                <input type="text" readonly id="tax" name="tax" class="form-control format-quantity text-right" value="{{ $invoice->tax}}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-9 control-label text-right"></label>
                            <div class="col-md-3 content-show">
                                <label>
                                    <input type="checkbox" id="tax-choice-include-tax" name="type_of_tax"
                                       {{ $invoice->type_of_tax == 'include' ? 'checked'  : '' }}
                                       onchange="$('#tax-choice-exclude-tax').prop('checked', false); calculate();"
                                       value="include"> Include Tax
                                </label>
                                <br />
                                <label>
                                    <input type="checkbox" id="tax-choice-exclude-tax" name="type_of_tax"
                                       {{ $invoice->type_of_tax == 'exclude' ? 'checked'  : '' }}
                                       onchange="$('#tax-choice-include-tax').prop('checked', false); calculate();"
                                       value="exclude"> Exclude Tax
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-9 control-label text-right">TOTAL</label>
                            <div class="col-md-3 content-show">
                                <input type="text" id="total" name="total" readonly class="form-control format-quantity text-right" value="{{ $invoice->total}}"/>
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
        function individualFee() {
            $(".fee").attr("readonly", false);
            $(".fee:last-child").focus();
            $("#subtotal").attr("readonly", true);
        }

        function averageFee() {
            $(".fee").attr("readonly", true);
            $("#subtotal").attr("readonly", false);
            $("#subtotal").focus();
        }

        function isDiscount(val) {
            if (val.length >= 2) {
                $("#discount").val("");
            }
            calculate();
        }

        function calculate() {
            var total_fee = dbNum($("#subtotal").val());
            var discount = dbNum($('#discount').val());
            var tax_base = total_fee - total_fee * discount / 100;
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

    </script>
@stop
