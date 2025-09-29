@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/goods-received') }}">Goods Received</a></li>
            <li>Create step 3</li>
        </ul>
        <h2 class="sub-header">Goods Received</h2>
        @include('point-purchasing::app.purchasing.point.inventory.goods-received._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('purchasing/point/goods-received')}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input type="hidden" name="reference_purchase_order" value="{{ get_class($reference_purchase_order) }}">
                    <input type="hidden" name="reference_purchase_order_id" value="{{ $reference_purchase_order->id }}">
                    <input type="hidden" name="group_expedition_order" value="{{ $reference_expedition_order ? $reference_expedition_order->group : '' }}">
                    <input type="hidden" name="reference_expedition_order_id" value="{{ $reference_expedition_order ? $reference_expedition_order->id : '' }}">
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Info Reference</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Date</label>
                            <div class="col-md-6 content-show">
                                {{ date_format_view($reference_purchase_order->formulir->form_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Number</label>
                            <div class="col-md-6 content-show">
                                <a href="{{url('purchasing/point/purchase-order/'.$reference_purchase_order->id)}}"> {{ $reference_purchase_order->formulir->form_number }}</a>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Supplier</label>
                            <div class="col-md-6 content-show">
                                {!! get_url_person($reference_purchase_order->supplier->id) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Remaining Downpayment</label>
                            <div class="col-md-6 content-show">
                                <?php $remaining_downpayment = $reference_purchase_order->getTotalRemainingDownpayment($reference_purchase_order->id);?>
                                {{ number_format_price($remaining_downpayment) }}
                                <input type="hidden" name="dp_amount" value="{{ $remaining_downpayment }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Close</label>
                            <div class="col-md-6 content-show">
                                <input type="checkbox" name="close" value="1">
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Goods Received Form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date *</label>

                        <div class="col-md-3">
                            <input readonly type="text" name="form_date" class="form-control date"
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
                        <label class="col-md-3 control-label">Warehouse *</label>

                        <div class="col-md-6">
                            <select id="warehouse-id" name="warehouse_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                <option></option>
                                @foreach($list_warehouse as $warehouse)
                                    <option value="{{$warehouse->id}}"
                                            @if(old('warehouse') == $warehouse->id) selected @endif>{{$warehouse->codeName}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Driver</label>

                        <div class="col-md-6">
                            <input type="text" name="driver" class="form-control" value="{{old('driver')}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">License Plate</label>

                        <div class="col-md-6">
                            <input type="text" name="license_plate" class="form-control"
                                   value="{{old('license_plate')}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>

                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control"
                                   value="{{$reference_purchase_order->formulir->notes}}">
                        </div>
                    </div>
                    <input type="checkbox" style="display:none" id="include-expedition" name="include_expedition" {{ $reference_expedition_order ? '' : 'checked'}} value="1">
                    <input type="hidden" name="expedition_fee" class="form-control format-price"
                           value="{{$reference_expedition_order ? $reference_expedition_order->expedition_fee : $reference_purchase_order->expedition_fee}}"/>

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
                                <table id="datatable-item" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>ITEM</th>
                                        <th class="text-center">QUANTITY PENDING</th>
                                        <th class="text-center">QUANTITY RECEIVED</th>
                                    </tr>
                                    </thead>
                                    <?php $counter = 0; $value_deliver = 0; ?>
                                    @if(!$reference_expedition_order)
                                    <tbody class="manipulate-row">
                                    @foreach($reference_purchase_order->items as $reference_item)
                                        <?php 
                                        $order_qty = ReferHelper::remaining(get_class($reference_item), $reference_item->id, $reference_item->quantity);
                                        $deliver_qty = ReferHelper::remaining(get_class($reference_item), $reference_item->id, $reference_item->quantity);
                                        ?>
                                        <tr>
                                            <td>
                                                {{ $reference_item->item->codeName }}
                                                <input type="hidden" name="reference_item_id[]"
                                                       value="{{$reference_item->id}}"/>
                                                <input type="hidden" name="reference_item_type[]"
                                                       value="{{get_class($reference_item)}}"/>
                                                <input type="hidden" name="item_id[]"
                                                       value="{{$reference_item->item_id}}"/>
                                                <input type="hidden" name="allocation_id[]"
                                                       value="{{$reference_item->allocation_id}}"/>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input id="item-quantity-reference-{{$counter}}" type="text"
                                                           name="item_quantity_reference[]"
                                                           class="form-control format-quantity text-right"
                                                           value="{{ $order_qty }}" readonly/>
                                                    <span class="input-group-addon"> {{ $reference_item->unit }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input id="item-quantity-delivery_order-{{$counter}}" type="text"
                                                       name="item_quantity[]"
                                                       class="form-control format-quantity text-right"
                                                       onKeyup="calculate()" value="{{ $deliver_qty }}"/>
                                                    <span class="input-group-addon"> {{ $reference_item->unit }}</span>   
                                                </div>
                                           </td>
                                            <td>
                                                <input type="hidden" name="item_unit_name[]"
                                                       value="{{$reference_item->unit}}"/>
                                                <input type="hidden" name="item_unit_converter[]"
                                                       value="{{$reference_item->converter}}"/>
                                                <input type="hidden" id="item-price-{{$counter}}" name="item_price[]"
                                                       class="form-control text-right"
                                                       value="{{ $reference_item->price }}"/>
                                                <input type="hidden" id="item-discount-{{$counter}}"
                                                       name="item_discount[]" class="form-control text-right"
                                                       value="{{ $reference_item->discount }}"/>
                                            </td>
                                        </tr>
                                        <?php  $counter++;?>
                                    @endforeach
                                    @endif

                                    @if($reference_expedition_order)
                                     <tbody class="manipulate-row">
                                    @foreach($reference_expedition_order->items as $reference_item)
                                    <?php
                                        $purchase_order_item = Point\PointPurchasing\Models\Inventory\PurchaseOrderItem::where('point_purchasing_order_id', $reference_purchase_order->id)->where('item_id', $reference_item->item_id)->first();
                                    ?>
                                            <td>
                                                {{ $reference_item->item->codeName }}
                                                <input type="hidden" name="reference_item_id[]"
                                                       value="{{$purchase_order_item->id}}"/>
                                                <input type="hidden" name="reference_item_type[]"
                                                       value="{{get_class($purchase_order_item)}}"/>
                                                <input type="hidden" name="item_id[]"
                                                       value="{{$purchase_order_item->item_id}}"/>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input id="item-quantity-reference-{{$counter}}" type="text"
                                                           name="item_quantity_reference[]"
                                                           class="form-control format-quantity text-right"
                                                           value="{{ $reference_item->quantity }}" readonly/>
                                                    <span class="input-group-addon"> {{ $reference_item->unit }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input id="item-quantity-delivery_order-{{$counter}}" type="text"
                                                       name="item_quantity[]"
                                                       readonly="" 
                                                       class="form-control format-quantity text-right"
                                                       onKeyup="calculate()" value="{{ $reference_item->quantity }}"/>
                                                    <span class="input-group-addon"> {{ $reference_item->unit }}</span>   
                                                </div>
                                           </td>
                                            <td>
                                                <input type="hidden" name="item_unit_name[]"
                                                       value="{{$reference_item->unit}}"/>
                                                <input type="hidden" name="item_unit_converter[]"
                                                       value="{{$reference_item->converter}}"/>
                                                <input type="hidden" id="item-price-{{$counter}}" name="item_price[]"
                                                       class="form-control text-right"
                                                       value="{{ $reference_item->price }}"/>
                                                <input type="hidden" id="item-discount-{{$counter}}"
                                                       name="item_discount[]" class="form-control text-right"
                                                       value="{{ $reference_item->discount }}"/>
                                            </td>
                                        </tr>
                                        <?php  $counter++;?>
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
        function includeExpedition() {
            if (document.getElementById("include-expedition").checked) {
                $('#fee-expedition').show();
            } else {
                $('#fee-expedition').hide();
            }
        }

        function calculate() {
            var total_order = 0;
            var total_deliver = 0;
            for (var i = 0; i < {{$counter}}; i++) {
                total_order += dbNum($('#item-quantity-reference-' + i).val()) * dbNum($('#item-price-' + i).val()) - (dbNum($('#item-quantity-reference-' + i).val()) * dbNum($('#item-price-' + i).val()) / 100 * dbNum($('#item-discount-' + i).val()) );
                total_deliver += dbNum($('#item-quantity-delivery_order-' + i).val()) * dbNum($('#item-price-' + i).val()) - (dbNum($('#item-quantity-delivery_order-' + i).val()) * dbNum($('#item-price-' + i).val()) / 100 * dbNum($('#item-discount-' + i).val()) );
            }

            $('#total-order').val(appNum(total_order));
            $('#total-deliver').val(appNum(total_deliver));
        }

        $('.calculate').keyup(function () {
            calculate();
        });

        $(function () {
            calculate();
        });

    </script>
@stop

