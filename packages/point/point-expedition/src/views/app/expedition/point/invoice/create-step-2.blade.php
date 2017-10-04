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
                    <input type="hidden" name="reference_type" value="{{get_class($expedition_order)}}">
                    <input type="hidden" name="reference_id" value="{{$expedition_order->id}}">
                    <input type="hidden" name="expedition_id" value="{{$expedition_order->expedition_id}}">

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend>
                                    <i class="fa fa-angle-right"></i> 
                                    REF# {{ $reference->formulir->form_number }}
                                </legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Date</label>
                            <div class="col-md-6 content-show">
                                {{ date_format_view($reference->formulir->form_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>
                            <div class="col-md-6 content-show">
                                {{ $reference->formulir->notes }}
                            </div>
                        </div>
                    </fieldset>

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
                            {!! get_url_person($expedition_order->expedition_id) !!}
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
                                    <table id="item-datatable" class="table">
                                        <thead>
                                        <tr>
                                            <th>ITEM</th>
                                            <th>QUANTITY</th>
                                            <th>UNIT</th>
                                        </tr>
                                        </thead>
                                        <?php $counter = 1; ?>
                                        <tbody class="manipulate-row">

                                        @foreach($expedition_order->items as $expedition_order_item)
                                            <input type="hidden" name="item_id[]" value="{{$expedition_order_item->item_id}}">
                                            <input type="hidden" name="quantity[]" value="{{$expedition_order_item->quantity}}">
                                            <input type="hidden" name="price[]" value="{{$expedition_order_item->price}}">
                                            <input type="hidden" name="item_discount[]" value="{{$expedition_order_item->discount}}">
                                            <input type="hidden" name="unit[]" value="{{$expedition_order_item->unit}}">
                                            <tr>
                                                <td>{{$expedition_order_item->item->codeName}}</td>
                                                <td>{{number_format_quantity($expedition_order_item->quantity, 0)}}</td>
                                                <td>{{$expedition_order_item->unit}}</td>
                                            </tr>
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
                                <input type="text" id="subtotal" name="subtotal" onkeyup="calculate()" class="form-control format-quantity text-right" value="{{$expedition_order->expedition_fee}}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-9 control-label text-right">DISCOUNT</label>
                            <div class="col-md-3 content-show">
                                <div class="input-group">
                                    <input type="hidden" name="original_discount" value="{{$expedition_order->discount}}">
                                    <input type="text" id="discount" onkeyup="calculate()" maxlength="2"
                                           name="discount" class="form-control format-quantity text-right"
                                           value="{{$expedition_order->discount}}"/>
                                    <span class="input-group-addon">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-9 control-label text-right">TAX BASE</label>
                            <div class="col-md-3 content-show">
                                <input type="text" readonly id="tax_base" name="tax_base" class="form-control format-quantity text-right" value="{{$expedition_order->tax_base}}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-9 control-label text-right">TAX</label>
                            <div class="col-md-3 content-show">
                                <input type="text" readonly id="tax" name="tax" class="form-control format-quantity text-right" value="{{$expedition_order->tax}}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-9 control-label text-right"></label>
                            <div class="col-md-3 content-show">
                                <input type="hidden" name="original_type_of_tax" value="{{$expedition_order->type_of_tax}}">
                                <input type="checkbox" id="tax-choice-include-tax" class="tax" name="type_of_tax" {{ $expedition_order->type_of_tax == 'include' ? 'checked'  : '' }} onchange="calculate()" value="include"> Tax Included <br/>
                                <input type="checkbox" id="tax-choice-exclude-tax" class="tax" name="type_of_tax" {{ $expedition_order->type_of_tax == 'exclude' ? 'checked'  : '' }} onchange="calculate()" value="exclude"> Tax Excluded
                                <input type="checkbox" id="tax-choice-non-tax" class="tax" name="type_of_tax" {{ $expedition_order->type_of_tax == 'non' ? 'checked'  : '' }} onchange="calculate()" value="non" style="display:none">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-9 control-label text-right">TOTAL</label>
                            <div class="col-md-3 content-show">
                                <input type="text" id="total" name="total" readonly class="form-control format-quantity text-right" value="{{$expedition_order->total}}"/>
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
        $(".tax").change(function() {
            var checked = $(this).is(':checked');
            $(".tax").prop('checked',false);
            if(checked) {
                $(this).prop('checked',true);
            } else {
                $('#tax-choice-non-tax').prop('checked', true);
            }
            calculate();
        });

        function calculate() {
            var subtotal = dbNum($('#subtotal').val());
            if (dbNum($('#discount').val()) >= 100) {
                dbNum($('#discount').val(99))
            }

            var discount = dbNum($('#discount').val());
            var tax_base = subtotal - (subtotal * discount / 100);
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
            $('#total').val(appNum(tax_base + tax));
        }
    </script>
@stop
