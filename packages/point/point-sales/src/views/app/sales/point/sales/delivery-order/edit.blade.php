@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/delivery-order') }}">Delivery Order</a></li>
            <li>Edit</li>
        </ul>
        <h2 class="sub-header">Delivery Order</h2>
        @include('point-sales::app.sales.point.sales.delivery-order._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('sales/point/indirect/delivery-order/'.$delivery_order->id)}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input name="_method" type="hidden" value="PUT">
                    <input type="hidden" name="reference_sales_order" value="{{ get_class($reference_sales_order) }}">
                    <input type="hidden" name="reference_sales_order_id" value="{{ $reference_sales_order->id}}">
                    <input type="hidden" name="reference_expedition_order" value="{{ $reference_expedition_order ? get_class($reference_expedition_order) : '' }}">
                    <input type="hidden" name="reference_expedition_order_id" value="{{ $reference_expedition_order ? $reference_expedition_order->id : '' }}">

                    <div class="form-group">
                        <label class="col-md-3 control-label">Reason edit *</label>

                        <div class="col-md-6">
                            <input type="text" name="edit_notes" class="form-control"
                                   value="{{$delivery_order->formulir->approval_message}}" autofocus>
                        </div>
                    </div>
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Info Reference</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Date</label>

                            <div class="col-md-6 content-show">
                                {{ date_format_view($reference_sales_order->formulir->form_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Number</label>

                            <div class="col-md-6 content-show">
                                {{ $reference_sales_order->formulir->form_number }}
                            </div>
                        </div>
                        @if($reference_sales_order->person)
                            <div class="form-group">
                                <label class="col-md-3 control-label">Customer</label>

                                <div class="col-md-6 content-show">
                                    <a href="{{ url('master/contact/person/'.$reference_sales_order->person_id) }}">{{ $reference_sales_order->person->codeName }}</a>
                                </div>
                            </div>
                        @endif
                        @if($reference_sales_order->expedition)
                            <div class="form-group">
                                <label class="col-md-3 control-label">Expedition</label>

                                <div class="col-md-6 content-show">
                                    <a href="{{ url('master/contact/expedition/'.$reference_sales_order->expedition_id) }}">{{ $reference_sales_order->expedition->codeName }}</a>
                                </div>
                            </div>
                        @endif
                        <div class="form-group">
                            <label class="col-md-3 control-label">Downpayment</label>

                            <div class="col-md-6 content-show">
                                <?php
                                if (count($reference_expedition_order) > 0) {
                                    $remaining_downpayment = $reference_expedition_order->getTotalDownpayment($reference_sales_order->id);
                                } else {
                                    $remaining_downpayment = $reference_sales_order->getTotalDownpayment($reference_sales_order->id);
                                }
                                ?>
                                {{ number_format_price($remaining_downpayment) }}
                                <input type="hidden" name="dp_amount" value="{{ $remaining_downpayment }}">
                               
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Close</label>
                            <div class="col-md-6 content-show">
                                <input type="checkbox" value="1" name="close">
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Goods delivery form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Number</label>
                        <div class="col-md-6 content-show">
                            {{$delivery_order->formulir->form_number}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date</label>

                        <div class="col-md-3">
                            <input type="text" name="form_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                   value="{{ date(date_format_get(), strtotime($delivery_order->formulir->form_date)) }}">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group bootstrap-timepicker">
                                <input type="text" id="time" name="time" class="form-control timepicker"
                                       value="{{date('H:i', strtotime($delivery_order->formulir->form_date))}}">
                            <span class="input-group-btn">
                                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary"><i
                                            class="fa fa-clock-o"></i></a>
                            </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Warehouse</label>

                        <div class="col-md-6">
                            <select id="warehouse-id" name="warehouse_id" class="selectize" style="width: 100%;"
                                    data-placeholder="Choose one..">
                                <option></option>
                                @foreach($list_warehouse as $warehouse)
                                    <option value="{{$warehouse->id}}"
                                            @if($delivery_order->warehouse_id == $warehouse->id) selected @endif>{{$warehouse->codeName}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Driver</label>

                        <div class="col-md-6">
                            <input type="text" name="driver" class="form-control" value="{{$delivery_order->driver}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">License Plate</label>

                        <div class="col-md-6">
                            <input type="text" name="license_plate" class="form-control"
                                   value="{{$delivery_order->license_plate}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>

                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="{{$delivery_order->notes}}">
                        </div>
                    </div>
                    <input type="hidden" id="include-expedition" name="include_expedition" readonly {{$delivery_order->include_expedition == 1 ? 'checked' : ''}} value="true">
                    <input type="hidden" name="expedition_fee" class="form-control format-price" value="@if(! count($reference_expedition_order) > 0) {{$reference_sales_order->expedition_fee}} @else {{$reference_expedition_order->expedition_fee}} @endif"/>
                    
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
                                        <th class="text-right">QUANTITY ORDER</th>
                                        <th class="text-right">QUANTITY DELIVER</th>
                                        <th>UNIT</th>
                                    </tr>
                                    </thead>
                                    <?php $counter = 0; ?>
                                    <tbody class="manipulate-row">
                                    @if(count($reference_expedition_order) > 0)
                                    <!--  Reference from sales-->
                                    @foreach($reference_sales_order->items as $reference_item)
                                        <?php
                                            $refer_to = ReferHelper::getReferTo(get_class($reference_item),
                                                $reference_item->id,
                                                get_class($delivery_order),
                                                $delivery_order->id);
                                        ?>
                                        <tr>
                                            <td>
                                                {{ $reference_item->item->codeName }}
                                                <input type="hidden" name="reference_item_id[]"
                                                       value="{{$reference_item->id}}"/>
                                                <input type="hidden" name="reference_item_type[]"
                                                       value="{{get_class($reference_item)}}"/>
                                                <input type="hidden" name="reference_item_value[]"
                                                       value="{{$reference_item->quantity}}"/>
                                                <input type="hidden" name="item_id[]"
                                                       value="{{$reference_item->item_id}}"/>
                                                <input type="hidden" name="allocation_id[]"
                                                       value="{{$reference_item->allocation_id}}"/>
                                            </td>
                                            <td><input id="item-quantity-reference-{{$counter}}" type="text"
                                                       name="item_quantity_reference[]"
                                                       class="form-control format-quantity text-right"
                                                       value="{{ ReferHelper::remaining(get_class($reference_item), $reference_item->id, $reference_item->quantity) + $refer_to->quantity }}"
                                                       readonly/></td>
                                            <td><input id="item-quantity-delivery_order-{{$counter}}" type="text"
                                                       name="item_quantity[]"
                                                       class="form-control format-quantity text-right"
                                                       onKeyup="calculate()" value="{{ $refer_to->quantity }}"/></td>
                                            <td>
                                                <input type="hidden" name="item_unit_name[]"
                                                       value="{{$reference_item->unit}}"/>
                                                <input type="hidden" name="item_unit_converter[]"
                                                       value="{{$reference_item->converter}}"/>
                                                {{ $reference_item->unit }}
                                                <input type="hidden" id="item-price-{{$counter}}" name="item_price[]"
                                                       class="form-control text-right"
                                                       value="{{ $reference_item->price }}"/></td>
                                            <input type="hidden" id="item-discount-{{$counter}}" name="item_discount[]"
                                                   class="form-control text-right"
                                                   value="{{ $reference_item->discount }}"/></td>
                                            </td>
                                        </tr>
                                        <?php $counter++;?>
                                    @endforeach

                                    @else
                                    <!--  Reference from expedition-->
                                    @foreach($reference_sales_order->items as $reference_item)
                                    <?php
                                        $order_qty = ReferHelper::remaining(get_class($reference_item), $reference_item->id, $reference_item->quantity);
                                        $refer_to = ReferHelper::getReferTo(get_class($reference_item),
                                            $reference_item->id,
                                            get_class($delivery_order),
                                            $delivery_order->id);

                                        $qty = $order_qty + $refer_to->quantity;
                                    ?>
                                    <input type="hidden" name="expedition_order_id" value="{{$reference_item->point_expedition_order_id}}">
                                        <tr>
                                            <td>
                                                {{ $reference_item->item->codeName }}
                                                <input type="hidden" name="reference_item_id[]"
                                                       value="{{$reference_item->id}}"/>
                                                <input type="hidden" name="reference_item_type[]"
                                                       value="{{get_class($reference_item)}}"/>
                                                <input type="hidden" name="reference_item_value[]"
                                                       value="{{$reference_item->quantity}}"/>
                                                <input type="hidden" name="item_id[]"
                                                       value="{{$reference_item->item_id}}"/>
                                                <input type="hidden" name="allocation_id[]"
                                                       value="{{$reference_item->allocation_id}}"/>
                                            </td>
                                            <td>
                                                <input id="item-quantity-reference-{{$counter}}" type="text"
                                                       name="item_quantity_reference[]"
                                                       class="form-control format-quantity text-right"
                                                       value="{{$qty}}"
                                                       readonly/>
                                            </td>
                                            <td><input id="item-quantity-delivery_order-{{$counter}}" type="text"
                                                       name="item_quantity[]"
                                                       class="form-control format-quantity text-right"
                                                       onKeyup="calculate()" value="{{ $refer_to->quantity }}"/></td>
                                            <td>
                                                <input type="hidden" name="item_unit_name[]"
                                                       value="{{$reference_item->unit}}"/>
                                                <input type="hidden" name="item_unit_converter[]"
                                                       value="{{$reference_item->converter}}"/>
                                                {{ $reference_item->unit }}
                                                <input type="hidden" id="item-price-{{$counter}}" name="item_price[]"
                                                       class="form-control text-right"
                                                       value="{{ $reference_item->price }}"/></td>
                                            <input type="hidden" id="item-discount-{{$counter}}" name="item_discount[]"
                                                   class="form-control text-right"
                                                   value="{{ $reference_item->discount }}"/></td>
                                            </td>
                                        </tr>
                                        <?php $counter++;?>
                                    @endforeach
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <input id="total-order" type="hidden" name="value_order" class="form-control text-right" value="0" readonly/>
                    <input id="total-deliver" type="hidden" name="value_deliver" class="form-control text-right calculate" value="0" readonly>

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
                        @if($reference_sales_order->is_cash > 0)
                            <div class="form-group">
                                <label class="col-md-3 control-label">Ask Approval To</label>

                                <div class="col-md-6">
                                    <select id="approval-to" name="approval_to" class="selectize" style="width: 100%;"
                                            data-placeholder="Choose one..">
                                        <option></option>
                                        @foreach($list_user_approval as $user_approval)
                                            @if($user_approval->may('approval.point.sales.payment.collection'))
                                                <option value="{{$user_approval->id}}"
                                                        @if($delivery_order->formulir->approval_to == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
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
        var item_table = initDatatable('#item-datatable');
        $('.calculate').keyup(function () {
            calculate();
        });

        $(function () {
            calculate();
        });

        function includeExpedition() {
            if (document.getElementById("include-expedition").checked) {
                $('#fee-expedition').show();
            } else {
                $('#fee-expedition').hide();
            }
        }

        if (!document.getElementById("include-expedition").checked) {
            $('#fee-expedition').hide();
        }

        function calculate() {
            var rows = $("#item-datatable").dataTable().fnGetNodes();
            var total_order = 0;
            var total_deliver = 0;
            for (var i = 0; i < rows.length; i++) {
                total_order += dbNum($('#item-quantity-reference-' + i).val()) * dbNum($('#item-price-' + i).val()) - (dbNum($('#item-quantity-reference-' + i).val()) * dbNum($('#item-price-' + i).val()) / 100 * dbNum($('#item-discount-' + i).val()) );

                total_deliver += dbNum($('#item-quantity-delivery_order-' + i).val()) * dbNum($('#item-price-' + i).val()) - (dbNum($('#item-quantity-delivery_order-' + i).val()) * dbNum($('#item-price-' + i).val()) / 100 * dbNum($('#item-discount-' + i).val()) );
            }

            $('#total-order').val(appNum(total_order));
            $('#total-deliver').val(appNum(total_deliver));
        }
    </script>
@stop
