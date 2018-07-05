@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/retur') }}">Return</a></li>
            <li>Edit</li>
        </ul>
        <h2 class="sub-header">Return</h2>
        @include('point-sales::app.sales.point.sales.retur._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('sales/point/indirect/retur/'.$retur->id)}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input type="hidden" name="_method" value="PUT">

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> INFO REFERENCE</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Date</label>

                        <div class="col-md-6 content-show">
                            {{date_Format_view($invoice->formulir->form_date, true)}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Number</label>

                        <div class="col-md-6 content-show">
                            <input type="hidden" name="invoice_id" value="{{$invoice->id}}">
                            <a href="{{url('sales/point/indirect/invoice/'.$invoice->id)}}">{{$invoice->formulir->form_number}}</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Customer</label>

                        <div class="col-md-6 content-show">
                            <input type="hidden" name="person_id" value="{{$invoice->person_id}}">
                            {{$invoice->person->codeName}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>

                        <div class="col-md-6 content-show">
                            {{$invoice->formulir->notes}}
                        </div>
                    </div>

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Return Form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Date</label>

                        <div class="col-md-3">
                            <input type="text" name="form_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}" placeholder="{{date_format_get()}}"
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
                                        <th>Goods Delivered</th>
                                        <th>ITEM</th>
                                        <th class="text-right" width="100">QUANTITY RECEIVED</th>
                                        <th class="text-right" width="100">QUANTITY RETURNED</th>
                                        <th>SATUAN</th>
                                        <th class="text-right" width="100">PRICE</th>
                                        <th class="text-right" width="100">DISCOUNT</th>
                                        <th class="text-right" width="100">TOTAL</th>
                                    </tr>
                                    </thead>
                                    <?php $counter = 1; $expedition_fee = 0; ?>
                                    <tbody class="manipulate-row">
                                    <?php $expedition_fee = $invoice->expedition_fee; ?>
                                    @foreach($invoice->items as $invoice_item)
                                        <?php
                                        $refer_to = \Point\Framework\Helpers\ReferHelper::getReferTo(get_class($invoice_item),
                                                $invoice_item->id,
                                                get_class($retur),
                                                $retur->id);
                                        ?>
                                        <tr>
                                            <td>
                                                <a href="{{url('sales/point/indirect/invoice/'.$invoice->id)}}">{{$invoice->formulir->form_number}}</a>
                                                <br/>
                                                {{date_format_view($invoice->formulir->form_date)}}
                                                <input type="hidden" name="reference_item_type[]"
                                                       value="{{get_class($invoice_item)}}">
                                                <input type="hidden" name="reference_item_id[]"
                                                       value="{{$invoice_item->id}}">
                                            </td>
                                            <td>
                                                <a href="{{ url('master/item/'.$invoice_item->item_id) }}">{{ $invoice_item->item->codeName }}</a>
                                                <input type="hidden" name="item_id[]"
                                                       value="{{$invoice_item->item_id}}"/>
                                            </td>
                                            <td><input type="text" readonly id="item-quantity-penerimaan-{{$counter}}"
                                                       name="item_quantity_penerimaan[]"
                                                       class="form-control format-quantity calculate text-right"
                                                       value="{{ $invoice_item->quantity }}"/></td>
                                            <td><input type="text" id="item-quantity-{{$counter}}"
                                                       name="item_quantity[]"
                                                       class="form-control format-quantity calculate text-right"
                                                       value="{{ $refer_to->quantity }}"/></td>
                                            <td>
                                                {{ $refer_to->unit }}
                                            </td>
                                            <td><input type="text" readonly id="item-price-{{$counter}}"
                                                       name="item_price[]"
                                                       class="form-control format-quantity calculate text-right"
                                                       value="{{ $refer_to->price }}"/></td>
                                            <td>
                                                <div class="input-group"><input type="text" readonly
                                                                                id="item-discount-{{$counter}}"
                                                                                name="item_discount[]"
                                                                                class="form-control format-quantity calculate text-right"
                                                                                value="{{ $refer_to->discount }}"/><span
                                                            class="input-group-addon">%</span></div>
                                            </td>
                                            <td><input type="text" readonly id="item-total-{{$counter}}"
                                                       class="form-control format-quantity text-right" value=""/></td>
                                        </tr>
                                        <?php $counter++;?>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td colspan="7" class="text-right">SUB TOTAL</td>
                                        <td><input type="text" readonly id="subtotal"
                                                   class="form-control format-quantity calculate text-right" value="0"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-right">DISCOUNT</td>
                                        <td>
                                            <div class="input-group"><input type="text" id="discount" name="discount"
                                                                            class="form-control format-quantity calculate text-right"
                                                                            value="0"/><span
                                                        class="input-group-addon">%</span></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-right">TAX BASE</td>
                                        <td><input type="text" readonly id="tax_base" name="tax_base"
                                                   class="form-control format-quantity calculate text-right" value="0"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-right">TAX</td>
                                        <td><input type="text" readonly="" id="tax" name="tax"
                                                   class="form-control format-quantity calculate text-right" value="0"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7"></td>
                                        <td>
                                            <input type="radio" id="tax-choice-include-tax" name="type_of_tax"
                                                   {{ old('type_of_tax') == 'on' ? 'checked'  : '' }} onchange="calculate()"
                                                   value="include"> Include Tax <br/>
                                            <input type="radio" id="tax-choice-exclude-tax" name="type_of_tax"
                                                   {{ old('type_of_tax') == 'on' ? 'checked'  : '' }} onchange="calculate()"
                                                   value="exclude"> Exclude Tax <br/>
                                            <input type="text" id="tax-choice-non-tax" name="type_of_tax"
                                                   onchange="calculate()" value="non">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-right">EXPEDITION FEE</td>
                                        <td><input type="text" id="expedition-fee" name="expedition_fee"
                                                   class="form-control format-price calculate text-right"
                                                   value="{{$expedition_fee}}"/></td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-right">TOTAL</td>
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
                                <legend><i class="fa fa-angle-right"></i> PERSON IN CHARGE</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">FORM CREATOR</label>

                            <div class="col-md-6 content-show">
                                {{\Auth::user()->name}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Ask Approval To</label>

                            <div class="col-md-6">
                                <select id="approval-to" name="approval_to" class="selectize" style="width: 100%;"
                                        data-placeholder="Choose one..">
                                    @foreach($list_user_approval as $user_approval)
                                        @if($user_approval->may('approval.point.sales.return'))
                                            <option value="{{$user_approval->id}}"
                                                    @if(old('user_approval') == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
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
@stop

@section('scripts')
    <style>
        tbody.manipulate-row:after {
            content: '';
            display: block;
            height: 100px;
        }
    </style>
    <script>
        $('#item-datatable').DataTable({
            bSort: false,
            bPaginate: false,
            bInfo: false,
            bFilter: false,
            bScrollCollapse: false
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
            $("#tax-choice-non-tax").hide();

            calculate();
        });

        function calculate() {
            var rows_length = $("#item-datatable").dataTable().fnGetNodes().length;
            var subtotal = 0;
            for (var i = 1; i <= rows_length; i++) {
                var total_per_row = dbNum($('#item-quantity-' + i).val()) * dbNum($('#item-price-' + i).val()) -
                        ( dbNum($('#item-quantity-' + i).val()) * dbNum($('#item-price-' + i).val()) / 100 * dbNum($('#item-discount-' + i).val()) );
                subtotal += total_per_row;
                $('#item-total-' + i).val(appNum(total_per_row));
            }

            $('#subtotal').val(appNum(subtotal));

            var discount = dbNum($('#discount').val());
            var tax_base = subtotal - discount;
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
    </script>
@stop
